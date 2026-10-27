<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use DOMDocument;
use DOMElement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

    private const MAX_THUMBNAIL_SIZE = 4 * 1024 * 1024;

    private const MAX_ATTACHMENT_SIZE = 100 * 1024 * 1024;

    private const MAX_ATTACHMENT_COUNT = 20;

    private const MAX_MEDIA_COUNT = 20;

    private const MAX_MEDIA_FILE_SIZE = 10 * 1024 * 1024;

    private const ATTACHMENT_EXTENSIONS = [
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'ppt',
        'pptx',
        'csv',
        'txt',
        'jpg',
        'jpeg',
        'png',
        'webp',
        'gif',
        'mp4',
        'webm',
        'mp3',
        'wav',
        'zip',
        'rar',
        '7z',
        'tar',
        'gz',
    ];

    public function index(Request $request): View
    {
        Announcement::publishDueScheduled();

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
        $categorySuggestions = $this->getCategorySuggestions();

        return view(
            'announcements-manage.create',
            compact('categorySuggestions')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules());

        $this->validateFileInputs($request);

        $this->validatePublicationSchedule($validated);

        $storedPaths = [];

        try {
            $announcement = DB::transaction(function () use (
                $request,
                $validated,
                &$storedPaths
            ) {
                $validated['slug'] = $this->uniqueSlug(
                    $validated['title']
                );

                $validated['content'] = $this->sanitizeHtml(
                    $validated['content']
                );

                $validated['is_pinned'] = $request->boolean('is_pinned');
                $validated['created_by'] = Auth::id();
                $validated['updated_by'] = Auth::id();

                $this->normalizePublicationFields($validated);

                if ($validated['is_pinned']) {
                    $validated['pin_order'] = $validated['pin_order']
                        ?? (
                            (int) Announcement::where('is_pinned', true)
                                ->max('pin_order') + 1
                        );
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
        if (Announcement::publishDueScheduled() > 0) {
            $announcement->refresh();
        }

        $announcement->load([
            'creator',
            'updater',
            'attachments.uploader',
            'media.uploader',
        ]);

        return view(
            'announcements-manage.show',
            compact('announcement')
        );
    }

    public function edit(Announcement $announcement): View
    {
        if (Announcement::publishDueScheduled() > 0) {
            $announcement->refresh();
        }

        $announcement->load([
            'attachments',
            'media',
        ]);

        $categorySuggestions = $this->getCategorySuggestions();

        return view(
            'announcements-manage.edit',
            compact('announcement', 'categorySuggestions')
        );
    }

    public function update(
        Request $request,
        Announcement $announcement
    ): RedirectResponse {
        $validated = $request->validate(
            $this->validationRules($announcement)
        );

        $this->validateFileInputs($request);

        $this->validatePublicationSchedule($validated, $announcement);

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
                        ?? (
                            (int) Announcement::where('is_pinned', true)
                                ->where(
                                    'announcement_id',
                                    '!=',
                                    $announcement->announcement_id
                                )
                                ->max('pin_order') + 1
                        );
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
                    if (
                        $announcement->thumbnail_path
                        && ! in_array(
                            $announcement->thumbnail_path,
                            $oldPaths,
                            true
                        )
                    ) {
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

            $announcement->load([
                'attachments',
                'media',
            ]);

            $filePaths = $announcement->attachments
                ->pluck('file_path')
                ->merge(
                    $announcement->media->pluck('file_path')
                );

            if ($announcement->thumbnail_path) {
                $filePaths->push($announcement->thumbnail_path);
            }

            $announcement->delete();

            return $filePaths
                ->filter()
                ->unique()
                ->values()
                ->all();
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
            'title' => [
                'required',
                'string',
                'max:150',
            ],
            'category' => [
                'nullable',
                'string',
                'max:100',
            ],
            'summary' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'content' => [
                'required',
                'string',
                'max:1000000',
            ],
            'status' => [
                'required',
                Rule::in(self::STATUSES),
            ],
            'scheduled_at' => [
                'nullable',
                'required_if:status,scheduled',
                'date',
            ],
            'expires_at' => [
                'nullable',
                'date',
            ],
            'is_pinned' => [
                'nullable',
                'boolean',
            ],
            'pin_order' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'remove_thumbnail' => [
                'nullable',
                'boolean',
            ],
            'remove_media' => [
                'sometimes',
                'array',
            ],
            'remove_media.*' => [
                'required',
                'uuid',
                Rule::exists('announcement_media', 'media_id')
                    ->where(
                        'announcement_id',
                        $announcement?->announcement_id
                    ),
            ],
            'remove_attachments' => [
                'sometimes',
                'array',
            ],
            'remove_attachments.*' => [
                'required',
                'uuid',
                Rule::exists(
                    'announcement_attachments',
                    'attachment_id'
                )->where(
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
            'media_files' => [
                'nullable',
                'array',
                'max:' . self::MAX_MEDIA_COUNT,
            ],
            'media_files.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:10240',
            ],
            'attachment_files' => [
                'nullable',
                'array',
                'max:' . self::MAX_ATTACHMENT_COUNT,
            ],
            'attachment_files.*' => [
                'required',
                'file',
                'mimes:' . implode(',', self::ATTACHMENT_EXTENSIONS),
                'max:102400',
            ],
        ]);

        $this->validateTotalAttachmentSize($request);
    }

    private function validateTotalAttachmentSize(
        Request $request
    ): void {
        $files = $request->file('attachment_files', []);

        $totalSize = collect($files)
            ->filter(fn ($file) => $file instanceof UploadedFile)
            ->sum(fn (UploadedFile $file) => $file->getSize());

        if ($totalSize > self::MAX_ATTACHMENT_SIZE) {
            throw ValidationException::withMessages([
                'attachment_files' => 'The combined attachment size must not exceed 100 MB.',
            ]);
        }
    }

    private function validatePublicationSchedule(
        array $validated,
        ?Announcement $announcement = null
    ): void {
        $status = $validated['status'];
        $now = now();
        $errors = [];
        $start = null;

        if ($status === 'scheduled') {
            $scheduledAt = Carbon::parse($validated['scheduled_at']);

            $unchanged = $announcement?->scheduled_at
                && $announcement->scheduled_at->format('Y-m-d H:i')
                    === $scheduledAt->format('Y-m-d H:i');

            if (! $unchanged && $scheduledAt->lte($now)) {
                $errors['scheduled_at'] = 'The scheduled time must be in the future. To publish right now, choose the Published status.';
            }

            $start = $scheduledAt;
        } elseif ($status === 'published') {
            $start = $announcement?->published_at ?? $now;
        }

        if (! empty($validated['expires_at']) && $start) {
            $expiresAt = Carbon::parse($validated['expires_at']);

            if ($expiresAt->lte($start)) {
                $errors['expires_at'] = $status === 'scheduled'
                    ? 'The expiration time must be later than the scheduled publication time.'
                    : 'The expiration time must be later than the publication time.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function normalizePublicationFields(
        array &$validated,
        ?Announcement $announcement = null
    ): void {
        $status = $validated['status'];

        if ($status === 'published') {
            $validated['published_at'] =
                $announcement?->published_at ?? now();

            $validated['scheduled_at'] = null;
        }

        if ($status === 'scheduled') {
            $validated['published_at'] = null;
        }

        if ($status !== 'scheduled') {
            $validated['scheduled_at'] = null;
        }

        if (empty($validated['is_pinned'])) {
            $validated['pin_order'] = null;
        }
    }

    private function storeMediaFiles(
        Request $request,
        Announcement $announcement,
        array &$storedPaths
    ): void {
        $files = $request->file('media_files', []);

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $this->storeFile(
                $file,
                'announcements/media',
                $storedPaths
            );

            $sortOrder = (
                (int) $announcement->media()->max('sort_order')
            ) + 1;

            $announcement->media()->create([
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'media_type' => 'image',
                'sort_order' => $sortOrder,
                'uploaded_by' => Auth::id(),
            ]);
        }
    }

    private function storeAttachmentFiles(
        Request $request,
        Announcement $announcement,
        array &$storedPaths
    ): void {
        $files = $request->file('attachment_files', []);

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $this->storeFile(
                $file,
                'announcements/attachments',
                $storedPaths
            );

            $extension = strtolower(
                $file->getClientOriginalExtension()
            );

            $mediaType = match ($extension) {
                'mp4', 'webm' => 'video',
                'mp3', 'wav' => 'audio',
                'jpg', 'jpeg', 'png', 'webp', 'gif' => 'image',
                default => 'document',
            };

            $sortOrder = (
                (int) $announcement->attachments()->max('sort_order')
            ) + 1;

            $announcement->attachments()->create([
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'media_type' => $mediaType,
                'sort_order' => $sortOrder,
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

        if (! is_array($ids) || empty($ids)) {
            return;
        }

        $items = $announcement->media()
            ->whereIn('media_id', $ids)
            ->get();

        foreach ($items as $item) {
            if ($item->file_path) {
                $oldPaths[] = $item->file_path;
            }

            $item->delete();
        }
    }

    private function removeSelectedAttachments(
        Request $request,
        Announcement $announcement,
        array &$oldPaths
    ): void {
        $ids = $request->input('remove_attachments', []);

        if (! is_array($ids) || empty($ids)) {
            return;
        }

        $items = $announcement->attachments()
            ->whereIn('attachment_id', $ids)
            ->get();

        foreach ($items as $item) {
            if ($item->file_path) {
                $oldPaths[] = $item->file_path;
            }

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
            throw new \RuntimeException(
                'Unable to store uploaded file.'
            );
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

    private function getCategorySuggestions(): array
    {
        return Announcement::query()
            ->whereNotNull('category')
            ->pluck('category')
            ->flatMap(function ($category) {
                return explode(',', $category);
            })
            ->map(fn ($category) => trim($category))
            ->filter()
            ->unique(fn ($category) => Str::lower($category))
            ->values()
            ->map(fn ($category) => [
                'name' => $category,
            ])
            ->all();
    }

    private function sanitizeHtml(string $html): string
    {
        $allowedTags = [
            'p',
            'br',
            'strong',
            'b',
            'em',
            'i',
            'u',
            's',
            'h1',
            'h2',
            'h3',
            'h4',
            'ul',
            'ol',
            'li',
            'blockquote',
            'pre',
            'code',
            'table',
            'thead',
            'tbody',
            'tr',
            'th',
            'td',
            'a',
            'img',
            'figure',
            'figcaption',
            'div',
            'span',
            'hr',
        ];

        $html = strip_tags(
            $html,
            '<' . implode('><', $allowedTags) . '>'
        );

        if (! class_exists(DOMDocument::class)) {
            return $this->sanitizeHtmlFallback($html);
        }

        $previous = libxml_use_internal_errors(true);

        try {
            $document = new DOMDocument('1.0', 'UTF-8');

            $document->loadHTML(
                '<?xml encoding="UTF-8"><div id="announcement-content">'
                    . $html
                    . '</div>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );

            $container = $document->getElementById(
                'announcement-content'
            );

            if (! $container) {
                return '';
            }

            $this->sanitizeNode($container);

            $result = '';

            foreach ($container->childNodes as $child) {
                $result .= $document->saveHTML($child);
            }

            return $result;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function sanitizeHtmlFallback(string $html): string
    {
        $html = preg_replace(
            '/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i',
            '',
            $html
        );

        $html = preg_replace_callback(
            '/\s(href|src)\s*=\s*(["\'])(.*?)\2/i',
            function ($matches) {
                $attribute = strtolower($matches[1]);
                $value = trim($matches[3]);
                $compact = preg_replace(
                    '/[\x00-\x20]+/',
                    '',
                    $value
                );

                if (
                    preg_match(
                        '/^(javascript|vbscript|file):/i',
                        $compact
                    )
                ) {
                    return ' ' . $attribute . '="#"';
                }

                if (
                    str_starts_with(strtolower($compact), 'data:')
                    && ! (
                        $attribute === 'src'
                        && preg_match(
                            '/^data:image\/(png|jpeg|gif|webp);base64,/i',
                            $compact
                        )
                    )
                ) {
                    return ' ' . $attribute . '="#"';
                }

                return ' ' . $attribute . '='
                    . $matches[2]
                    . htmlspecialchars(
                        $value,
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                    )
                    . $matches[2];
            },
            $html
        );

        return $html;
    }

    private function sanitizeNode(DOMElement $element): void
    {
        $allowedAttributes = [
            'a' => [
                'href',
                'title',
                'target',
                'rel',
                'style',
            ],
            'img' => [
                'src',
                'alt',
                'width',
                'height',
                'style',
            ],
            'td' => [
                'colspan',
                'rowspan',
                'style',
            ],
            'th' => [
                'colspan',
                'rowspan',
                'style',
            ],
            'table' => [
                'style',
            ],
            'p' => [
                'style',
            ],
            'div' => [
                'style',
            ],
            'span' => [
                'style',
            ],
            'h1' => [
                'style',
            ],
            'h2' => [
                'style',
            ],
            'h3' => [
                'style',
            ],
            'h4' => [
                'style',
            ],
            'blockquote' => [
                'style',
            ],
            'ul' => [
                'style',
            ],
            'ol' => [
                'style',
            ],
            'li' => [
                'style',
            ],
            'figure' => [
                'style',
            ],
            'figcaption' => [
                'style',
            ],
            'pre' => [
                'style',
            ],
            'code' => [
                'style',
            ],
        ];

        $remove = [];

        foreach ($element->childNodes as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array(
                $tag,
                [
                    'script',
                    'style',
                    'iframe',
                    'object',
                    'embed',
                    'form',
                    'svg',
                    'math',
                    'video',
                    'audio',
                    'source',
                ],
                true
            )) {
                $remove[] = $child;
                continue;
            }

            $this->sanitizeNode($child);
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
            $lowerName = strtolower($name);
            $tag = strtolower($element->tagName);

            if (
                str_starts_with($lowerName, 'on')
                || ! in_array(
                    $lowerName,
                    $allowedAttributes[$tag] ?? [],
                    true
                )
            ) {
                $element->removeAttribute($name);
                continue;
            }

            if ($lowerName === 'style') {
                $safeStyle = $this->sanitizeInlineStyle($value);

                if ($safeStyle === '') {
                    $element->removeAttribute($name);
                } else {
                    $element->setAttribute('style', $safeStyle);
                }

                continue;
            }

            if (in_array($lowerName, ['href', 'src'], true)) {
                $compact = preg_replace(
                    '/[\x00-\x20]+/',
                    '',
                    $value
                );

                $scheme = parse_url(
                    $compact,
                    PHP_URL_SCHEME
                );

                $isDataImage = $lowerName === 'src'
                    && preg_match(
                        '/^data:image\/(png|jpeg|gif|webp);base64,/i',
                        $compact
                    );

                if (
                    $scheme !== null
                    && ! in_array(
                        strtolower($scheme),
                        ['http', 'https'],
                        true
                    )
                    && ! $isDataImage
                ) {
                    $element->removeAttribute($name);
                    continue;
                }

                if (
                    str_starts_with(strtolower($compact), 'data:')
                    && ! $isDataImage
                ) {
                    $element->removeAttribute($name);
                    continue;
                }

                if (
                    $lowerName === 'href'
                    && preg_match(
                        '/^\s*(javascript|vbscript|file):/i',
                        $value
                    )
                ) {
                    $element->removeAttribute($name);
                    continue;
                }
            }

            if ($tag === 'a' && $lowerName === 'target') {
                if (! in_array($value, ['_blank', '_self'], true)) {
                    $element->removeAttribute($name);
                } elseif ($value === '_blank') {
                    $element->setAttribute(
                        'rel',
                        'noopener noreferrer'
                    );
                }
            }
        }
    }

    private function sanitizeInlineStyle(string $style): string
    {
        $allowedProperties = [
            'color',
            'background-color',
            'border',
            'border-collapse',
            'padding',
            'text-align',
            'font-weight',
            'font-style',
            'text-decoration',
            'width',
            'max-width',
            'height',
            'vertical-align',
            'white-space',
            'margin',
        ];

        $safeDeclarations = [];

        foreach (explode(';', $style) as $declaration) {
            if (! str_contains($declaration, ':')) {
                continue;
            }

            [$property, $value] = array_map(
                'trim',
                explode(':', $declaration, 2)
            );

            $property = strtolower($property);
            $value = trim($value);

            if (
                ! in_array($property, $allowedProperties, true)
                || $value === ''
                || preg_match(
                    '/(url\s*\(|expression\s*\(|javascript:|vbscript:|@import|behavior\s*:|[<>])/i',
                    $value
                )
            ) {
                continue;
            }

            if (
                in_array(
                    $property,
                    ['color', 'background-color'],
                    true
                )
                && ! preg_match(
                    '/^(#[0-9a-f]{3,8}|rgba?\([\d\s.,%]+\)|hsla?\([\d\s.,%]+\)|[a-z]{1,30}|transparent|currentcolor)$/i',
                    $value
                )
            ) {
                continue;
            }

            if (
                $property === 'font-weight'
                && ! preg_match(
                    '/^(normal|bold|bolder|lighter|[1-9]00)$/i',
                    $value
                )
            ) {
                continue;
            }

            if (
                $property === 'text-align'
                && ! in_array(
                    strtolower($value),
                    ['left', 'right', 'center', 'justify', 'start', 'end'],
                    true
                )
            ) {
                continue;
            }

            $safeDeclarations[] = $property . ': ' . $value;
        }

        return implode('; ', $safeDeclarations);
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