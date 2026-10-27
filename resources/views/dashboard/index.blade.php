@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@php
    use App\Models\Announcement;
    use Illuminate\Support\Facades\Storage;

    // Prefer $latestAnnouncements passed by DashboardController.
    // Falls back to a direct query so the slider also works without it.
    if (! isset($latestAnnouncements)) {
        Announcement::publishDueScheduled();

        $latestAnnouncements = Announcement::query()
            ->with('creator')
            ->published()
            ->orderByDesc('is_pinned')
            ->orderByRaw('pin_order ASC NULLS LAST')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();
    }
@endphp

<div class="page-head">

    <div>
        <h1>Dashboard</h1>

        <p>
            Overview of CRM Medical Device
        </p>
    </div>

    <div class="actions">

        <button
            class="btn"
            type="button"
            onclick="notify('Export feature is not available yet.')"
        >
            Export
        </button>

        <button
            class="btn primary"
            type="button"
            onclick="notify('New Activity feature is not available yet.')"
        >
            + New Activity
        </button>

    </div>

</div>


{{-- =========================================================
     KPI
========================================================= --}}

<div class="grid kpis">

    {{-- Customers --}}

    <div class="card kpi">

        <div class="label">
            Total Customers
        </div>

        <div class="value">
            {{ $stats['customers'] }}
        </div>

        <div class="trend">
            Customer database
        </div>

    </div>


    {{-- Leads --}}

    <div class="card kpi">

        <div class="label">
            Active Leads
        </div>

        <div class="value">
            {{ $stats['leads'] }}
        </div>

        <div class="trend">
            Current leads
        </div>

    </div>


    {{-- Opportunities --}}

    <div class="card kpi">

        <div class="label">
            Opportunities
        </div>

        <div class="value">
            {{ $stats['opportunities'] }}
        </div>

        <div class="trend">
            Active opportunities
        </div>

    </div>


    {{-- Service Tickets --}}

    <div class="card kpi">

        <div class="label">
            Service Tickets
        </div>

        <div class="value">
            {{ $stats['service_tickets'] }}
        </div>

        <div class="trend">
            Current service tickets
        </div>

    </div>

</div>


{{-- =========================================================
     ANNOUNCEMENT SLIDER
========================================================= --}}

<div
    class="card dash-announcements"
    style="margin-top: 16px;"
    id="dashAnnouncementSlider"
    role="region"
    aria-roledescription="carousel"
    aria-label="Latest announcements"
    tabindex="0"
>

    <div class="card-head dash-announcements-head">

        <h3>
            Latest Announcements
        </h3>

        <div class="dash-head-actions">

            @if($latestAnnouncements->count() > 1)

                <div class="dash-slider-controls">

                    <button
                        type="button"
                        class="dash-slider-arrow"
                        id="dashSliderPrev"
                        aria-label="Previous announcement"
                    >
                        ‹
                    </button>

                    <div class="dash-slider-dots">
                        @foreach($latestAnnouncements as $announcement)
                            <button
                                type="button"
                                class="dash-slider-dot {{ $loop->first ? 'active' : '' }}"
                                data-slide="{{ $loop->index }}"
                                aria-label="Go to announcement {{ $loop->iteration }}"
                            ></button>
                        @endforeach
                    </div>

                    <button
                        type="button"
                        class="dash-slider-arrow"
                        id="dashSliderNext"
                        aria-label="Next announcement"
                    >
                        ›
                    </button>

                </div>

            @endif

            <a
                href="{{ route('announcements.index') }}"
                class="btn"
            >
                View All →
            </a>

        </div>

    </div>

    <div class="card-body dash-announcements-body">

        @if($latestAnnouncements->isEmpty())

            <div class="empty">
                No announcements have been published yet.
            </div>

        @else

            <div class="dash-slider-viewport">

                <div class="dash-slider-track" id="dashSliderTrack">

                    @foreach($latestAnnouncements as $announcement)

                        @php
                            $thumbnailUrl = null;

                            if (
                                $announcement->thumbnail_path
                                && Storage::disk('public')->exists($announcement->thumbnail_path)
                            ) {
                                $thumbnailUrl = '/storage/' . ltrim($announcement->thumbnail_path, '/');
                            }
                        @endphp

                        <a
                            href="{{ route('announcements.show', $announcement) }}"
                            class="dash-slide"
                            role="group"
                            aria-roledescription="slide"
                            aria-label="{{ $loop->iteration }} of {{ $loop->count }}: {{ $announcement->title }}"
                        >

                            <span class="dash-slide-media {{ $thumbnailUrl ? '' : 'is-placeholder' }}">
                                @if($thumbnailUrl)
                                    <img
                                        src="{{ $thumbnailUrl }}"
                                        alt=""
                                        loading="lazy"
                                    >
                                @else
                                    ▤
                                @endif
                            </span>

                            <span class="dash-slide-content">

                                <span class="dash-slide-meta">

                                    @if($announcement->is_pinned)
                                        <span class="dash-pinned">📌 Pinned</span>
                                    @endif

                                    @if($announcement->category)
                                        <span class="dash-category">{{ $announcement->category }}</span>
                                    @endif

                                    <span class="dash-date">
                                        {{ $announcement->published_at?->format('d M Y') ?? '' }}
                                    </span>

                                </span>

                                <strong class="dash-slide-title">
                                    {{ $announcement->title }}
                                </strong>

                            </span>

                            <span class="dash-slide-arrow" aria-hidden="true">→</span>

                        </a>

                    @endforeach

                </div>

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     DASHBOARD CONTENT
========================================================= --}}

