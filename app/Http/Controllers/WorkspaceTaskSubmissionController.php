<?php

namespace App\Http\Controllers;

use App\Models\WorkspaceTask;
use App\Models\WorkspaceTaskAssignment;
use App\Models\WorkspaceTaskSubmissionFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

final class WorkspaceTaskSubmissionController extends Controller
{
    private function actorId(): string
    {
        abort_unless(Auth::guard('web')->check(), 401);
        return (string) Auth::guard('web')->id();
    }

    private function taskVisible(WorkspaceTask $todo): WorkspaceTask
    {
        $id = $this->actorId();
        abort_unless((string) $todo->owner_user_id === $id ||
            ($todo->visibility === 'work' && $todo->assignments()->where('assigned_user_id',$id)->exists()),
            404);
        return $todo;
    }

    private function owner(WorkspaceTask $todo): WorkspaceTask
    {
        $this->taskVisible($todo);
        abort_unless((string) $todo->owner_user_id === $this->actorId(), 403,
            'Only the creator can download other assignees\' submissions.');
        return $todo;
    }

    private function ownAssignment(WorkspaceTask $todo): WorkspaceTaskAssignment
    {
        $this->taskVisible($todo);
        abort_unless($todo->visibility === 'work', 404);
        return $todo->assignments()->where('assigned_user_id', $this->actorId())->firstOrFail();
    }

