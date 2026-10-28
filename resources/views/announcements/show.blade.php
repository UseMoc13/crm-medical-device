@extends('layouts.app')

@section('title', $announcement->title)

@section('content')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $fileUrl = fn (?string $path) => $path ? '/storage/' . ltrim($path, '/') : null;

    $publishedDate = $announcement->published_at;
    $thumbnailUrl = null;

    if ($announcement->thumbnail_path && Storage::disk('public')->exists($announcement->thumbnail_path)) {
        $thumbnailUrl = $fileUrl($announcement->thumbnail_path);
    }

    $images = $announcement->media->filter(function ($item) {
        return $item->media_type === 'image'
            && $item->file_path
            && Storage::disk('public')->exists($item->file_path);
    });

    $otherMedia = $announcement->media->filter(function ($item) {
        return $item->media_type !== 'image'
            && $item->file_path
            && Storage::disk('public')->exists($item->file_path);
    });

    $availableAttachments = $announcement->attachments->filter(function ($item) {
        return $item->file_path
            && Storage::disk('public')->exists($item->file_path);
    });

    $previewKind = function ($mimeType, $fileName) {
        $mime = strtolower((string) $mimeType);
        $extension = strtolower(pathinfo((string) $fileName, PATHINFO_EXTENSION));

        if (Str::startsWith($mime, 'image/') || in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            return 'image';
        }

        if ($mime === 'application/pdf' || $extension === 'pdf') {
            return 'pdf';
        }

        if (Str::startsWith($mime, 'video/') || in_array($extension, ['mp4', 'webm'], true)) {
            return 'video';
        }

        if (Str::startsWith($mime, 'audio/') || in_array($extension, ['mp3', 'wav'], true)) {
            return 'audio';
        }

        return null;
    };

    $formatSize = function ($bytes) {
        if (!$bytes) {
            return 'File size unavailable';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        return number_format($bytes / 1024, 1) . ' KB';
    };

    $fileIcon = function ($mimeType, $fileName) {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (Str::contains((string) $mimeType, 'pdf') || $extension === 'pdf') {
            return 'PDF';
        }

        if (Str::startsWith((string) $mimeType, 'image/')) {
            return 'IMG';
        }

        if (Str::startsWith((string) $mimeType, 'video/')) {
            return 'VID';
        }

        if (Str::startsWith((string) $mimeType, 'audio/')) {
            return 'AUD';
        }

        if (in_array($extension, ['doc', 'docx', 'txt', 'rtf'], true)) {
            return 'DOC';
        }

        if (in_array($extension, ['xls', 'xlsx', 'csv'], true)) {
            return 'XLS';
        }

        if (in_array($extension, ['ppt', 'pptx'], true)) {
            return 'PPT';
        }

        if (in_array($extension, ['zip', 'rar', '7z'], true)) {
            return 'ZIP';
        }

        return strtoupper($extension ?: 'FILE');
    };
@endphp

<div class="page-head announcement-detail-head">
    <div>
        <h1>Announcement Details</h1>
        <p>Read the full announcement and view its related attachments.</p>
    </div>

    <div class="actions">
        <a href="{{ route('announcements.index') }}" class="btn">
            ← Back to Announcements
        </a>
    </div>
</div>

<div class="announcement-detail-layout">
    <main class="card announcement-detail-card">
        <div class="card-body">
            <div class="announcement-detail-meta">
                @if($announcement->is_pinned)
                    <span class="announcement-detail-pinned">📌 Pinned Announcement</span>
                @endif

                @if($announcement->category)
                    <span class="announcement-detail-category">
                        {{ $announcement->category }}
                    </span>
                @endif
            </div>

            <h1 class="announcement-detail-title">
                {{ $announcement->title }}
            </h1>

            <div class="announcement-detail-byline">
                <span class="announcement-detail-date">
                    <span>▦</span>
                    Published {{ $publishedDate ? $publishedDate->format('d M Y, H:i') : 'Date unavailable' }}
                </span>

                @if($announcement->creator)
                    <span class="announcement-detail-author">
                        <span>◉</span>
                        {{ $announcement->creator->name }}
                    </span>
                @endif
            </div>

            @if($thumbnailUrl)
                <div class="announcement-detail-thumbnail">
                    <x-announcement-thumbnail :announcement="$announcement" :url="$thumbnailUrl" />
                </div>
            @endif

            @if($announcement->summary)
                <div class="announcement-detail-summary">
                    {{ $announcement->summary }}
                </div>
            @endif

            <article class="announcement-detail-content">
                {!! $announcement->content !!}
            </article>

            @if($images->isNotEmpty())
                <section class="announcement-detail-section">
                    <div class="announcement-section-heading">
                        <div>
                            <h2>Images</h2>
                            <p>{{ $images->count() }} image{{ $images->count() === 1 ? '' : 's' }} attached</p>
                        </div>
                    </div>

                    <div class="announcement-image-grid">
                        @foreach($images as $image)
                            @php
                                $imageUrl = $fileUrl($image->file_path);
                            @endphp

                            <button
                                type="button"
                                class="announcement-image-item"
                                data-image-url="{{ $imageUrl }}"
                                data-image-alt="{{ $image->alt_text ?: $image->original_name }}"
                                aria-label="Preview {{ $image->original_name }}">

                                <span class="announcement-image-preview">
                                    <img
                                        src="{{ $imageUrl }}"
                                        alt="{{ $image->alt_text ?: $image->original_name }}"
                                        loading="lazy">
                                    <span class="announcement-image-zoom">View image ↗</span>
                                </span>

                                <span class="announcement-image-name">
                                    {{ $image->original_name }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($otherMedia->isNotEmpty())
                <section class="announcement-detail-section">
                    <div class="announcement-section-heading">
                        <div>
                            <h2>Additional Media</h2>
                            <p>Related files and media for this announcement.</p>
                        </div>
                    </div>

                    <div class="announcement-attachment-list">
                        @foreach($otherMedia as $media)
                            @php
                                $mediaUrl = $fileUrl($media->file_path);
                                $mediaMime = strtolower((string) $media->mime_type);
                                $mediaPreview = $previewKind($media->mime_type, $media->original_name);
                            @endphp

                            <div class="announcement-file-card">
                                <div class="announcement-file-row">
                                    <span class="announcement-file-icon">
                                        {{ $fileIcon($media->mime_type, $media->original_name) }}
                                    </span>

                                    <span class="announcement-attachment-info">
                                        <strong>{{ $media->original_name }}</strong>
                                        <small>{{ $formatSize($media->file_size) }}</small>
                                    </span>

                                    <div class="announcement-file-actions">
                                        @if($mediaPreview)
                                            <button
                                                type="button"
                                                class="btn announcement-preview-button"
                                                data-preview-url="{{ $mediaUrl }}"
                                                data-preview-type="{{ $mediaPreview }}"
                                                data-file-name="{{ $media->original_name }}">
                                                Preview
                                            </button>
                                        @endif

                                        <a
                                            href="{{ $mediaUrl }}"
                                            class="btn announcement-open-button"
                                            target="_blank"
                                            rel="noopener noreferrer">
                                            Open ↗
                                        </a>

                                        <a
                                            href="{{ $mediaUrl }}"
                                            class="btn announcement-download-button"
                                            download="{{ $media->original_name }}">
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($availableAttachments->isNotEmpty())
                <section class="announcement-detail-section">
                    <div class="announcement-section-heading">
                        <div>
                            <h2>Attachments</h2>
                            <p>{{ $availableAttachments->count() }} file{{ $availableAttachments->count() === 1 ? '' : 's' }} available</p>
                        </div>
                    </div>

                    <div class="announcement-attachment-list">
                        @foreach($availableAttachments as $attachment)
                            @php
                                $attachmentUrl = $fileUrl($attachment->file_path);
                                $attachmentMime = strtolower((string) $attachment->mime_type);
                                $attachmentExtension = strtolower(pathinfo($attachment->original_name, PATHINFO_EXTENSION));
                                $attachmentPreview = $previewKind($attachment->mime_type, $attachment->original_name);
                            @endphp

                            <div class="announcement-file-card">
                                <div class="announcement-file-row">
                                    <span class="announcement-file-icon">
                                        {{ $fileIcon($attachment->mime_type, $attachment->original_name) }}
                                    </span>

                                    <span class="announcement-attachment-info">
                                        <strong>{{ $attachment->original_name }}</strong>
                                        <small>{{ $formatSize($attachment->file_size) }}</small>
                                    </span>

                                    <div class="announcement-file-actions">
                                        @if($attachmentPreview)
                                            <button
                                                type="button"
                                                class="btn announcement-preview-button"
                                                data-preview-url="{{ $attachmentUrl }}"
                                                data-preview-type="{{ $attachmentPreview }}"
                                                data-file-name="{{ $attachment->original_name }}">
                                                Preview
                                            </button>
                                        @endif

                                        <a
                                            href="{{ $attachmentUrl }}"
                                            class="btn announcement-open-button"
                                            target="_blank"
                                            rel="noopener noreferrer">
                                            Open ↗
                                        </a>

                                        <a
                                            href="{{ $attachmentUrl }}"
                                            class="btn announcement-download-button"
                                            download="{{ $attachment->original_name }}">
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if(!$thumbnailUrl && $images->isEmpty() && $otherMedia->isEmpty() && $availableAttachments->isEmpty())
                <div class="announcement-no-files">
                    <span>▤</span>
                    <p>This announcement has no additional images or attachments.</p>
                </div>
            @endif
        </div>
    </main>

    <aside class="card announcement-detail-sidebar">
        <div class="card-head">
            <div>
                <h3>Announcement Information</h3>
                <p>Publication details</p>
            </div>
        </div>

        <div class="card-body">
            <div class="announcement-info-item">
                <span class="announcement-info-label">Category</span>
                <strong>{{ $announcement->category ?: 'General' }}</strong>
            </div>

            <div class="announcement-info-item">
                <span class="announcement-info-label">Published Date</span>
                <strong>{{ $publishedDate ? $publishedDate->format('d M Y') : 'Not available' }}</strong>
            </div>

            <div class="announcement-info-item">
                <span class="announcement-info-label">Published Time</span>
                <strong>{{ $publishedDate ? $publishedDate->format('H:i') : 'Not available' }}</strong>
            </div>

            <div class="announcement-info-item">
                <span class="announcement-info-label">Valid Until</span>
                <strong>
                    {{ $announcement->expires_at ? $announcement->expires_at->format('d M Y') : 'No expiration' }}
                </strong>
            </div>

            <div class="announcement-info-item">
                <span class="announcement-info-label">Images</span>
                <strong>{{ $images->count() }}</strong>
            </div>

            <div class="announcement-info-item">
                <span class="announcement-info-label">Additional Media</span>
                <strong>{{ $otherMedia->count() }}</strong>
            </div>

            <div class="announcement-info-item">
                <span class="announcement-info-label">Attachments</span>
                <strong>{{ $availableAttachments->count() }}</strong>
            </div>

            <a href="{{ route('announcements.index') }}" class="btn announcement-back-button">
                View All Announcements
            </a>
        </div>
    </aside>
</div>

<div class="announcement-preview-modal" id="announcementPreviewModal" aria-hidden="true">
    <div class="announcement-preview-backdrop" data-preview-close></div>

    <div class="announcement-preview-dialog" role="dialog" aria-modal="true" aria-labelledby="announcementPreviewTitle">
        <div class="announcement-preview-header">
            <div>
                <h2 id="announcementPreviewTitle">File Preview</h2>
                <p id="announcementPreviewName"></p>
            </div>

            <button type="button" class="announcement-preview-close" data-preview-close aria-label="Close preview">
                ×
            </button>
        </div>

        <div class="announcement-preview-body" id="announcementPreviewBody"></div>

        <div class="announcement-preview-footer">
            <a href="#" id="announcementPreviewOpen" target="_blank" rel="noopener noreferrer" class="btn">
                Open in new tab ↗
            </a>

            <a href="#" id="announcementPreviewDownload" class="btn primary" download>
                Download
            </a>

            <button type="button" class="btn" data-preview-close>Close</button>
        </div>
    </div>
</div>

<style>
    .announcement-detail-head {
        gap: 16px;
        flex-wrap: wrap;
    }

    .announcement-detail-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 280px;
        align-items: start;
        gap: 20px;
    }

    .announcement-detail-card {
        min-width: 0;
    }

    .announcement-detail-card > .card-body {
        padding: 28px;
    }

    .announcement-detail-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 14px;
    }

    .announcement-detail-pinned,
    .announcement-detail-category {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
    }

    .announcement-detail-pinned {
        background: #fff4df;
        color: #986416;
    }

    .announcement-detail-category {
        background: #eaf7f3;
        color: #167d70;
    }

    .announcement-detail-title {
        margin: 0 0 16px;
        color: #17284f;
        font-size: clamp(23px, 3vw, 31px);
        font-weight: 700;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .announcement-detail-byline {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 14px;
        padding-bottom: 22px;
        border-bottom: 1px solid #edf0f5;
        color: #7d8797;
        font-size: 12px;
    }

    .announcement-detail-date,
    .announcement-detail-author {
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .announcement-detail-thumbnail {
        margin: 24px 0;
        overflow: hidden;
        border-radius: 11px;
        background: #f3f6fa;
    }


    .announcement-detail-summary {
        margin: 24px 0;
        padding: 17px 19px;
        border-left: 3px solid #2ba7a0;
        border-radius: 0 8px 8px 0;
        background: #f4f9f8;
        color: #34415c;
        font-size: 14px;
        font-weight: 500;
        line-height: 1.8;
        overflow-wrap: anywhere;
    }

    .announcement-detail-content {
        color: #46536a;
        font-size: 14px;
        line-height: 1.9;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .announcement-detail-content > :first-child {
        margin-top: 0;
    }

    .announcement-detail-content > :last-child {
        margin-bottom: 0;
    }

    .announcement-detail-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
    }

    .announcement-detail-content table {
        display: block;
        max-width: 100%;
        overflow-x: auto;
        border-collapse: collapse;
    }

    .announcement-detail-content td,
    .announcement-detail-content th {
        padding: 8px;
        border: 1px solid #d9dee8;
    }

    .announcement-detail-content pre {
        max-width: 100%;
        padding: 12px;
        overflow-x: auto;
        border-radius: 8px;
        background: #f3f6fa;
    }

    .announcement-detail-content a {
        color: #238c85;
        text-decoration: underline;
        overflow-wrap: anywhere;
    }

    .announcement-detail-section {
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #edf0f5;
    }

    .announcement-section-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .announcement-section-heading h2 {
        margin: 0 0 5px;
        color: #17284f;
        font-size: 16px;
    }

    .announcement-section-heading p {
        margin: 0;
        color: #8a94a6;
        font-size: 12px;
    }

    .announcement-image-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .announcement-image-item {
        display: flex;
        flex-direction: column;
        gap: 9px;
        min-width: 0;
        padding: 0;
        border: 0;
        background: transparent;
        color: #34415c;
        text-align: left;
        font: inherit;
        cursor: pointer;
    }

    .announcement-image-preview {
        position: relative;
        display: block;
        overflow: hidden;
        border-radius: 9px;
        background: #f5f7fb;
    }

    .announcement-image-item img {
        display: block;
        width: 100%;
        height: 170px;
        border: 1px solid #edf0f5;
        border-radius: 9px;
        object-fit: cover;
        transition: transform .2s ease;
    }

    .announcement-image-item:hover img {
        transform: scale(1.03);
    }

    .announcement-image-zoom {
        position: absolute;
        right: 8px;
        bottom: 8px;
        padding: 6px 9px;
        border-radius: 6px;
        background: rgba(23, 40, 79, .88);
        color: #fff;
        font-size: 11px;
    }

    .announcement-image-name {
        overflow-wrap: anywhere;
        font-size: 12px;
    }

    .announcement-attachment-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .announcement-file-card {
        min-width: 0;
        padding: 13px;
        border: 1px solid #e6eaf1;
        border-radius: 9px;
        transition: border-color .18s ease, background .18s ease;
    }

    .announcement-file-card:hover {
        border-color: #c5e2de;
        background: #fbfdfd;
    }

    .announcement-file-row {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .announcement-file-icon {
        display: flex;
        flex: 0 0 42px;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 8px;
        background: #edf7f5;
        color: #167d70;
        font-size: 10px;
        font-weight: 750;
        overflow-wrap: anywhere;
    }

    .announcement-attachment-info {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 5px;
        min-width: 0;
    }

    .announcement-attachment-info strong {
        color: #34415c;
        font-size: 12px;
        overflow-wrap: anywhere;
    }

    .announcement-attachment-info small {
        color: #8a94a6;
        font-size: 11px;
    }

    .announcement-file-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 6px;
    }

    .announcement-file-actions .btn {
        min-height: 32px;
        padding: 6px 9px;
        font-size: 11px;
        white-space: nowrap;
    }

    .announcement-preview-button {
        border: 1px solid #b9ded9;
        background: #eff9f7;
        color: #167d70;
    }

    .announcement-open-button {
        border: 1px solid #e0e5ed;
        background: #fff;
        color: #34415c;
    }

    .announcement-download-button {
        border: 1px solid #223a70;
        background: #223a70;
        color: #fff;
    }

    .announcement-no-files {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 28px;
        padding: 16px;
        border-radius: 9px;
        background: #f6f8fb;
        color: #7d8797;
        font-size: 12px;
    }

    .announcement-no-files > span {
        color: #2ba7a0;
        font-size: 23px;
    }

    .announcement-no-files p {
        margin: 0;
    }

    .announcement-detail-sidebar .card-body {
        padding: 20px;
    }

    .announcement-info-item {
        display: flex;
        flex-direction: column;
        gap: 7px;
        padding: 0 0 15px;
        margin-bottom: 15px;
        border-bottom: 1px solid #edf0f5;
    }

    .announcement-info-label {
        color: #8a94a6;
        font-size: 11px;
    }

    .announcement-info-item strong {
        color: #17284f;
        font-size: 13px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .announcement-back-button {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        box-sizing: border-box;
        margin-top: 6px;
        text-align: center;
    }

    .announcement-preview-modal {
        position: fixed;
        z-index: 9999;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .announcement-preview-modal.is-open {
        display: flex;
    }

    .announcement-preview-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(12, 22, 43, .72);
    }

    .announcement-preview-dialog {
        position: relative;
        display: flex;
        flex-direction: column;
        width: min(100%, 1000px);
        max-height: 90vh;
        overflow: hidden;
        border-radius: 13px;
        background: #fff;
        box-shadow: 0 20px 70px rgba(0, 0, 0, .25);
    }

    .announcement-preview-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 20px;
        border-bottom: 1px solid #edf0f5;
    }

    .announcement-preview-header h2 {
        margin: 0 0 4px;
        color: #17284f;
        font-size: 16px;
    }

    .announcement-preview-header p {
        margin: 0;
        color: #7d8797;
        font-size: 12px;
        overflow-wrap: anywhere;
    }

    .announcement-preview-close {
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 8px;
        background: #f1f4f8;
        color: #34415c;
        font-size: 25px;
        cursor: pointer;
    }

    .announcement-preview-body {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 180px;
        overflow: auto;
        padding: 18px;
        background: #f3f5f9;
    }

    .announcement-preview-body img {
        display: block;
        max-width: 100%;
        max-height: 65vh;
        object-fit: contain;
    }

    .announcement-preview-body iframe {
        display: block;
        width: 100%;
        height: 65vh;
        border: 0;
        border-radius: 6px;
        background: #fff;
    }

    .announcement-preview-body video {
        display: block;
        max-width: 100%;
        max-height: 65vh;
        border-radius: 6px;
        background: #111;
    }

    .announcement-preview-audio {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 18px;
        width: 100%;
    }

    .announcement-preview-audio span {
        color: #17284f;
        font-size: 52px;
    }

    .announcement-preview-audio audio {
        width: min(480px, 100%);
    }

    .announcement-preview-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
        padding: 14px 20px;
        border-top: 1px solid #edf0f5;
    }

    body.announcement-modal-open {
        overflow: hidden;
    }

    @media (max-width: 1000px) {
        .announcement-detail-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .announcement-detail-sidebar {
            order: -1;
        }

        .announcement-detail-sidebar .card-body {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .announcement-info-item {
            margin: 0;
        }

        .announcement-back-button {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 650px) {
        .announcement-detail-card > .card-body {
            padding: 19px;
        }

        .announcement-detail-sidebar .card-body {
            grid-template-columns: minmax(0, 1fr);
        }

        .announcement-detail-title {
            font-size: 23px;
        }

        .announcement-image-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .announcement-image-item img {
            height: 200px;
        }

        .announcement-file-row {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .announcement-attachment-info {
            padding-top: 3px;
        }

        .announcement-file-actions {
            flex-basis: 100%;
            justify-content: flex-start;
            padding-top: 5px;
        }

        .announcement-preview-modal {
            padding: 8px;
        }

        .announcement-preview-dialog {
            max-height: 94vh;
        }

        .announcement-preview-header,
        .announcement-preview-footer {
            padding: 12px;
        }

        .announcement-preview-body {
            padding: 8px;
        }

        .announcement-preview-body iframe {
            height: 60vh;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('announcementPreviewModal');

    if (!modal) {
        return;
    }

    const title = document.getElementById('announcementPreviewTitle');
    const name = document.getElementById('announcementPreviewName');
    const body = document.getElementById('announcementPreviewBody');
    const openLink = document.getElementById('announcementPreviewOpen');
    const downloadLink = document.getElementById('announcementPreviewDownload');

    let previousFocus = null;

    const previewTitles = {
        image: 'Image Preview',
        pdf: 'PDF Preview',
        video: 'Video Preview',
        audio: 'Audio Preview'
    };

    function openPreview(url, fileName, type) {
        if (!url) {
            return;
        }

        previousFocus = document.activeElement;
        title.textContent = previewTitles[type] || 'File Preview';
        name.textContent = fileName || 'Preview file';
        body.replaceChildren();

        if (type === 'image') {
            const image = document.createElement('img');
            image.src = url;
            image.alt = fileName || 'Announcement image';
            body.appendChild(image);
        } else if (type === 'video') {
            const video = document.createElement('video');
            video.src = url;
            video.controls = true;
            video.preload = 'metadata';
            body.appendChild(video);
        } else if (type === 'audio') {
            const wrapper = document.createElement('div');
            const icon = document.createElement('span');
            const audio = document.createElement('audio');

            wrapper.className = 'announcement-preview-audio';
            icon.textContent = '♫';
            audio.src = url;
            audio.controls = true;

            wrapper.append(icon, audio);
            body.appendChild(wrapper);
        } else {
            const frame = document.createElement('iframe');
            frame.src = url;
            frame.title = fileName || 'PDF preview';
            frame.setAttribute('loading', 'lazy');
            body.appendChild(frame);
        }

        openLink.href = url;
        downloadLink.href = url;
        downloadLink.setAttribute('download', fileName || 'download');

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('announcement-modal-open');

        const closeButton = modal.querySelector('.announcement-preview-close');

        if (closeButton) {
            closeButton.focus();
        }
    }

    function closePreview() {
        body.querySelectorAll('video,audio').forEach(function (media) {
            media.pause();
        });

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('announcement-modal-open');
        body.replaceChildren();

        openLink.removeAttribute('href');
        downloadLink.removeAttribute('href');

        if (previousFocus && typeof previousFocus.focus === 'function') {
            previousFocus.focus();
        }
    }

    document.querySelectorAll('[data-image-url]').forEach(function (button) {
        button.addEventListener('click', function () {
            openPreview(
                button.dataset.imageUrl,
                button.dataset.imageAlt,
                'image'
            );
        });
    });

    document.querySelectorAll('[data-preview-url]').forEach(function (button) {
        button.addEventListener('click', function () {
            openPreview(
                button.dataset.previewUrl,
                button.dataset.fileName,
                button.dataset.previewType
            );
        });
    });

    modal.querySelectorAll('[data-preview-close]').forEach(function (element) {
        element.addEventListener('click', closePreview);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closePreview();
        }
    });
});
</script>

@endsection