<div
    class="grid two"
    style="margin-top: 16px;"
>


    {{-- =====================================================
         SALES OVERVIEW
    ====================================================== --}}

    <div class="card">

        <div class="card-head">

            <h3>
                Sales Overview
            </h3>

        </div>

        <div class="card-body">

            <div class="empty">

                Sales data will appear here.

            </div>

        </div>

    </div>


    {{-- =====================================================
         RECENT ACTIVITIES
    ====================================================== --}}

    <div class="card">

        <div class="card-head">

            <h3>
                Recent Activities
            </h3>

        </div>

        <div class="card-body">

            @if($recentActivities->isEmpty())

                <div class="empty">
                    No recent activities.
                </div>

            @else

                <div class="list">

                    @foreach($recentActivities as $activity)

                        <div class="list-item">

                            <div>

                                <strong>
                                    {{ $activity->title ?? 'Activity' }}
                                </strong>

                                <small>
                                    {{ $activity->description ?? '' }}
                                </small>

                            </div>

                        </div>

                    @endforeach

                </div>

            @endif

        </div>

    </div>

</div>

<style>
    .dash-announcements:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .18);
    }

    .dash-announcements-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .dash-announcements-head h3 {
        margin: 0;
    }

    .dash-head-actions {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-shrink: 0;
    }

    .dash-head-actions .btn {
        white-space: nowrap;
    }

    .dash-announcements-body {
        padding-top: 14px;
        padding-bottom: 14px;
    }

    .dash-slider-viewport {
        overflow: hidden;
        border: 1px solid #e5e9f0;
        border-radius: 10px;
        background: #fff;
    }

    .dash-slider-track {
        display: flex;
        transition: transform .4s ease;
        will-change: transform;
    }

    .dash-slide {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 0 0 100%;
        min-width: 0;
        box-sizing: border-box;
        padding: 12px 16px;
        color: inherit;
        text-decoration: none;
        transition: background .18s ease;
    }

    .dash-slide:hover {
        background: #f8fafc;
    }

    .dash-slide:focus-visible {
        outline: 2px solid #2ba7a0;
        outline-offset: -2px;
    }

    .dash-slide-media {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 88px;
        width: 88px;
        height: 56px;
        overflow: hidden;
        border-radius: 8px;
        background: #f1f5f9;
    }

    .dash-slide-media img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .dash-slide-media.is-placeholder {
        background: linear-gradient(135deg, #edf3fb, #e3f4f0);
        color: #2ba7a0;
        font-size: 24px;
    }

    .dash-slide-content {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
    }

    .dash-slide-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        font-size: 11px;
    }

    .dash-pinned {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 7px;
        border-radius: 5px;
        background: #fff4df;
        color: #986416;
        font-weight: 600;
    }

    .dash-category {
        max-width: 180px;
        padding: 3px 7px;
        overflow: hidden;
        border-radius: 5px;
        background: #eaf7f3;
        color: #167d70;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dash-date {
        color: #8a94a6;
    }

    .dash-slide-title {
        display: block;
        overflow: hidden;
        color: #17284f;
        font-size: 14px;
        line-height: 1.4;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dash-slide:hover .dash-slide-title {
        color: #2ba7a0;
    }

    .dash-slide-arrow {
        flex-shrink: 0;
        color: #8a94a6;
        font-size: 18px;
        transition: transform .18s ease, color .18s ease;
    }

    .dash-slide:hover .dash-slide-arrow {
        color: #2ba7a0;
        transform: translateX(3px);
    }

    .dash-slider-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .dash-slider-arrow {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        padding: 0 0 2px;
        border: 1px solid #d9dee8;
        border-radius: 50%;
        background: #fff;
        color: #17284f;
        font-size: 18px;
        line-height: 1;
        cursor: pointer;
        transition: border-color .18s ease, color .18s ease;
    }

    .dash-slider-arrow:hover {
        border-color: #2ba7a0;
        color: #2ba7a0;
    }

    .dash-slider-dots {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .dash-slider-dot {
        width: 7px;
        height: 7px;
        padding: 0;
        border: 0;
        border-radius: 999px;
        background: #cfd6e2;
        cursor: pointer;
        transition: width .2s ease, background .2s ease;
    }

    .dash-slider-dot.active {
        width: 20px;
        background: #2ba7a0;
    }

    @media (max-width: 640px) {
        .dash-announcements-head {
            flex-wrap: wrap;
        }

        .dash-head-actions {
            width: 100%;
            justify-content: space-between;
        }

        .dash-slide {
            padding: 10px 12px;
        }

        .dash-slide-media {
            flex-basis: 64px;
            width: 64px;
            height: 48px;
        }

        .dash-slide-arrow {
            display: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .dash-slider-track {
            transition: none;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const slider = document.getElementById('dashAnnouncementSlider');
    const track = document.getElementById('dashSliderTrack');

    if (!slider || !track) {
        return;
    }

    const slides = Array.from(track.children);
    const total = slides.length;

    if (total < 2) {
        return;
    }

    const dots = Array.from(slider.querySelectorAll('.dash-slider-dot'));
    const AUTOPLAY_MS = 5000;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let current = 0;
    let timer = null;
    let startX = null;
    let deltaX = 0;

    function goTo(index) {
        current = (index + total) % total;

        track.style.transform = 'translateX(-' + (current * 100) + '%)';

        dots.forEach(function (dot, position) {
            dot.classList.toggle('active', position === current);
        });

        slides.forEach(function (slide, position) {
            slide.tabIndex = position === current ? 0 : -1;
            slide.setAttribute('aria-hidden', position === current ? 'false' : 'true');
        });
    }

    function stop() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    }

    function start() {
        stop();

        if (reduceMotion) {
            return;
        }

        timer = setInterval(function () {
            goTo(current + 1);
        }, AUTOPLAY_MS);
    }

    document.getElementById('dashSliderPrev').addEventListener('click', function () {
        goTo(current - 1);
        start();
    });

    document.getElementById('dashSliderNext').addEventListener('click', function () {
        goTo(current + 1);
        start();
    });

    dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
            goTo(Number(dot.dataset.slide));
            start();
        });
    });

    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);
    slider.addEventListener('focusin', stop);
    slider.addEventListener('focusout', start);

    slider.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowLeft') {
            goTo(current - 1);
        } else if (event.key === 'ArrowRight') {
            goTo(current + 1);
        }
    });

    slider.addEventListener('touchstart', function (event) {
        startX = event.touches[0].clientX;
        deltaX = 0;
        stop();
    }, { passive: true });

    slider.addEventListener('touchmove', function (event) {
        if (startX !== null) {
            deltaX = event.touches[0].clientX - startX;
        }
    }, { passive: true });

    slider.addEventListener('touchend', function () {
        if (Math.abs(deltaX) > 50) {
            goTo(deltaX < 0 ? current + 1 : current - 1);
        }

        startX = null;
        deltaX = 0;
        start();
    });

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            stop();
        } else {
            start();
        }
    });

    goTo(0);
    start();
});
</script>

@endsection