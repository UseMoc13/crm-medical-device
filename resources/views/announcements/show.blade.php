@extends('layouts.app')

@section('title', $announcement->title)

@section('content')

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
                    Published {{ $announcement->published_at->format('d M Y, H:i') }}
                </span>

                @if($announcement->creator)
                    <span class="announcement-detail-author">
                        <span>◉</span>
                        {{ $announcement->creator->name }}
                    </span>
                @endif
            </div>

            @if($announcement->thumbnail_path)
                <div class="announcement-detail-thumbnail">
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($announcement->thumbnail_path) }}"
                        alt="{{ $announcement->title }}">
                </div>
            @endif

            @if($announcement->summary)
                <div class="announcement-detail-summary">
                    {{ $announcement->summary }}
                </div>
            @endif

            <div class="announcement-detail-content">
                {!! nl2br(e($announcement->content)) !!}
            </div>

            @php
                $images = $announcement->media
                    ->where('media_type', 'image');

                $otherMedia = $announcement->media
                    ->where('media_type', '!=', 'image');
            @endphp

            @if($images->isNotEmpty())
                <section class="announcement-detail-section">
                    <h2>Images</h2>

                    <div class="announcement-image-grid">
                        @foreach($images as $image)
                            <a
                                href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->file_path) }}"
                                class="announcement-image-item"
                                target="_blank"
                                rel="noopener noreferrer">

                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->file_path) }}"
                                    alt="{{ $image->alt_text ?: $image->original_name }}"
                                    loading="lazy">

                                <span>{{ $image->original_name }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($otherMedia->isNotEmpty())
                <section class="announcement-detail-section">
                    <h2>Additional Media</h2>

                    <div class="announcement-attachment-list">
                        @foreach($otherMedia as $media)
                            <a
                                href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($media->file_path) }}"
                                class="announcement-attachment"
                                target="_blank"
                                rel="noopener noreferrer">

                                <span class="announcement-attachment-icon">▧</span>

                                <span class="announcement-attachment-info">
                                    <strong>{{ $media->original_name }}</strong>
                                    <small>
                                        {{ $media->file_size ? number_format($media->file_size / 1024, 1) . ' KB' : 'Media file' }}
                                    </small>
                                </span>

                                <span class="announcement-attachment-download">↗</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($announcement->attachments->isNotEmpty())
                <section class="announcement-detail-section">
                    <h2>Attachments</h2>

                    <div class="announcement-attachment-list">
                        @foreach($announcement->attachments as $attachment)
                            <a
                                href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($attachment->file_path) }}"
                                class="announcement-attachment"
                                target="_blank"
                                rel="noopener noreferrer">

                                <span class="announcement-attachment-icon">📎</span>

                                <span class="announcement-attachment-info">
                                    <strong>{{ $attachment->original_name }}</strong>
                                    <small>
                                        {{ $attachment->file_size ? number_format($attachment->file_size / 1024, 1) . ' KB' : 'Attachment' }}
                                    </small>
                                </span>

                                <span class="announcement-attachment-download">↗</span>
                            </a>
                        @endforeach
                    </div>
                </section>
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
                <strong>{{ $announcement->published_at->format('d M Y') }}</strong>
            </div>

            <div class="announcement-info-item">
                <span class="announcement-info-label">Published Time</span>
                <strong>{{ $announcement->published_at->format('H:i') }}</strong>
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
                <span class="announcement-info-label">Attachments</span>
                <strong>{{ $announcement->attachments->count() + $otherMedia->count() }}</strong>
            </div>

            <a href="{{ route('announcements.index') }}" class="btn announcement-back-button">
                View All Announcements
            </a>
        </div>
    </aside>
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

    .announcement-detail-thumbnail img {
        display: block;
        width: 100%;
        max-height: 440px;
        object-fit: contain;
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

    .announcement-detail-section {
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #edf0f5;
    }

    .announcement-detail-section h2 {
        margin: 0 0 16px;
        color: #17284f;
        font-size: 16px;
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
        color: #34415c;
        font-size: 12px;
        text-decoration: none;
    }

    .announcement-image-item img {
        display: block;
        width: 100%;
        height: 170px;
        border: 1px solid #edf0f5;
        border-radius: 9px;
        background: #f5f7fb;
        object-fit: cover;
    }

    .announcement-image-item span {
        overflow-wrap: anywhere;
    }

    .announcement-attachment-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .announcement-attachment {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        padding: 13px;
        border: 1px solid #e6eaf1;
        border-radius: 9px;
        color: #34415c;
        text-decoration: none;
        transition: border-color .18s ease, background .18s ease;
    }

    .announcement-attachment:hover {
        border-color: #a9d8d2;
        background: #f7fbfa;
    }

    .announcement-attachment-icon {
        display: flex;
        flex: 0 0 38px;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background: #edf7f5;
        font-size: 17px;
    }

    .announcement-attachment-info {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
    }

    .announcement-attachment-info strong {
        overflow-wrap: anywhere;
        font-size: 12px;
    }

    .announcement-attachment-info small {
        color: #8a94a6;
        font-size: 11px;
    }

    .announcement-attachment-download {
        color: #238c85;
        font-size: 17px;
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

    @media (max-width: 600px) {
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
    }
</style>

@endsection
