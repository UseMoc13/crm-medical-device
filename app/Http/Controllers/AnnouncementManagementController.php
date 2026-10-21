<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementManagementController extends Controller
{
    private const STATUSES = [
        'draft',
        'scheduled',
        'published',
        'archived',
    ];

    public function index(Request $request): View
    {
        $query = Announcement::query()
            ->with('creator')
            ->withCount(['attachments', 'media']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($query) use ($search) {
                $query->where('title', 'ilike', "%{$search}%")
                    ->orWhere('summary', 'ilike', "%{$search}%")
                    ->orWhere('category', 'ilike', "%{$search}%");
            });
        }

        if (
            $request->filled('status')
            && in_array($request->input('status'), self::STATUSES, true)
        ) {
            $query->where('status', $request->input('status'));
        }

        if ($request->input('pin') === 'pinned') {
            $query->where('is_pinned', true);
        } elseif ($request->input('pin') === 'unpinned') {
            $query->where('is_pinned', false);
        }

        $announcements = $query
            ->orderByDesc('is_pinned')
            ->orderByRaw('pin_order ASC NULLS LAST')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $statistics = [
            'total' => Announcement::count(),
            'draft' => Announcement::where('status', 'draft')->count(),
            'scheduled' => Announcement::where('status', 'scheduled')->count(),
            'published' => Announcement::where('status', 'published')->count(),
            'archived' => Announcement::where('status', 'archived')->count(),
        ];

        return view('announcements-manage.index', compact(
            'announcements',
            'statistics'
        ));
    }

    public function create(): View
    {
        return view('announcements-manage.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules());

        $validated['slug'] = $this->uniqueSlug($validated['title']);
        $validated['is_pinned'] = $request->boolean('is_pinned');
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $this->normalizePublicationFields($validated);

        if ($validated['is_pinned']) {
            $validated['pin_order'] = $validated['pin_order']
                ?? ((int) Announcement::where('is_pinned', true)->max('pin_order') + 1);
        }

        Announcement::create($validated);

        return redirect()
            ->route('announcement-management.index')
            ->with('success', 'Announcement created successfully.');
    }

    public function show(Announcement $announcement): View
    {
        $announcement->load([
            'creator',
            'updater',
            'attachments.uploader',
            'media.uploader',
        ]);

        return view('announcements-manage.show', compact('announcement'));
    }

    public function edit(Announcement $announcement): View
    {
        $announcement->load([
            'attachments',
            'media',
        ]);

        return view('announcements-manage.edit', compact('announcement'));
    }

    public function update(
        Request $request,
        Announcement $announcement
    ): RedirectResponse {
        $validated = $request->validate($this->validationRules());

        $validated['slug'] = $this->uniqueSlug(
            $validated['title'],
            $announcement->announcement_id
        );

        $validated['is_pinned'] = $request->boolean('is_pinned');
        $validated['updated_by'] = Auth::id();

        $this->normalizePublicationFields($validated, $announcement);

        if ($validated['is_pinned']) {
            $validated['pin_order'] = $validated['pin_order']
                ?? $announcement->pin_order
                ?? ((int) Announcement::where('is_pinned', true)
                    ->where('announcement_id', '!=', $announcement->announcement_id)
                    ->max('pin_order') + 1);
        } else {
            $validated['pin_order'] = null;
        }

        $announcement->update($validated);

        return redirect()
            ->route('announcement-management.index')
            ->with('success', 'Announcement updated successfully.');
    }

    public function destroy(
        Announcement $announcement
    ): RedirectResponse {
        DB::transaction(function () use ($announcement) {
            $announcement->load(['attachments', 'media']);

            $filePaths = $announcement->attachments
                ->pluck('file_path')
                ->merge($announcement->media->pluck('file_path'));

            if ($announcement->thumbnail_path) {
                $filePaths->push($announcement->thumbnail_path);
            }

            foreach ($filePaths->filter()->unique() as $filePath) {
                Storage::disk('public')->delete($filePath);
            }

            $announcement->delete();
        });

        return redirect()
            ->route('announcement-management.index')
            ->with('success', 'Announcement deleted successfully.');
    }

    public function togglePin(
        Announcement $announcement
    ): RedirectResponse {
        if ($announcement->is_pinned) {
            $announcement->is_pinned = false;
            $announcement->pin_order = null;
        } else {
            $announcement->is_pinned = true;
            $announcement->pin_order = (
                (int) Announcement::where('is_pinned', true)
                    ->max('pin_order')
            ) + 1;
        }

        $announcement->updated_by = Auth::id();
        $announcement->save();

        return redirect()
            ->back()
            ->with(
                'success',
                $announcement->is_pinned
                    ? 'Announcement pinned successfully.'
                    : 'Announcement unpinned successfully.'
            );
    }

    private function validationRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'summary' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'status' => ['required', Rule::in(self::STATUSES)],
            'scheduled_at' => [
                'nullable',
                'required_if:status,scheduled',
                'date',
            ],
            'published_at' => ['nullable', 'date'],
            'expires_at' => [
                'nullable',
                'date',
                'after:published_at',
            ],
            'is_pinned' => ['nullable', 'boolean'],
            'pin_order' => ['nullable', 'integer', 'min:1'],
        ];
    }

    private function normalizePublicationFields(
        array &$validated,
        ?Announcement $announcement = null
    ): void {
        if ($validated['status'] === 'published') {
            $validated['published_at'] ??=
                $announcement?->published_at ?? now();
        }

        if ($validated['status'] !== 'scheduled') {
            $validated['scheduled_at'] = null;
        }

        if (! $validated['is_pinned']) {
            $validated['pin_order'] = null;
        }
    }

    private function uniqueSlug(
        string $title,
        ?string $ignoreId = null
    ): string {
        $baseSlug = Str::slug($title);

        if ($baseSlug === '') {
            $baseSlug = 'announcement';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            Announcement::where('slug', $slug)
                ->when(
                    $ignoreId,
                    fn ($query) => $query->where(
                        'announcement_id',
                        '!=',
                        $ignoreId
                    )
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
