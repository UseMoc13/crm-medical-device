@extends('layouts.app')

@section('title', 'Announcement Detail')

@section('content')

@php
    $now = now();

    $fileUrl = fn (?string $path) => $path ? '/storage/' . ltrim($path, '/') : null;

    $formatSize = function ($bytes) {
        $bytes = (int) $bytes;

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return number_format($bytes / (1024 * 1024), 2) . ' MB';
    };

    $kindOf = function ($item) {
        $mime = (string) $item->mime_type;
        $ext = strtolower(pathinfo((string) $item->original_name, PATHINFO_EXTENSION));

        if (str_starts_with($mime, 'image/') || in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            return 'image';
        }

        if ($mime === 'application/pdf' || $ext === 'pdf') {
            return 'pdf';
        }

        if (str_starts_with($mime, 'video/') || in_array($ext, ['mp4', 'webm'], true)) {
            return 'video';
        }

        if (str_starts_with($mime, 'audio/') || in_array($ext, ['mp3', 'wav'], true)) {
            return 'audio';
        }

        return 'file';
    };

    $extensionOf = function ($item) {
        $ext = strtoupper(pathinfo((string) $item->original_name, PATHINFO_EXTENSION));

        return $ext !== '' ? substr($ext, 0, 6) : 'FILE';
    };

    $tags = collect(explode(',', (string) $announcement->category))
        ->map(fn ($tag) => trim($tag))
        ->filter()
        ->values();

    $isExpired = $announcement->expires_at && $announcement->expires_at->lte($now);

    $isLive = $announcement->status === 'published'
        && $announcement->published_at
        && $announcement->published_at->lte($now)
        && ! $isExpired;

    if ($isLive) {
        $visibility = ['live', 'Live now', 'This announcement is currently visible in the public announcement list.'];
    } elseif ($announcement->status === 'published' && $isExpired) {
        $visibility = ['expired', 'Expired', 'This announcement has passed its expiration time and is no longer visible to readers.'];
    } elseif ($announcement->status === 'published') {
        $visibility = ['scheduled', 'Waiting for publication time', 'This announcement is published but its publication time has not arrived yet.'];
    } elseif ($announcement->status === 'scheduled') {
        $visibility = ['scheduled', 'Scheduled', 'This announcement will not be visible until it is published.'];
    } elseif ($announcement->status === 'archived') {
        $visibility = ['archived', 'Archived', 'This announcement is kept out of the public announcement list.'];
    } else {
        $visibility = ['draft', 'Private draft', 'This announcement is only visible in the management area.'];
    }

    $primaryDate = $announcement->status === 'scheduled'
        ? $announcement->scheduled_at
        : $announcement->published_at;

    $thumbnailUrl = $fileUrl($announcement->thumbnail_path);
@endphp

<div class="page-head">
    <div>
        <h1>Announcement Detail</h1>
        <p>Review the announcement exactly as its information is stored, and manage it from here.</p>
    </div>

    <div class="actions">
        <a href="{{ route('announcement-management.index') }}" class="btn">← Back</a>

        <form
            action="{{ route('announcement-management.toggle-pin', $announcement) }}"
            method="POST"
            class="ann-inline-form"
            data-ann-confirm="pin"
            data-confirm-pin="{{ $announcement->is_pinned ? 'Unpin' : 'Pin' }}"
            data-announcement-title="{{ $announcement->title }}">
            @csrf
            <button type="submit" class="btn">
                {{ $announcement->is_pinned ? '★ Unpin' : '☆ Pin' }}
            </button>
        </form>

        <a href="{{ route('announcement-management.edit', $announcement) }}" class="btn primary">✎ Edit Announcement</a>
    </div>
</div>

@if(session('success'))
<div class="alert success">{{ session('success') }}</div>
@endif