    public function upload(Request $request, WorkspaceTask $todo): RedirectResponse
    {
        $assignment = $this->ownAssignment($todo);
        abort_if(in_array($todo->status, ['completed','cancelled'], true), 422,
            'This task no longer accepts submissions.');
        abort_if($assignment->status === 'completed', 422,
            'This assignment has already been submitted.');

        $validated = $request->validate([
            'files' => ['required','array','min:1','max:5'],
            'files.*' => ['required','file','max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,txt',
                'extensions:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,txt'],
        ]);
        $newFiles = $validated['files'];
        if ($assignment->files()->count() + count($newFiles) > 30) {
            throw ValidationException::withMessages(['files' => 'Maximum 30 files per assignee.']);
        }

        $saved = [];
        try {
            DB::transaction(function () use ($todo, $assignment, $newFiles, &$saved) {
                // A7.4.9: serialize upload with task closure before locking the assignment.
                $lockedTask = WorkspaceTask::query()->whereKey($todo->getKey())->lockForUpdate()->firstOrFail();
                abort_unless($lockedTask->visibility === 'work' &&
                    !in_array($lockedTask->status, ['completed','cancelled'], true), 422,
                    'This task no longer accepts submissions.');
                // Lock assignment so completion cannot race with upload.
                $locked = WorkspaceTaskAssignment::query()->whereKey($assignment->getKey())
                    ->where('task_id', $todo->getKey())->lockForUpdate()->firstOrFail();
                abort_if($locked->status === 'completed', 422, 'Assignment already submitted.');
                if ($locked->files()->count() + count($newFiles) > 30) {
                    throw ValidationException::withMessages(['files' => 'Maximum 30 files per assignee.']);
                }
                foreach ($newFiles as $file) {
                    $original = trim(basename(str_replace('\\', '/', $file->getClientOriginalName())));
                    if ($original === '') $original = 'file';
                    $original = Str::limit($original, 200, '');
                    $extension = strtolower($file->getClientOriginalExtension());
                    $path = 'crm-task-submissions/'.$todo->getKey().'/'.$locked->getKey()
                        .'/'.Str::uuid().'.'.$extension;
                    if (!$file->storeAs(dirname($path), basename($path), 'local')) {
                        throw ValidationException::withMessages(['files' => 'Unable to store the uploaded file.']);
                    }
                    $saved[] = $path;
                    $locked->files()->create([
                        'uploaded_by_user_id' => $this->actorId(),
                        'original_name' => $original,
                        'stored_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => (int) $file->getSize(),
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($saved);
            throw $e;
        }
        return back()->with('success', count($newFiles).' file(s) uploaded privately.');
    }

    public function complete(WorkspaceTask $todo): RedirectResponse
    {
        $assignment = $this->ownAssignment($todo);
        DB::transaction(function () use ($todo, $assignment) {
            // Task first, then assignment: identical lock order to creator review.
            $currentTask = WorkspaceTask::query()->whereKey($todo->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($currentTask->visibility === 'work', 404);
            abort_if(in_array($currentTask->status,['completed','cancelled'],true),422,
                'This task is no longer accepting submissions.');
            $locked = WorkspaceTaskAssignment::query()->whereKey($assignment->getKey())
                ->where('task_id', $currentTask->getKey())->lockForUpdate()->firstOrFail();
            abort_unless((string) $locked->assigned_user_id === $this->actorId(), 404);
            if ($locked->submission_required && !$locked->files()->exists()) {
                throw ValidationException::withMessages([
                    'files' => 'This assignment requires at least one file before completion.',
                ]);
            }
            if ($locked->status !== 'completed') {
                $locked->update([
                    'status'=>'completed', 'completed_at'=>now(),
                    'review_status'=>'pending_review',
                    'reviewed_by_user_id'=>null, 'reviewed_at'=>null, 'review_note'=>null,
                ]);
                if ($currentTask->status === 'todo') $currentTask->update(['status'=>'in_progress']);
            }
        }, 3);
        return back()->with('success','Submission received. Awaiting the creator\'s review.');
    }

    public function removeFile(WorkspaceTask $todo, string $file): RedirectResponse
    {
        $assignment = $this->ownAssignment($todo);
        $path = DB::transaction(function () use ($todo, $assignment, $file) {
            $lockedTask = WorkspaceTask::query()->whereKey($todo->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($lockedTask->visibility === 'work', 404);
            abort_if(in_array($lockedTask->status,['completed','cancelled'],true),422);
            $locked = WorkspaceTaskAssignment::query()->whereKey($assignment->getKey())
                ->where('task_id', $lockedTask->getKey())->lockForUpdate()->firstOrFail();
            abort_unless((string) $locked->assigned_user_id === $this->actorId(), 404);
            abort_if($locked->status === 'completed',422, 'Submitted evidence is locked.');
            $document = $locked->files()->whereKey($file)->firstOrFail();
            abort_unless((string) $document->uploaded_by_user_id === $this->actorId(), 403);
            $path = $document->stored_path;
            $document->delete();
            return $path;
        }, 3);
        Storage::disk('local')->delete($path);
        return back()->with('success', 'Attachment removed.');
    }

    private function scopedFile(WorkspaceTask $todo, string $file): WorkspaceTaskSubmissionFile
    {
        $this->taskVisible($todo);
        $document = WorkspaceTaskSubmissionFile::query()
            ->whereKey($file)->whereHas('assignment', fn ($q) => $q->where('task_id',$todo->getKey()))
            ->with('assignment.assignee')->firstOrFail();
        $isOwner = (string) $todo->owner_user_id === $this->actorId();
        abort_unless($isOwner || ($todo->visibility === 'work' &&
            (string) $document->assignment->assigned_user_id === $this->actorId()), 404);
        return $document;
    }

    private function safePart(string $value): string
    {
        // Disallow path separators, Windows reserved chars, control characters and dots-only names.
        $value = preg_replace('/[\\x00-\\x1F\\x7F\\\\\/:*?"<>|]+/u','-', $value) ?? 'file';
        $value = trim($value, ". \t\n\r\0\x0B");
        return Str::limit($value ?: 'file', 130, '');
    }

    private function downloadName(WorkspaceTaskSubmissionFile $file): string
    {
        $user = $this->safePart((string) ($file->assignment?->assignee?->name ?: 'Unknown User'));
        return $user.' - '.$this->safePart($file->original_name);
    }

    private function directDownload(WorkspaceTaskSubmissionFile $file): BinaryFileResponse
    {
        $path = Storage::disk('local')->path($file->stored_path);
        abort_unless(str_starts_with($file->stored_path, 'crm-task-submissions/') && is_file($path), 404);
        return response()->download($path, $this->downloadName($file), [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function downloadFile(WorkspaceTask $todo, string $file): BinaryFileResponse
    {
        return $this->directDownload($this->scopedFile($todo,$file));
    }

    /** Per-user: one file => direct; 2+ => ZIP. */
    public function downloadAssignment(WorkspaceTask $todo, string $assignment): BinaryFileResponse
    {
        $this->owner($todo);
        $record = $todo->assignments()->whereKey($assignment)
            ->with(['assignee','files.assignment.assignee'])->firstOrFail();
        $files = $record->files;
        abort_if($files->isEmpty(), 404, 'There are no files for this assignee.');
        if ($files->count() === 1) return $this->directDownload($files->first());
        return $this->zipFiles($files->all(), $record->assignee?->name.' - submission.zip');
    }

    /** One flat ZIP containing files from every recipient. */
    public function downloadAll(WorkspaceTask $todo): BinaryFileResponse
    {
        $this->owner($todo);
        $records = $todo->assignments()->with(['files.assignment.assignee'])->get();
        $files = $records->flatMap(fn ($record) => $record->files)->all();
        abort_if(!$files, 404, 'No submitted files are available yet.');
        return $this->zipFiles($files, $todo->title.' - all submissions.zip');
    }

    /** @param WorkspaceTaskSubmissionFile[] $files */
    private function zipFiles(array $files, string $archiveName): BinaryFileResponse
    {
        abort_unless(class_exists(ZipArchive::class), 503,
            'PHP zip extension must be enabled to download archives.');
        $temp = tempnam(sys_get_temp_dir(), 'crm-task-');
        abort_unless($temp !== false, 500, 'Unable to create a temporary archive.');
        $zip = new ZipArchive();
        if ($zip->open($temp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($temp);
            abort(500, 'Unable to open the archive.');
        }
        $used = [];
        $added = 0;
        $missing = 0;
        foreach ($files as $file) {
            $path = Storage::disk('local')->path($file->stored_path);
            if (!str_starts_with($file->stored_path, 'crm-task-submissions/') || !is_file($path)) { $missing++; continue; }
            $name = $this->downloadName($file);
            $base = pathinfo($name, PATHINFO_FILENAME);
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $unique = $name;
            $number = 2;
            while (isset($used[mb_strtolower($unique)])) {
                $unique = $base.' ('.$number++.')'.($ext ? '.'.$ext : '');
            }
            $used[mb_strtolower($unique)] = true;
            if ($zip->addFile($path, $unique)) $added++; else $missing++;
        }
        $zip->close();
        if ($missing > 0) {
            @unlink($temp);
            abort(409, 'Some submitted files could not be found. Download cancelled to prevent an incomplete archive.');
        }
        if ($added === 0) {
            @unlink($temp);
            abort(404, 'No submitted files are available.');
        }
        return response()->download($temp, $this->safePart($archiveName), [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ])->deleteFileAfterSend(true);
    }
}
