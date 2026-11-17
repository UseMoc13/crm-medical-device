<?php

namespace App\Http\Controllers;

use App\Models\WorkspacePersonalNote;
use App\Services\WorkspaceNoteMentions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Global floating note endpoints: owner-only, JSON-only, revision-aware. */
final class WorkspaceFloatingNotesController extends Controller
{
    private function actor(): string
    {
        abort_unless(Auth::guard('web')->check(), 401);
        return (string) Auth::guard('web')->id();
    }

    private function owned(string $note): WorkspacePersonalNote
    {
        return WorkspacePersonalNote::query()->where('owner_user_id', $this->actor())
            ->whereKey($note)->firstOrFail();
    }

    private function dto(WorkspacePersonalNote $note, WorkspaceNoteMentions $mentions, bool $withPreview = true): array
    {
        return [
            'id' => (string) $note->getKey(), 'title' => (string) $note->title,
            'body' => (string) ($note->body ?? ''), 'revision' => (int) $note->revision,
            'is_pinned' => (bool) $note->is_pinned,
            'updated' => $note->updated_at?->format('d M Y, H:i') ?? '-',
            'preview_html' => $withPreview ? $mentions->render((string) $note->body, $this->actor()) : '',
            'detail_url' => route('notes.show', ['note' => $note->getKey()]),
        ];
    }

    public function index(WorkspaceNoteMentions $mentions): JsonResponse
    {
        $items = WorkspacePersonalNote::query()->where('owner_user_id', $this->actor())
            ->whereNull('archived_at')->orderByDesc('is_pinned')->orderByDesc('updated_at')
            ->limit(50)->get()->map(fn ($note) => $this->dto($note, $mentions, false))->all();
        return response()->json(['notes' => $items], 200, ['Cache-Control' => 'private, no-store']);
    }

    public function show(string $note, WorkspaceNoteMentions $mentions): JsonResponse
    {
        return response()->json(['note' => $this->dto($this->owned($note), $mentions)], 200,
            ['Cache-Control' => 'private, no-store']);
    }

    public function create(WorkspaceNoteMentions $mentions): JsonResponse
    {
        $note = WorkspacePersonalNote::create([
            'owner_user_id' => $this->actor(), 'title' => 'Untitled Note', 'body' => '',
        ]);
        return response()->json(['note' => $this->dto($note, $mentions)], 201,
            ['Cache-Control' => 'private, no-store']);
    }

    public function save(Request $request, string $note, WorkspaceNoteMentions $mentions): JsonResponse
    {
        $input = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'body' => ['nullable', 'string', 'max:10000'],
            'revision' => ['required', 'integer', 'min:0'],
        ]);
        $title = trim((string) ($input['title'] ?? '')) ?: 'Untitled Note';
        $body = (string) ($input['body'] ?? '');
        $actor = $this->actor();
        $this->owned($note);
        return DB::transaction(function () use ($note, $input, $title, $body, $actor, $mentions): JsonResponse {
            $record = WorkspacePersonalNote::query()->where('owner_user_id', $actor)
                ->whereKey($note)->lockForUpdate()->firstOrFail();
            abort_if($record->archived_at !== null, 422, 'Archived notes cannot be edited in the floating panel.');
            if ((int) $record->revision !== (int) $input['revision']) {
                return response()->json(['message' => 'This note was changed elsewhere. Reload the note before saving.',
                    'conflict' => true], 409, ['Cache-Control' => 'private, no-store']);
            }
            $mentions->synchronize($record, $body, $actor);
            $record->update(['title' => $title, 'body' => $body, 'revision' => (int)$record->revision + 1]);
            return response()->json(['note' => $this->dto($record->refresh(), $mentions)], 200,
                ['Cache-Control' => 'private, no-store']);
        });
    }

    public function destroy(string $note): JsonResponse
    {
        DB::transaction(function () use ($note): void {
            WorkspacePersonalNote::query()->where('owner_user_id', $this->actor())
                ->whereKey($note)->lockForUpdate()->firstOrFail()->delete();
        });
        return response()->json(['deleted' => true], 200, ['Cache-Control' => 'private, no-store']);
    }

    public function search(Request $request, WorkspaceNoteMentions $mentions): JsonResponse
    {
        $input = $request->validate([
            'type' => ['required', Rule::in(['all','activity','todo','calendar'])],
            'q' => ['nullable', 'string', 'max:80'],
        ]);
        return response()->json(['items' => $mentions->search($this->actor(), $input['type'],
            (string) ($input['q'] ?? ''))], 200, ['Cache-Control' => 'private, no-store']);
    }
}