@if($errors->any())
<div class="alert error">
    <strong>Please check the following errors:</strong>
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="ann-show-layout">
    <div class="ann-show-main">
        <article class="card ann-article-card">
            @if($thumbnailUrl)
            <div class="ann-cover">
                <img src="{{ $thumbnailUrl }}" alt="{{ $announcement->title }}">
            </div>
            @endif

            <div class="card-body ann-article-body">
                <div class="ann-article-badges">
                    <span class="ann-status-badge status-{{ $announcement->status }}">
                        <span class="ann-status-dot"></span>
                        {{ ucfirst($announcement->status) }}
                    </span>

                    @if($announcement->is_pinned)
                    <span class="ann-pin-badge">★ Pinned{{ $announcement->pin_order ? ' · #' . $announcement->pin_order : '' }}</span>
                    @endif

                    @foreach($tags as $tag)
                    <span class="ann-tag">{{ $tag }}</span>
                    @endforeach
                </div>

                <h2 class="ann-article-title">{{ $announcement->title }}</h2>

                <div class="ann-byline">
                    <div class="ann-avatar">{{ strtoupper(mb_substr($announcement->creator?->name ?? 'R', 0, 1)) }}</div>
                    <div>
                        <strong>{{ $announcement->creator?->name ?? 'Unknown author' }}</strong>
                        <span>
                            {{ $primaryDate ? $primaryDate->format('d M Y, H:i') : 'Publication date not set' }}
                        </span>
                    </div>
                </div>

                @if($announcement->summary)
                <p class="ann-summary">{{ $announcement->summary }}</p>
                @endif

                <div class="ann-content">
                    {!! $announcement->content !!}
                </div>
            </div>
        </article>

        @if($announcement->media->isNotEmpty())
        <section class="card">
            <div class="card-head">
                <div>
                    <h3>Media</h3>
                    <p>{{ $announcement->media->count() }} {{ \Illuminate\Support\Str::plural('image', $announcement->media->count()) }} stored with this announcement</p>
                </div>
            </div>

            <div class="card-body">
                <div class="ann-media-grid">
                    @foreach($announcement->media as $media)
                    @php $mediaUrl = $fileUrl($media->file_path); @endphp
                    <button
                        type="button"
                        class="ann-media-item"
                        data-preview
                        data-url="{{ $mediaUrl }}"
                        data-name="{{ $media->original_name }}"
                        data-kind="image"
                        data-meta="{{ strtoupper(pathinfo($media->original_name, PATHINFO_EXTENSION)) }} · {{ $formatSize($media->file_size) }}"
                        title="{{ $media->original_name }}">
                        <img src="{{ $mediaUrl }}" alt="{{ $media->alt_text ?: $media->original_name }}" loading="lazy">
                        <span>{{ $media->original_name }}</span>
                    </button>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <section class="card">
            <div class="card-head">
                <div>
                    <h3>Attachments</h3>
                    <p>
                        @if($announcement->attachments->isEmpty())
                        No files attached
                        @else
                        {{ $announcement->attachments->count() }} {{ \Illuminate\Support\Str::plural('file', $announcement->attachments->count()) }} ·
                        {{ $formatSize($announcement->attachments->sum('file_size')) }} in total
                        @endif
                    </p>
                </div>
            </div>

            <div class="card-body">
                @forelse($announcement->attachments as $attachment)
                @php
                    $attachmentUrl = $fileUrl($attachment->file_path);
                    $kind = $kindOf($attachment);
                    $extension = $extensionOf($attachment);
                    $canPreview = in_array($kind, ['image', 'pdf', 'video', 'audio'], true);
                @endphp

                <div class="ann-file-row">
                    @if($kind === 'image')
                    <img class="ann-file-thumb" src="{{ $attachmentUrl }}" alt="{{ $attachment->original_name }}" loading="lazy">
                    @else
                    <div class="ann-file-icon">{{ $extension }}</div>
                    @endif

                    <div class="ann-file-info">
                        <strong title="{{ $attachment->original_name }}">{{ $attachment->original_name }}</strong>
                        <span>
                            {{ $attachment->mime_type ?: 'Unknown file type' }} · {{ $formatSize($attachment->file_size) }}
                        </span>
                        <span>
                            Uploaded {{ $attachment->created_at?->format('d M Y, H:i') ?? '-' }}
                            @if($attachment->uploader)
                            by {{ $attachment->uploader->name }}
                            @endif
                        </span>
                    </div>

                    <div class="ann-file-actions">
                        @if($canPreview)
                        <button
                            type="button"
                            class="btn"
                            data-preview
                            data-url="{{ $attachmentUrl }}"
                            data-name="{{ $attachment->original_name }}"
                            data-kind="{{ $kind }}"
                            data-meta="{{ $extension }} · {{ $formatSize($attachment->file_size) }}">Preview</button>
                        @endif

                        <a href="{{ $attachmentUrl }}" class="btn" download="{{ $attachment->original_name }}">Download</a>
                    </div>
                </div>
                @empty
                <div class="ann-empty">
                    <div class="ann-empty-icon">▤</div>
                    <p>This announcement has no attachments.</p>
                </div>
                @endforelse
            </div>
        </section>
    </div>

    <aside class="ann-show-side">
        <div class="card ann-side-card ann-visibility-card">
            <div class="ann-visibility-heading">
                <span class="ann-visibility-dot {{ $visibility[0] }}"></span>
                <strong>{{ $visibility[1] }}</strong>
            </div>
            <p>{{ $visibility[2] }}</p>
        </div>

        <div class="card ann-side-card">
            <h3>Publication</h3>
            <dl class="ann-details">
                <div>
                    <dt>Status</dt>
                    <dd>
                        <span class="ann-status-badge status-{{ $announcement->status }}">
                            <span class="ann-status-dot"></span>
                            {{ ucfirst($announcement->status) }}
                        </span>
                    </dd>
                </div>

                @if($announcement->status === 'scheduled')
                <div>
                    <dt>Scheduled for</dt>
                    <dd>{{ $announcement->scheduled_at?->format('d M Y, H:i') ?? '—' }}</dd>
                </div>
                @else
                <div>
                    <dt>Published at</dt>
                    <dd>{{ $announcement->published_at?->format('d M Y, H:i') ?? 'Not published' }}</dd>
                </div>
                @endif

                <div>
                    <dt>Expires at</dt>
                    <dd>{{ $announcement->expires_at?->format('d M Y, H:i') ?? 'No expiration' }}</dd>
                </div>

                <div>
                    <dt>Pinned</dt>
                    <dd>
                        @if($announcement->is_pinned)
                        <span class="ann-pin-badge">★ Pinned{{ $announcement->pin_order ? ' · #' . $announcement->pin_order : '' }}</span>
                        @else
                        Not pinned
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div class="card ann-side-card">
            <h3>Announcement Details</h3>
            <dl class="ann-details">
                <div>
                    <dt>Created</dt>
                    <dd>{{ $announcement->created_at?->format('d M Y, H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Created by</dt>
                    <dd>
                        {{ $announcement->creator?->name ?? 'Unknown' }}
                        @if($announcement->creator?->email)
                        <small>{{ $announcement->creator->email }}</small>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Last updated</dt>
                    <dd>{{ $announcement->updated_at?->format('d M Y, H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Last updated by</dt>
                    <dd>
                        {{ $announcement->updater?->name ?? '—' }}
                        @if($announcement->updater?->email)
                        <small>{{ $announcement->updater->email }}</small>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Slug</dt>
                    <dd class="ann-mono">{{ $announcement->slug }}</dd>
                </div>
                <div>
                    <dt>Files</dt>
                    <dd>📎 {{ $announcement->attachments->count() }} · ▧ {{ $announcement->media->count() }}</dd>
                </div>
            </dl>
        </div>

        <div class="card ann-side-card ann-danger-card">
            <h3>Danger Zone</h3>
            <p>Deleting this announcement also removes its thumbnail, media, and attachments permanently.</p>

            <form
                action="{{ route('announcement-management.destroy', $announcement) }}"
                method="POST"
                data-ann-confirm="delete"
                data-announcement-title="{{ $announcement->title }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn ann-delete-button">🗑 Delete Announcement</button>
            </form>
        </div>
    </aside>
</div>

{{-- Confirm modal (pin / delete) --}}
<div class="ann-modal" id="annConfirmModal" aria-hidden="true">
    <div class="ann-modal-overlay" data-close-confirm></div>

    <div class="ann-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="annConfirmTitle">
        <div class="ann-confirm-icon" id="annConfirmIcon">!</div>

        <div class="ann-confirm-content">
            <h3 id="annConfirmTitle">Confirm action</h3>
            <p id="annConfirmMessage"></p>
            <span id="annConfirmNote">Please confirm before continuing.</span>
        </div>

        <div class="ann-confirm-actions">
            <button type="button" class="btn" id="annConfirmCancel">Cancel</button>
            <button type="button" class="btn" id="annConfirmSubmit">Confirm</button>
        </div>
    </div>
</div>

{{-- File preview modal --}}
<div class="ann-modal" id="annPreviewModal" aria-hidden="true">
    <div class="ann-modal-overlay dark" data-close-preview></div>

    <div class="ann-preview-dialog" role="dialog" aria-modal="true" aria-labelledby="annPreviewTitle">
        <div class="ann-preview-head">
            <div class="ann-preview-heading">
                <h3 id="annPreviewTitle">Preview</h3>
                <p id="annPreviewMeta"></p>
            </div>

            <div class="ann-preview-tools">
                <a href="#" class="btn" id="annPreviewDownload" download>Download</a>
                <button type="button" class="ann-preview-close" id="annPreviewClose" aria-label="Close preview">×</button>
            </div>
        </div>

        <div class="ann-preview-body" id="annPreviewBody"></div>
    </div>
</div>

<style>
    .ann-show-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 22px;
        align-items: start;
    }

    .ann-show-main,
    .ann-show-side {
        display: grid;
        gap: 22px;
        min-width: 0;
    }

    .ann-show-side {
        position: sticky;
        top: 20px;
    }

    .ann-inline-form {
        display: inline-flex;
        margin: 0;
    }

    /* ---------- Article ---------- */
    .ann-article-card {
        overflow: hidden;
        padding: 0;
    }

    .ann-cover {
        width: 100%;
        aspect-ratio: 16 / 7;
        overflow: hidden;
        background: #f1f5f9;
    }

    .ann-cover img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .ann-article-body {
        padding: 28px 32px 34px;
    }

    .ann-article-badges {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 16px;
    }

    .ann-tag {
        display: inline-flex;
        padding: 5px 8px;
        border-radius: 6px;
        background: #eef3f8;
        color: #465771;
        font-size: 11px;
        font-weight: 600;
    }

    .ann-article-title {
        margin: 0 0 18px;
        color: #17284f;
        font-size: 28px;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .ann-byline {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 22px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e5e9f0;
    }

    .ann-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 40px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #17284f;
        color: #fff;
        font-size: 15px;
        font-weight: 700;
    }

    .ann-byline strong {
        display: block;
        color: #17284f;
        font-size: 13px;
    }

    .ann-byline span {
        color: #7d8797;
        font-size: 12px;
    }

    .ann-summary {
        margin: 0 0 24px;
        padding: 14px 18px;
        border-left: 3px solid #2ba7a0;
        border-radius: 0 8px 8px 0;
        background: #f5f9f9;
        color: #34415c;
        font-size: 15px;
        line-height: 1.75;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .ann-content {
        color: #34415c;
        font-size: 15px;
        line-height: 1.85;
        overflow-wrap: anywhere;
    }

    .ann-content > :first-child {
        margin-top: 0;
    }

    .ann-content h2,
    .ann-content h3,
    .ann-content h4 {
        margin: 1.6em 0 .6em;
        color: #17284f;
        line-height: 1.35;
    }

    .ann-content h2 { font-size: 22px; }
    .ann-content h3 { font-size: 19px; }
    .ann-content h4 { font-size: 16px; }

    .ann-content p {
        margin: 0 0 1em;
    }

    .ann-content a {
        color: #1d7f79;
        text-decoration: underline;
    }

    .ann-content ul,
    .ann-content ol {
        margin: 0 0 1em;
        padding-left: 24px;
    }

    .ann-content blockquote {
        margin: 1.2em 0;
        padding: 10px 18px;
        border-left: 3px solid #cbd5e1;
        background: #f8fafc;
        color: #465771;
    }

    .ann-content img {
        display: block;
        max-width: 100%;
        height: auto;
        margin: 1em 0;
        border-radius: 8px;
    }

    .ann-content table {
        display: block;
        max-width: 100%;
        margin: 1.2em 0;
        overflow-x: auto;
        border-collapse: collapse;
    }

    .ann-content pre {
        overflow-x: auto;
        padding: 14px;
        border-radius: 8px;
        background: #f1f5f9;
        font-size: 13px;
    }

    /* ---------- Badges ---------- */
    .ann-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 9px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .ann-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .status-draft { background: #f0f2f5; color: #667085; }
    .status-scheduled { background: #fff5e5; color: #a66b12; }
    .status-published { background: #e8f6f0; color: #16805f; }
    .status-archived { background: #f0edfa; color: #7660a9; }

    .ann-pin-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 8px;
        border-radius: 6px;
        background: #fff5e5;
        color: #a66b12;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* ---------- Media ---------- */
    .ann-media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 12px;
    }

    .ann-media-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 8px;
        border: 1px solid #e5e9f0;
        border-radius: 10px;
        background: #fff;
        color: #526078;
        font-size: 11px;
        text-align: left;
        cursor: pointer;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .ann-media-item:hover {
        border-color: #2ba7a0;
        box-shadow: 0 4px 14px rgba(23, 40, 79, .08);
    }

    .ann-media-item img {
        display: block;
        width: 100%;
        aspect-ratio: 4 / 3;
        object-fit: cover;
        border-radius: 7px;
        background: #f1f5f9;
    }

    .ann-media-item span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ---------- Attachments ---------- */
    .ann-file-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px;
        border: 1px solid #e5e9f0;
        border-radius: 10px;
        background: #fff;
    }

    .ann-file-row + .ann-file-row {
        margin-top: 10px;
    }

    .ann-file-icon,
    .ann-file-thumb {
        flex: 0 0 52px;
        width: 52px;
        height: 52px;
        border-radius: 9px;
    }

    .ann-file-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eef2ff;
        color: #17284f;
        font-size: 11px;
        font-weight: 800;
    }

    .ann-file-thumb {
        object-fit: cover;
        border: 1px solid #e5e9f0;
        background: #f1f5f9;
    }

    .ann-file-info {
        display: grid;
        flex: 1;
        gap: 3px;
        min-width: 0;
    }

    .ann-file-info strong {
        overflow: hidden;
        color: #17284f;
        font-size: 13px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ann-file-info span {
        color: #7d8797;
        font-size: 11px;
    }

    .ann-file-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .ann-empty {
        padding: 26px 16px;
        text-align: center;
    }

    .ann-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        margin: 0 auto 10px;
        border-radius: 12px;
        background: #edf3ff;
        color: #315caa;
        font-size: 22px;
    }

    .ann-empty p {
        margin: 0;
        color: #7d8797;
        font-size: 13px;
    }

    /* ---------- Side panel ---------- */
    .ann-side-card {
        padding: 20px;
    }

    .ann-side-card h3 {
        margin: 0 0 14px;
        color: #17284f;
        font-size: 15px;
    }

    .ann-visibility-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 8px;
        color: #17284f;
        font-size: 14px;
    }

    .ann-visibility-card p {
        margin: 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.65;
    }

    .ann-visibility-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #98a2b3;
    }

    .ann-visibility-dot.live { background: #16a34a; box-shadow: 0 0 0 4px rgba(22, 163, 74, .14); }
    .ann-visibility-dot.scheduled { background: #d68a1b; box-shadow: 0 0 0 4px rgba(214, 138, 27, .14); }
    .ann-visibility-dot.expired { background: #c94a4a; box-shadow: 0 0 0 4px rgba(201, 74, 74, .14); }
    .ann-visibility-dot.archived { background: #7660a9; box-shadow: 0 0 0 4px rgba(118, 96, 169, .14); }
    .ann-visibility-dot.draft { background: #98a2b3; box-shadow: 0 0 0 4px rgba(152, 162, 179, .18); }

    .ann-details {
        display: grid;
        gap: 12px;
        margin: 0;
    }

    .ann-details > div {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        font-size: 12px;
    }

    .ann-details dt {
        flex: 0 0 auto;
        color: #7d8797;
    }

    .ann-details dd {
        margin: 0;
        color: #17284f;
        font-weight: 600;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .ann-details dd small {
        display: block;
        margin-top: 2px;
        color: #8a94a6;
        font-size: 11px;
        font-weight: 400;
    }

    .ann-mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-weight: 500 !important;
    }

    .ann-danger-card {
        border-color: #f0caca;
    }

    .ann-danger-card h3 {
        color: #963d3d;
    }

    .ann-danger-card p {
        margin: 0 0 14px;
        color: #7d8797;
        font-size: 12px;
        line-height: 1.65;
    }

    .ann-delete-button {
        width: 100%;
        justify-content: center;
        border-color: #e6b5b5 !important;
        color: #b83f3f !important;
    }

    .ann-delete-button:hover {
        background: #fff4f4 !important;
    }

    /* ---------- Modals ---------- */
    .ann-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .ann-modal.open {
        display: flex;
    }

    .ann-modal-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 24, 42, .45);
        backdrop-filter: blur(2px);
    }

    .ann-modal-overlay.dark {
        background: rgba(15, 23, 42, .72);
    }

    .ann-confirm-dialog {
        position: relative;
        width: min(420px, calc(100% - 32px));
        padding: 26px;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 20px 60px rgba(23, 40, 79, .2);
        animation: annModalIn .18s ease;
    }

    @keyframes annModalIn {
        from {
            opacity: 0;
            transform: translateY(8px) scale(.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .ann-confirm-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        margin-bottom: 16px;
        border-radius: 50%;
        background: #fff0f0;
        color: #c94a4a;
        font-size: 21px;
        font-weight: 700;
    }

    .ann-confirm-icon.pin {
        background: #fff5e5;
        color: #a66b12;
    }

    .ann-confirm-content h3 {
        margin: 0 0 8px;
        color: #17284f;
        font-size: 18px;
    }

    .ann-confirm-content p {
        margin: 0 0 7px;
        color: #34415c;
        font-size: 13px;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .ann-confirm-content span {
        color: #8a94a6;
        font-size: 12px;
    }

    .ann-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 24px;
    }

    .ann-confirm-actions .confirm-delete {
        border-color: #c94a4a !important;
        background: #c94a4a !important;
        color: #fff !important;
    }

    .ann-confirm-actions .confirm-delete:hover {
        border-color: #b83f3f !important;
        background: #b83f3f !important;
    }

    .ann-confirm-actions .confirm-pin {
        border-color: #a66b12 !important;
        background: #a66b12 !important;
        color: #fff !important;
    }

    .ann-preview-dialog {
        position: relative;
        display: flex;
        flex-direction: column;
        width: min(1100px, calc(100% - 32px));
        max-height: 92vh;
        overflow: hidden;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 24px 80px rgba(0, 0, 0, .25);
        animation: annModalIn .18s ease;
    }

    .ann-preview-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 22px;
        border-bottom: 1px solid #e5e7eb;
    }

    .ann-preview-heading {
        min-width: 0;
    }

    .ann-preview-heading h3 {
        margin: 0;
        color: #17284f;
        font-size: 16px;
        overflow-wrap: anywhere;
    }

    .ann-preview-heading p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
    }

    .ann-preview-tools {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .ann-preview-close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 8px;
        background: #f1f5f9;
        color: #17284f;
        font-size: 23px;
        cursor: pointer;
    }

    .ann-preview-body {
        flex: 1;
        min-height: 220px;
        overflow: auto;
        padding: 20px;
        background: #f5f7fb;
    }

    .ann-preview-body img {
        display: block;
        max-width: 100%;
        max-height: 70vh;
        margin: 0 auto;
        border-radius: 8px;
        background: #fff;
        object-fit: contain;
    }

    .ann-preview-body iframe {
        display: block;
        width: 100%;
        height: 70vh;
        min-height: 420px;
        border: 1px solid #dce2eb;
        border-radius: 8px;
        background: #fff;
    }

    .ann-preview-body video {
        display: block;
        max-width: 100%;
        max-height: 70vh;
        margin: 0 auto;
        border-radius: 8px;
        background: #111;
    }

    .ann-preview-audio {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 20px;
        min-height: 260px;
    }

    .ann-preview-audio span {
        color: #17284f;
        font-size: 52px;
    }

    .ann-preview-audio audio {
        width: min(500px, 100%);
    }

    @media (max-width: 1100px) {
        .ann-show-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .ann-show-side {
            position: static;
        }
    }

    @media (max-width: 650px) {
        .ann-article-body {
            padding: 20px 18px 24px;
        }

        .ann-article-title {
            font-size: 22px;
        }

        .ann-file-row {
            flex-wrap: wrap;
        }

        .ann-file-actions {
            width: 100%;
        }

        .ann-file-actions .btn {
            flex: 1;
            justify-content: center;
        }

        .ann-confirm-dialog {
            padding: 22px;
        }

        .ann-confirm-actions {
            flex-direction: column-reverse;
        }

        .ann-confirm-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    /* ---------- Confirm modal (pin / delete) ---------- */
    const confirmModal = document.getElementById('annConfirmModal');
    const confirmTitle = document.getElementById('annConfirmTitle');
    const confirmMessage = document.getElementById('annConfirmMessage');
    const confirmNote = document.getElementById('annConfirmNote');
    const confirmIcon = document.getElementById('annConfirmIcon');
    const confirmSubmit = document.getElementById('annConfirmSubmit');
    const confirmCancel = document.getElementById('annConfirmCancel');

    let activeForm = null;

    function openConfirm(form, action) {
        activeForm = form;

        const announcementTitle = form.dataset.announcementTitle || 'this announcement';

        if (action === 'delete') {
            confirmTitle.textContent = 'Delete Announcement?';
            confirmMessage.textContent = 'Are you sure you want to delete "' + announcementTitle + '"?';
            confirmNote.textContent = 'This action cannot be undone. Associated media files may also be deleted.';
            confirmIcon.textContent = '!';
            confirmIcon.classList.remove('pin');
            confirmSubmit.textContent = 'Delete Announcement';
            confirmSubmit.className = 'btn confirm-delete';
        } else {
            const pinAction = form.dataset.confirmPin || 'Pin';

            confirmTitle.textContent = pinAction + ' Announcement?';
            confirmMessage.textContent = 'Do you want to ' + pinAction.toLowerCase() + ' "' + announcementTitle + '"?';
            confirmNote.textContent = 'The pin setting can be changed again later.';
            confirmIcon.textContent = '★';
            confirmIcon.classList.add('pin');
            confirmSubmit.textContent = pinAction + ' Announcement';
            confirmSubmit.className = 'btn confirm-pin';
        }

        confirmModal.classList.add('open');
        confirmModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        confirmCancel.focus();
    }

    function closeConfirm() {
        confirmModal.classList.remove('open');
        confirmModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        activeForm = null;
    }

    document.querySelectorAll('form[data-ann-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            openConfirm(form, form.dataset.annConfirm);
        });
    });

    confirmSubmit.addEventListener('click', function () {
        if (activeForm) {
            activeForm.submit();
        }
    });

    confirmCancel.addEventListener('click', closeConfirm);

    document.querySelectorAll('[data-close-confirm]').forEach(function (element) {
        element.addEventListener('click', closeConfirm);
    });

    /* ---------- File preview modal ---------- */
    const previewModal = document.getElementById('annPreviewModal');
    const previewTitle = document.getElementById('annPreviewTitle');
    const previewMeta = document.getElementById('annPreviewMeta');
    const previewBody = document.getElementById('annPreviewBody');
    const previewDownload = document.getElementById('annPreviewDownload');
    const previewClose = document.getElementById('annPreviewClose');

    function openPreview(url, name, kind, meta) {
        previewTitle.textContent = name;
        previewMeta.textContent = meta || '';
        previewDownload.href = url;
        previewDownload.setAttribute('download', name);

        previewBody.replaceChildren();

        if (kind === 'image') {
            const image = document.createElement('img');

            image.src = url;
            image.alt = name;

            previewBody.appendChild(image);
        } else if (kind === 'pdf') {
            const frame = document.createElement('iframe');

            frame.src = url + '#toolbar=1&navpanes=0&view=FitH';
            frame.title = 'PDF preview: ' + name;

            previewBody.appendChild(frame);
        } else if (kind === 'video') {
            const video = document.createElement('video');

            video.src = url;
            video.controls = true;
            video.preload = 'metadata';

            previewBody.appendChild(video);
        } else if (kind === 'audio') {
            const wrapper = document.createElement('div');
            const icon = document.createElement('span');
            const audio = document.createElement('audio');

            wrapper.className = 'ann-preview-audio';
            icon.textContent = '♫';
            audio.src = url;
            audio.controls = true;

            wrapper.append(icon, audio);
            previewBody.appendChild(wrapper);
        }

        previewModal.classList.add('open');
        previewModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        previewClose.focus();
    }

    function closePreview() {
        previewBody.querySelectorAll('video,audio').forEach(function (media) {
            media.pause();
        });

        previewBody.replaceChildren();

        previewModal.classList.remove('open');
        previewModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-preview]').forEach(function (button) {
        button.addEventListener('click', function () {
            openPreview(
                button.dataset.url,
                button.dataset.name,
                button.dataset.kind,
                button.dataset.meta
            );
        });
    });

    previewClose.addEventListener('click', closePreview);

    document.querySelectorAll('[data-close-preview]').forEach(function (element) {
        element.addEventListener('click', closePreview);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        if (previewModal.classList.contains('open')) {
            closePreview();
        } else if (confirmModal.classList.contains('open')) {
            closeConfirm();
        }
    });
});
</script>

@endsection