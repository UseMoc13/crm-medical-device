<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use App\Models\AnnouncementMedia;
use DOMDocument;
use DOMElement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

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

        $this->validateFileInputs($request);

        $storedPaths = [];

        try {
            $announcement = DB::transaction(function () use (
                $request,
                $validated,
                &$storedPaths
            ) {
                $validated['slug'] = $this->uniqueSlug($validated['title']);
                $validated['content'] = $this->sanitizeHtml(
                    $validated['content']
                );
                $validated['is_pinned'] = $request->boolean('is_pinned');
                $validated['created_by'] = Auth::id();
                $validated['updated_by'] = Auth::id();

                $this->normalizePublicationFields($validated);

                if ($validated['is_pinned']) {
                    $validated['pin_order'] = $validated['pin_order']
                        ?? ((int) Announcement::where('is_pinned', true)
                            ->max('pin_order') + 1);
                } else {
                    $validated['pin_order'] = null;
                }

                if ($request->hasFile('thumbnail')) {
                    $validated['thumbnail_path'] = $this->storeFile(
                        $request->file('thumbnail'),
                        'announcements/thumbnails',
                        $storedPaths
                    );
                }

                $announcement = Announcement::create($validated);

                $this->storeMediaFiles(
                    $request,
                    $announcement,
                    $storedPaths
                );

                $this->storeAttachmentFiles(
                    $request,
                    $announcement,
                    $storedPaths
                );

                return $announcement;
            });
        } catch (Throwable $exception) {
            $this->deleteStoredFiles($storedPaths);

            throw $exception;
        }

        return redirect()
            ->route('announcement-management.edit', $announcement)
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
        $validated = $request->validate(
            $this->validationRules($announcement)
        );

        $this->validateFileInputs($request);

        $storedPaths = [];
        $oldPaths = [];

        try {
            DB::transaction(function () use (
                $request,
                $announcement,
                $validated,
                &$storedPaths,
                &$oldPaths
            ) {
                $announcement = Announcement::query()
                    ->where(
                        'announcement_id',
                        $announcement->announcement_id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $validated['slug'] = $this->uniqueSlug(
                    $validated['title'],
                    $announcement->announcement_id
                );

                $validated['content'] = $this->sanitizeHtml(
                    $validated['content']
                );
                $validated['is_pinned'] = $request->boolean('is_pinned');
                $validated['updated_by'] = Auth::id();

                $this->normalizePublicationFields(
                    $validated,
                    $announcement
                );

                if ($validated['is_pinned']) {
                    $validated['pin_order'] = $validated['pin_order']
                        ?? $announcement->pin_order
                        ?? ((int) Announcement::where('is_pinned', true)
                            ->where(
                                'announcement_id',
                                '!=',
                                $announcement->announcement_id
                            )
                            ->max('pin_order') + 1);
                } else {
                    $validated['pin_order'] = null;
                }

                if ($request->boolean('remove_thumbnail')) {
                    if ($announcement->thumbnail_path) {
                        $oldPaths[] = $announcement->thumbnail_path;
                    }

                    $validated['thumbnail_path'] = null;
                }

                if ($request->hasFile('thumbnail')) {
                    if ($announcement->thumbnail_path) {
                        $oldPaths[] = $announcement->thumbnail_path;
                    }

                    $validated['thumbnail_path'] = $this->storeFile(
                        $request->file('thumbnail'),
                        'announcements/thumbnails',
                        $storedPaths
                    );
                }

                $announcement->update($validated);

                $this->removeSelectedMedia(
                    $request,
                    $announcement,
                    $oldPaths
                );

                $this->removeSelectedAttachments(
                    $request,
                    $announcement,
                    $oldPaths
                );

                $this->storeMediaFiles(
                    $request,
                    $announcement,
                    $storedPaths
                );

                $this->storeAttachmentFiles(
                    $request,
                    $announcement,
                    $storedPaths
                );
            });
        } catch (Throwable $exception) {
            $this->deleteStoredFiles($storedPaths);

            throw $exception;
        }

        $this->deleteStoredFiles($oldPaths);

        return redirect()
            ->route('announcement-management.edit', $announcement)
            ->with('success', 'Announcement updated successfully.');
    }

    public function destroy(
        Announcement $announcement
    ): RedirectResponse {
        $filePaths = DB::transaction(function () use ($announcement) {
            $announcement = Announcement::query()
                ->where(
                    'announcement_id',
                    $announcement->announcement_id
                )
                ->lockForUpdate()
                ->firstOrFail();

            $announcement->load(['attachments', 'media']);

            $filePaths = $announcement->attachments
                ->pluck('file_path')
                ->merge($announcement->media->pluck('file_path'));

            if ($announcement->thumbnail_path) {
                $filePaths->push($announcement->thumbnail_path);
            }

            $announcement->delete();

            return $filePaths->filter()->unique()->values()->all();
        });

        $this->deleteStoredFiles($filePaths);

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

    private function validationRules(
        ?Announcement $announcement = null
    ): array {
        return [
            'title' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'content' => ['required', 'string', 'max:1000000'],
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
            'remove_thumbnail' => ['nullable', 'boolean'],
            'remove_media' => ['sometimes', 'array'],
            'remove_media.*' => [
                'uuid',
                Rule::exists('announcement_media', 'media_id')
                    ->where('announcement_id', $announcement?->announcement_id),
            ],
            'remove_attachments' => ['sometimes', 'array'],
            'remove_attachments.*' => [
                'uuid',
                Rule::exists('announcement_attachments', 'attachment_id')
                    ->where(
                        'announcement_id',
                        $announcement?->announcement_id
                    ),
            ],
        ];
    }

    private function validateFileInputs(Request $request): void
    {
        $request->validate([
            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'media_files' => ['nullable', 'array', 'max:20'],
            'media_files.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:10240',
            ],
            'attachment_files' => ['nullable', 'array', 'max:20'],
            'attachment_files.*' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,csv,txt,mp4,webm,zip',
                'max:20480',
            ],
        ]);
    }

    private function normalizePublicationFields(
        array &$validated,
        ?Announcement $announcement = null
    ): void {
        if ($validated['status'] === 'published') {
            $validated['published_at'] ??=
                $announcement?->published_at ?? now();

            $validated['scheduled_at'] = null;
        }

        if ($validated['status'] === 'scheduled') {
            $validated['published_at'] = null;
        }

        if (
            $validated['status'] !== 'scheduled'
        ) {
            $validated['scheduled_at'] = null;
        }

        if (! $validated['is_pinned']) {
            $validated['pin_order'] = null;
        }
    }

    private function storeMediaFiles(
        Request $request,
        Announcement $announcement,
        array &$storedPaths
    ): void {
        foreach ($request->file('media_files', []) as $index => $file) {
            $path = $this->storeFile(
                $file,
                'announcements/media',
                $storedPaths
            );

            $announcement->media()->create([
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'media_type' => 'image',
                'sort_order' => (
                    (int) $announcement->media()->max('sort_order')
                ) + 1 + $index,
                'uploaded_by' => Auth::id(),
            ]);
        }
    }

    private function storeAttachmentFiles(
        Request $request,
        Announcement $announcement,
        array &$storedPaths
    ): void {
        foreach (
            $request->file('attachment_files', []) as $index => $file
        ) {
            $path = $this->storeFile(
                $file,
                'announcements/attachments',
                $storedPaths
            );

            $extension = strtolower(
                $file->getClientOriginalExtension()
            );

            $mediaType = in_array(
                $extension,
                ['mp4', 'webm'],
                true
            ) ? 'video' : 'document';

            $announcement->attachments()->create([
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'media_type' => $mediaType,
                'sort_order' => (
                    (int) $announcement->attachments()->max('sort_order')
                ) + 1 + $index,
                'uploaded_by' => Auth::id(),
            ]);
        }
    }

    private function removeSelectedMedia(
        Request $request,
        Announcement $announcement,
        array &$oldPaths
    ): void {
        $ids = $request->input('remove_media', []);

        if (empty($ids)) {
            return;
        }

        $items = $announcement->media()
            ->whereIn('media_id', $ids)
            ->get();

        foreach ($items as $item) {
            $oldPaths[] = $item->file_path;
            $item->delete();
        }
    }

    private function removeSelectedAttachments(
        Request $request,
        Announcement $announcement,
        array &$oldPaths
    ): void {
        $ids = $request->input('remove_attachments', []);

        if (empty($ids)) {
            return;
        }

        $items = $announcement->attachments()
            ->whereIn('attachment_id', $ids)
            ->get();

        foreach ($items as $item) {
            $oldPaths[] = $item->file_path;
            $item->delete();
        }
    }

    private function storeFile(
        UploadedFile $file,
        string $directory,
        array &$storedPaths
    ): string {
        $path = $file->store($directory, 'public');

        if (! $path) {
            throw new \RuntimeException('Unable to store uploaded file.');
        }

        $storedPaths[] = $path;

        return $path;
    }

    private function deleteStoredFiles(array $paths): void
    {
        foreach (array_unique(array_filter($paths)) as $path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function sanitizeHtml(string $html): string
    {
        $allowedTags = [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's',
            'h1', 'h2', 'h3', 'h4', 'ul', 'ol', 'li',
            'blockquote', 'pre', 'code', 'table', 'thead',
            'tbody', 'tr', 'th', 'td', 'a', 'img', 'figure',
            'figcaption', 'div', 'span', 'hr',
        ];

        $html = strip_tags(
            $html,
            '<' . implode('><', $allowedTags) . '>'
        );

        if (! class_exists(DOMDocument::class)) {
            $html = preg_replace(
                '/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i',
                '',
                $html
            );

            $html = preg_replace(
                '/(href|src)\s*=\s*(["\'])\s*(javascript|data):.*?\2/i',
                '$1="#"',
                $html
            );

            return $html;
        }

        $previous = libxml_use_internal_errors(true);

        $document = new DOMDocument('1.0', 'UTF-8');

        $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="announcement-content">'
                . $html
                . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $container = $document->getElementById('announcement-content');

        if ($container) {
            $this->sanitizeNode($container);
            $result = '';

            foreach ($container->childNodes as $child) {
                $result .= $document->saveHTML($child);
            }
        } else {
            $result = '';
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $result;
    }

    private function sanitizeNode(DOMElement $element): void
    {
        $allowedAttributes = [
            'a' => ['href', 'title', 'target', 'rel'],
            'img' => ['src', 'alt', 'width', 'height'],
            'td' => ['colspan', 'rowspan'],
            'th' => ['colspan', 'rowspan'],
        ];

        $remove = [];

        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);

                if (in_array(
                    $tag,
                    ['script', 'style', 'iframe', 'object', 'embed', 'form'],
                    true
                )) {
                    $remove[] = $child;
                    continue;
                }

                $this->sanitizeNode($child);
            }
        }

        foreach ($remove as $child) {
            $element->removeChild($child);
        }

        $attributes = [];

        foreach ($element->attributes as $attribute) {
            $attributes[] = $attribute->name;
        }

        foreach ($attributes as $name) {
            $value = trim($element->getAttribute($name));
            $tag = strtolower($element->tagName);

            if (
                str_starts_with(strtolower($name), 'on')
                || ! in_array(
                    $name,
                    $allowedAttributes[$tag] ?? [],
                    true
                )
            ) {
                $element->removeAttribute($name);
                continue;
            }

            if (in_array($name, ['href', 'src'], true)) {
                $scheme = parse_url($value, PHP_URL_SCHEME);

                if (
                    $scheme !== null
                    && ! in_array(
                        strtolower($scheme),
                        ['http', 'https'],
                        true
                    )
                ) {
                    $element->removeAttribute($name);
                }
            }

            if ($tag === 'a' && $name === 'target') {
                if (! in_array($value, ['_blank', '_self'], true)) {
                    $element->removeAttribute($name);
                } elseif ($value === '_blank') {
                    $element->setAttribute('rel', 'noopener noreferrer');
                }
            }
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
