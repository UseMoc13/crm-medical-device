@extends('layouts.app')

@section('title', 'Announcements')

@section('content')

@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

$fileUrl = fn (?string $path) => $path ? '/storage/' . ltrim($path, '/') : null;

$formatDate = function ($date, $format = 'd M Y') {
return $date ? $date->format($format) : 'Date unavailable';
};
@endphp

<div class="page-head">
    <div>
        <h1>Announcements</h1>
        <p>Stay updated with the latest company announcements and information.</p>
    </div>
</div>

<div class="card announcement-card">
    <div class="card-head">
        <div>
            <h3>Latest Announcements</h3>
            <p>{{ $announcements->total() }} announcements found</p>
        </div>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('announcements.index') }}" class="announcement-filter-bar">
            <div class="announcement-search">
                <span class="announcement-search-icon">⌕</span>

                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search announcements..."
                    autocomplete="off"
                    aria-label="Search announcements">

                @if(request('search'))
                <a
                    href="{{ route('announcements.index') }}"
                    class="announcement-search-clear"
                    title="Clear search"
                    aria-label="Clear search">×</a>
                @endif
            </div>

            <button type="submit" class="btn primary announcement-search-button">
                Search
            </button>

            @if(request('search'))
            <a href="{{ route('announcements.index') }}" class="btn announcement-reset-button">
                Reset
            </a>
            @endif
        </form>

        @if(request('search'))
        <div class="announcement-filter-summary">
            <span>Search results for</span>
            <span class="announcement-filter-chip">{{ request('search') }}</span>
        </div>
        @endif

        @if($announcements->isNotEmpty())
        <div class="announcement-list">
            @foreach($announcements as $announcement)
            @php
            $thumbnailUrl = null;

            if ($announcement->thumbnail_path && Storage::disk('public')->exists($announcement->thumbnail_path)) {
            $thumbnailUrl = $fileUrl($announcement->thumbnail_path);
            }

            $publishedDate = $announcement->published_at;
            $summary = trim((string) $announcement->summary);

            if ($summary === '') {
            $summary = Str::limit(
            trim(preg_replace('/\s+/', ' ', strip_tags((string) $announcement->content))),
            180
            );
            }
            @endphp

            <article class="announcement-item {{ $announcement->is_pinned ? 'is-pinned' : '' }}">
                <a
                    href="{{ route('announcements.show', $announcement) }}"
                    class="announcement-thumbnail {{ $thumbnailUrl ? '' : 'announcement-thumbnail-placeholder' }}"
                    aria-label="View {{ $announcement->title }}">

                    @if($thumbnailUrl)
                    <img
                        src="{{ $thumbnailUrl }}"
                        alt="{{ $announcement->title }}"
                        loading="lazy">
                    @else
                    <span class="announcement-placeholder-icon">▤</span>
                    @endif
                </a>

                <div class="announcement-item-content">
                    <div class="announcement-meta">
                        @if($announcement->is_pinned)
                        <span class="announcement-pinned">
                            <span>📌</span> Pinned
                        </span>
                        @endif

                        @if($announcement->category)
                        <span class="announcement-category">
                            {{ $announcement->category }}
                        </span>
                        @endif

                        <span class="announcement-date">
                            {{ $formatDate($publishedDate) }}
                        </span>
                    </div>

                    <h3>
                        <a href="{{ route('announcements.show', $announcement) }}">
                            {{ $announcement->title }}
                        </a>
                    </h3>

                    @if($summary !== '')
                    <p class="announcement-summary">{{ $summary }}</p>
                    @endif

                    <div class="announcement-item-footer">
                        <a
                            href="{{ route('announcements.show', $announcement) }}"
                            class="announcement-read-more">
                            Read announcement <span>→</span>
                        </a>

                        <div class="announcement-extra">
                            @if(($announcement->attachments_count ?? 0) > 0)
                            <span title="Attachments">
                                <span>📎</span>
                                {{ $announcement->attachments_count }}
                            </span>
                            @endif

                            @if(($announcement->media_count ?? 0) > 0)
                            <span title="Media">
                                <span>▧</span>
                                {{ $announcement->media_count }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </article>
            @endforeach
        </div>

        @if($announcements->hasPages())
        <div class="pagination announcement-pagination">
            {{ $announcements->withQueryString()->links() }}
        </div>
        @endif
        @else
        <div class="announcement-empty">
            <div class="announcement-empty-icon">▤</div>

            <h3>No announcements found</h3>

            @if(request('search'))
            <p>Try a different keyword or clear your search to see all available announcements.</p>

            <a href="{{ route('announcements.index') }}" class="btn primary">
                Clear Search
            </a>
            @else
            <p>There are no published announcements available at the moment. Please check back later.</p>
            @endif
        </div>
        @endif
    </div>
</div>

<style>
    .announcement-card {
        overflow: hidden;
    }

    .announcement-filter-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
    }

    .announcement-search {
        position: relative;
        flex: 1 1 auto;
        min-width: 0;
    }

    .announcement-search input {
        box-sizing: border-box;
        width: 100%;
        height: 42px;
        padding: 0 40px;
        border: 1px solid #d9dee8;
        border-radius: 8px;
        background: #fff;
        color: #17284f;
        font-size: 13px;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .announcement-search input:focus {
        outline: none;
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
    }

    .announcement-search-icon {
        position: absolute;
        top: 50%;
        left: 13px;
        transform: translateY(-50%);
        color: #7d8797;
        font-size: 20px;
        pointer-events: none;
    }

    .announcement-search-clear {
        position: absolute;
        top: 50%;
        right: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 23px;
        height: 23px;
        transform: translateY(-50%);
        border-radius: 50%;
        color: #7d8797;
        font-size: 19px;
        text-decoration: none;
    }

    .announcement-search-clear:hover {
        background: #edf1f5;
        color: #17284f;
    }

    .announcement-search-button,
    .announcement-reset-button {
        min-height: 42px;
        white-space: nowrap;
    }

    .announcement-filter-summary {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 16px;
        color: #7d8797;
        font-size: 12px;
    }

    .announcement-filter-chip {
        padding: 5px 9px;
        border-radius: 6px;
        background: #f1f5f7;
        color: #34415c;
        overflow-wrap: anywhere;
    }

    .announcement-list {
        display: flex;
        flex-direction: column;
    }

    .announcement-item {
        display: flex;
        gap: 20px;
        padding: 22px 0;
        border-bottom: 1px solid #edf0f5;
    }

    .announcement-item:first-child {
        padding-top: 8px;
    }

    .announcement-item:last-child {
        padding-bottom: 8px;
        border-bottom: none;
    }

    .announcement-item.is-pinned {
        margin: 0 -12px;
        padding-right: 12px;
        padding-left: 12px;
        border-radius: 10px;
        background: #f7fafc;
    }

    .announcement-thumbnail {
        display: flex;
        flex: 0 0 174px;
        align-items: center;
        justify-content: center;
        width: 174px;
        height: 132px;
        overflow: hidden;
        border: 1px solid #edf0f5;
        border-radius: 10px;
        background: #f3f6fa;
        text-decoration: none;
    }

    .announcement-thumbnail img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .2s ease;
    }

    .announcement-thumbnail:hover img {
        transform: scale(1.04);
    }

    .announcement-thumbnail-placeholder {
        background: linear-gradient(135deg, #edf3fb, #e3f4f0);
        color: #2ba7a0;
    }

    .announcement-placeholder-icon {
        font-size: 38px;
    }

    .announcement-item-content {
        display: flex;
        flex: 1;
        flex-direction: column;
        min-width: 0;
    }

    .announcement-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 9px;
        font-size: 11px;
    }

    .announcement-pinned {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 8px;
        border-radius: 5px;
        background: #fff4df;
        color: #986416;
        font-weight: 600;
    }

    .announcement-category {
        padding: 4px 8px;
        border-radius: 5px;
        background: #eaf7f3;
        color: #167d70;
        font-weight: 600;
    }

    .announcement-date {
        color: #8a94a6;
    }

    .announcement-item-content h3 {
        margin: 0 0 8px;
        color: #17284f;
        font-size: 17px;
        font-weight: 650;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .announcement-item-content h3 a {
        color: inherit;
        text-decoration: none;
        transition: color .18s ease;
    }

    .announcement-item-content h3 a:hover {
        color: #238c85;
    }

    .announcement-summary {
        display: -webkit-box;
        overflow: hidden;
        margin: 0 0 14px;
        color: #68758a;
        font-size: 13px;
        line-height: 1.7;
        overflow-wrap: anywhere;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
    }

    .announcement-item-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: auto;
    }

    .announcement-read-more {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #238c85;
        font-size: 12px;
        font-weight: 650;
        text-decoration: none;
    }

    .announcement-read-more:hover {
        color: #17284f;
    }

    .announcement-read-more span {
        font-size: 16px;
        transition: transform .18s ease;
    }

    .announcement-read-more:hover span {
        transform: translateX(3px);
    }

    .announcement-extra {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #8a94a6;
        font-size: 11px;
    }

    .announcement-extra span {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .announcement-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 58px 20px;
        text-align: center;
    }

    .announcement-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 64px;
        height: 64px;
        margin-bottom: 16px;
        border-radius: 18px;
        background: #edf7f5;
        color: #2ba7a0;
        font-size: 30px;
    }

    .announcement-empty h3 {
        margin: 0 0 8px;
        color: #17284f;
        font-size: 17px;
    }

    .announcement-empty p {
        max-width: 440px;
        margin: 0 0 18px;
        color: #7d8797;
        font-size: 13px;
        line-height: 1.7;
    }

    .announcement-pagination {
        margin-top: 24px;
    }

    @media (max-width: 700px) {
        .announcement-filter-bar {
            align-items: stretch;
            flex-wrap: wrap;
        }

        .announcement-search {
            flex-basis: 100%;
        }

        .announcement-search-button,
        .announcement-reset-button {
            flex: 1;
            justify-content: center;
        }

        .announcement-item {
            gap: 13px;
            padding: 18px 0;
        }

        .announcement-thumbnail {
            flex-basis: 105px;
            width: 105px;
            height: 100px;
        }

        .announcement-item-content h3 {
            font-size: 14px;
        }

        .announcement-summary {
            font-size: 12px;
            -webkit-line-clamp: 2;
        }

        .announcement-meta {
            gap: 5px;
        }

        .announcement-item-footer {
            align-items: flex-start;
        }
    }

    @media (max-width: 440px) {
        .announcement-item {
            flex-direction: column;
        }

        .announcement-thumbnail {
            flex-basis: auto;
            width: 100%;
            height: 170px;
        }

        .announcement-item-content h3 {
            font-size: 16px;
        }
    }
</style>

@endsection