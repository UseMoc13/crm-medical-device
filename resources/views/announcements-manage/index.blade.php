@extends('layouts.app')

@section('title', 'Announcement Management')

@section('content')

<div class="page-head">
    <div>
        <h1>Announcement Management</h1>
        <p>Manage company announcements, important updates, and shared information.</p>
    </div>

    <div class="actions">
        <a href="{{ route('announcement-management.create') }}" class="btn primary">
            + New Announcement
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="announcement-alert-error">
        <strong>Please check the following errors:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="announcement-stats">
    <a href="{{ route('announcement-management.index', request()->except('status', 'page')) }}"
       class="announcement-stat-card {{ !request('status') ? 'active' : '' }}">
        <div class="announcement-stat-icon total-icon">▤</div>
        <div>
            <span class="announcement-stat-label">Total Announcements</span>
            <strong>{{ $statistics['total'] }}</strong>
        </div>
    </a>

    <a href="{{ route('announcement-management.index', array_merge(request()->except('page'), ['status' => 'draft'])) }}"
       class="announcement-stat-card {{ request('status') === 'draft' ? 'active' : '' }}">
        <div class="announcement-stat-icon draft-icon">✎</div>
        <div>
            <span class="announcement-stat-label">Draft</span>
            <strong>{{ $statistics['draft'] }}</strong>
        </div>
    </a>

    <a href="{{ route('announcement-management.index', array_merge(request()->except('page'), ['status' => 'scheduled'])) }}"
       class="announcement-stat-card {{ request('status') === 'scheduled' ? 'active' : '' }}">
        <div class="announcement-stat-icon scheduled-icon">◷</div>
        <div>
            <span class="announcement-stat-label">Scheduled</span>
            <strong>{{ $statistics['scheduled'] }}</strong>
        </div>
    </a>

    <a href="{{ route('announcement-management.index', array_merge(request()->except('page'), ['status' => 'published'])) }}"
       class="announcement-stat-card {{ request('status') === 'published' ? 'active' : '' }}">
        <div class="announcement-stat-icon published-icon">✓</div>
        <div>
            <span class="announcement-stat-label">Published</span>
            <strong>{{ $statistics['published'] }}</strong>
        </div>
    </a>

    <a href="{{ route('announcement-management.index', array_merge(request()->except('page'), ['status' => 'archived'])) }}"
       class="announcement-stat-card {{ request('status') === 'archived' ? 'active' : '' }}">
        <div class="announcement-stat-icon archived-icon">▣</div>
        <div>
            <span class="announcement-stat-label">Archived</span>
            <strong>{{ $statistics['archived'] }}</strong>
        </div>
    </a>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <h3>Announcement List</h3>
            <p>{{ $announcements->total() }} announcements found</p>
        </div>
    </div>

    <div class="card-body">
        <form method="GET"
              action="{{ route('announcement-management.index') }}"
              class="announcement-filter-bar">

            <div class="announcement-search">
                <span class="announcement-search-icon">⌕</span>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search announcements..."
                    autocomplete="off"
                    aria-label="Search announcements">

                @if(request('search'))
                    <a
                        href="{{ route('announcement-management.index', request()->except('search', 'page')) }}"
                        class="announcement-search-clear"
                        title="Clear search"
                        aria-label="Clear search">×</a>
                @endif
            </div>

            <div class="announcement-filter-select">
                <select name="status" aria-label="Filter by status">
                    <option value="">All Statuses</option>
                    @foreach(['draft' => 'Draft', 'scheduled' => 'Scheduled', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="announcement-filter-select">
                <select name="pin" aria-label="Filter by pin status">
                    <option value="">All Pin Statuses</option>
                    <option value="pinned" @selected(request('pin') === 'pinned')>Pinned</option>
                    <option value="unpinned" @selected(request('pin') === 'unpinned')>Not Pinned</option>
                </select>
            </div>

            <button type="submit" class="btn announcement-filter-button">
                Filter
            </button>

            @if(request()->hasAny(['search', 'status', 'pin']))
                <a href="{{ route('announcement-management.index') }}"
                   class="btn announcement-reset-button">
                    Reset
                </a>
            @endif
        </form>

        @if(request('search') || request('status') || request('pin'))
            <div class="announcement-filter-summary">
                <span>Showing filtered results</span>

                @if(request('search'))
                    <span class="announcement-filter-chip">
                        Search: "{{ request('search') }}"
                    </span>
                @endif

                @if(request('status'))
                    <span class="announcement-filter-chip">
                        Status: {{ ucfirst(request('status')) }}
                    </span>
                @endif

                @if(request('pin'))
                    <span class="announcement-filter-chip">
                        Pin: {{ request('pin') === 'pinned' ? 'Pinned' : 'Not Pinned' }}
                    </span>
                @endif
            </div>
        @endif

        <div class="table-wrap">
            <table class="announcement-table">
                <thead>
                    <tr>
                        <th>Announcement</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Publication</th>
                        <th>Created By</th>
                        <th>Media</th>
                        <th>Pin</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($announcements as $announcement)
                        <tr>
                            <td>
                                <div class="announcement-title-cell">
                                    <div class="announcement-thumbnail">
                                        @if($announcement->thumbnail_path)
                                        <img
                                            src="{{ '/storage/' . ltrim($announcement->thumbnail_path, '/') }}"
                                            alt="{{ $announcement->title }}"
                                            loading="lazy">
                                        @else
                                            <span>▤</span>
                                        @endif
                                    </div>

                                    <div class="announcement-title-info">
                                        <a href="{{ route('announcement-management.show', $announcement) }}"
                                           class="announcement-title-link">
                                            <strong>{{ $announcement->title }}</strong>
                                        </a>

                                        @if($announcement->summary)
                                            <div class="muted announcement-summary">
                                                {{ $announcement->summary }}
                                            </div>
                                        @endif

                                        <div class="muted">
                                            Created {{ $announcement->created_at?->format('d M Y') ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if($announcement->category)
                                    <span class="announcement-category">
                                        {{ $announcement->category }}
                                    </span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>

                            <td>
                                <span class="announcement-status-badge status-{{ $announcement->status }}">
                                    <span class="announcement-status-dot"></span>
                                    {{ ucfirst($announcement->status) }}
                                </span>
                            </td>

                            <td>
                                @if($announcement->status === 'scheduled' && $announcement->scheduled_at)
                                    <strong>{{ $announcement->scheduled_at->format('d M Y') }}</strong>
                                    <div class="muted">{{ $announcement->scheduled_at->format('H:i') }}</div>
                                @elseif($announcement->published_at)
                                    <strong>{{ $announcement->published_at->format('d M Y') }}</strong>
                                    <div class="muted">{{ $announcement->published_at->format('H:i') }}</div>
                                @else
                                    <span class="muted">Not published</span>
                                @endif

                                @if($announcement->expires_at)
                                    <div class="muted">
                                        Expires {{ $announcement->expires_at->format('d M Y') }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                @if($announcement->creator)
                                    <strong>{{ $announcement->creator->name }}</strong>
                                    <div class="muted">{{ $announcement->creator->email }}</div>
                                @else
                                    <span class="muted">Unknown</span>
                                @endif
                            </td>

                            <td>
                                <div class="announcement-media-count">
                                    <span title="Attachments">📎 {{ $announcement->attachments_count }}</span>
                                    <span title="Embedded media">▧ {{ $announcement->media_count }}</span>
                                </div>
                            </td>

                            <td>
                                @if($announcement->is_pinned)
                                    <span class="announcement-pin-badge" title="Pinned announcement">
                                        ★ Pinned
                                    </span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a
                                        href="{{ route('announcement-management.show', $announcement) }}"
                                        class="action-btn view"
                                        title="View Announcement"
                                        aria-label="View Announcement">👁</a>

                                    <a
                                        href="{{ route('announcement-management.edit', $announcement) }}"
                                        class="action-btn edit"
                                        title="Edit Announcement"
                                        aria-label="Edit Announcement">✎</a>

                                    <form
                                        action="{{ route('announcement-management.toggle-pin', $announcement) }}"
                                        method="POST"
                                        class="announcement-pin-form"
                                        data-confirm-pin="{{ $announcement->is_pinned ? 'Unpin' : 'Pin' }}"
                                        data-announcement-title="{{ $announcement->title }}">
                                        @csrf

                                        <button
                                            type="submit"
                                            class="action-btn announcement-pin-action {{ $announcement->is_pinned ? 'is-pinned' : '' }}"
                                            title="{{ $announcement->is_pinned ? 'Unpin Announcement' : 'Pin Announcement' }}"
                                            aria-label="{{ $announcement->is_pinned ? 'Unpin Announcement' : 'Pin Announcement' }}">★</button>
                                    </form>

                                    <form
                                        action="{{ route('announcement-management.destroy', $announcement) }}"
                                        method="POST"
                                        class="announcement-delete-form"
                                        data-announcement-title="{{ $announcement->title }}">
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="action-btn delete"
                                            title="Delete Announcement"
                                            aria-label="Delete Announcement">🗑</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="announcement-empty-state">
                                    <div class="announcement-empty-icon">▤</div>
                                    <h3>No announcements found</h3>
                                    <p>
                                        @if(request()->hasAny(['search', 'status', 'pin']))
                                            Try changing your search or filter criteria.
                                        @else
                                            Create your first announcement to share important company updates.
                                        @endif
                                    </p>

                                    @if(request()->hasAny(['search', 'status', 'pin']))
                                        <a href="{{ route('announcement-management.index') }}" class="btn">
                                            Clear Filters
                                        </a>
                                    @else
                                        <a href="{{ route('announcement-management.create') }}" class="btn primary">
                                            + New Announcement
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($announcements->hasPages())
            <div class="announcement-pagination">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
</div>

<div class="announcement-confirm-modal" id="announcementConfirmModal" aria-hidden="true">
    <div class="announcement-confirm-overlay" data-close-announcement-modal></div>

    <div class="announcement-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="announcementConfirmTitle">
        <div class="announcement-confirm-icon" id="announcementConfirmIcon">!</div>

        <div class="announcement-confirm-content">
            <h3 id="announcementConfirmTitle">Confirm action</h3>
            <p id="announcementConfirmMessage"></p>
            <span id="announcementConfirmNote">Please confirm before continuing.</span>
        </div>

        <div class="announcement-confirm-actions">
            <button type="button" class="btn" id="announcementCancelAction">Cancel</button>
            <button type="button" class="btn" id="announcementConfirmAction">Confirm</button>
        </div>
    </div>
</div>

<style>
    .announcement-stats {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .announcement-stat-card {
        display: flex;
        align-items: center;
        gap: 13px;
        min-width: 0;
        padding: 17px;
        background: #fff;
        border: 1px solid #e5e9f0;
        border-radius: 11px;
        color: #17284f;
        text-decoration: none;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .announcement-stat-card:hover {
        border-color: #b8c8df;
        box-shadow: 0 5px 18px rgba(23, 40, 79, .06);
        transform: translateY(-1px);
    }

    .announcement-stat-card.active {
        border-color: #2ba7a0;
        box-shadow: 0 0 0 2px rgba(43, 167, 160, .09);
    }

    .announcement-stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        border-radius: 10px;
        font-size: 21px;
        font-weight: 600;
    }

    .total-icon { background: #edf3ff; color: #315caa; }
    .draft-icon { background: #f0f2f5; color: #667085; }
    .scheduled-icon { background: #fff5e5; color: #a66b12; }
    .published-icon { background: #e8f6f0; color: #16805f; }
    .archived-icon { background: #f0edfa; color: #7660a9; }

    .announcement-stat-label {
        display: block;
        margin-bottom: 5px;
        color: #7d8797;
        font-size: 11px;
        line-height: 1.4;
    }

    .announcement-stat-card strong {
        display: block;
        color: #17284f;
        font-size: 24px;
        line-height: 1.1;
    }

    .announcement-filter-bar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 18px;
    }

    .announcement-search {
        position: relative;
        flex: 1 1 260px;
        min-width: 210px;
    }

    .announcement-search input {
        box-sizing: border-box;
        width: 100%;
        height: 40px;
        padding: 0 38px;
        border: 1px solid #d9dee8;
        border-radius: 8px;
        background: #fff;
        color: #17284f;
        font-size: 13px;
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
        font-size: 19px;
        pointer-events: none;
    }

    .announcement-search-clear {
        position: absolute;
        top: 50%;
        right: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        transform: translateY(-50%);
        border-radius: 50%;
        color: #7d8797;
        font-size: 18px;
        text-decoration: none;
    }

    .announcement-search-clear:hover {
        background: #edf1f5;
        color: #17284f;
    }

    .announcement-filter-select select {
        min-width: 155px;
        height: 40px;
        padding: 0 30px 0 12px;
        border: 1px solid #d9dee8;
        border-radius: 8px;
        background: #fff;
        color: #34415c;
        font-size: 13px;
    }

    .announcement-filter-select select:focus {
        outline: none;
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
    }

    .announcement-filter-button,
    .announcement-reset-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 40px;
        white-space: nowrap;
    }

    .announcement-filter-summary {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        margin-bottom: 15px;
        color: #7d8797;
        font-size: 12px;
    }

    .announcement-filter-chip {
        padding: 5px 9px;
        border-radius: 6px;
        background: #f1f5f7;
        color: #34415c;
        font-size: 11px;
    }

    .announcement-table {
        min-width: 1050px;
    }

    .announcement-title-cell {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        min-width: 220px;
        max-width: 340px;
    }

    .announcement-thumbnail {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 58px;
        width: 58px;
        height: 52px;
        overflow: hidden;
        border: 1px solid #e6eaf0;
        border-radius: 8px;
        background: #f1f5f9;
        color: #8090a7;
        font-size: 22px;
    }

    .announcement-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .announcement-title-info {
        min-width: 0;
    }

    .announcement-title-link {
        display: inline-block;
        margin-bottom: 4px;
        color: #17284f;
        line-height: 1.45;
        text-decoration: none;
        overflow-wrap: anywhere;
    }

    .announcement-title-link:hover {
        color: #2ba7a0;
    }

    .announcement-summary {
        display: -webkit-box;
        overflow: hidden;
        margin-bottom: 4px;
        line-height: 1.45;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .announcement-category {
        display: inline-flex;
        padding: 5px 8px;
        border-radius: 6px;
        background: #eef3f8;
        color: #465771;
        font-size: 11px;
        font-weight: 600;
    }

    .announcement-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 9px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .announcement-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .status-draft { background: #f0f2f5; color: #667085; }
    .status-scheduled { background: #fff5e5; color: #a66b12; }
    .status-published { background: #e8f6f0; color: #16805f; }
    .status-archived { background: #f0edfa; color: #7660a9; }

    .announcement-media-count {
        display: flex;
        flex-direction: column;
        gap: 5px;
        color: #526078;
        font-size: 12px;
        white-space: nowrap;
    }

    .announcement-pin-badge {
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

    .announcement-pin-form,
    .announcement-delete-form {
        display: inline-flex;
        margin: 0;
    }

    .announcement-pin-action {
        color: #8792a4;
    }

    .announcement-pin-action.is-pinned {
        background: #fff5e5;
        color: #a66b12;
    }

    .announcement-empty-state {
        padding: 48px 20px;
        text-align: center;
    }

    .announcement-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        margin: 0 auto 16px;
        border-radius: 15px;
        background: #edf3ff;
        color: #315caa;
        font-size: 27px;
    }

    .announcement-empty-state h3 {
        margin: 0 0 8px;
        color: #17284f;
        font-size: 17px;
    }

    .announcement-empty-state p {
        max-width: 430px;
        margin: 0 auto 20px;
        color: #7d8797;
        font-size: 13px;
        line-height: 1.6;
    }

    .announcement-pagination {
        margin-top: 20px;
    }

    .announcement-alert-error {
        margin-bottom: 18px;
        padding: 14px 16px;
        border: 1px solid #f0caca;
        border-radius: 8px;
        background: #fff4f4;
        color: #963d3d;
        font-size: 13px;
    }

    .announcement-alert-error ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .announcement-confirm-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .announcement-confirm-modal.open {
        display: flex;
    }

    .announcement-confirm-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 24, 42, .45);
        backdrop-filter: blur(2px);
    }

    .announcement-confirm-dialog {
        position: relative;
        width: min(420px, calc(100% - 32px));
        padding: 26px;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 20px 60px rgba(23, 40, 79, .2);
        animation: announcementModalIn .18s ease;
    }

    @keyframes announcementModalIn {
        from {
            opacity: 0;
            transform: translateY(8px) scale(.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .announcement-confirm-icon {
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

    .announcement-confirm-icon.pin {
        background: #fff5e5;
        color: #a66b12;
    }

    .announcement-confirm-content h3 {
        margin: 0 0 8px;
        color: #17284f;
        font-size: 18px;
    }

    .announcement-confirm-content p {
        margin: 0 0 7px;
        color: #34415c;
        font-size: 13px;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .announcement-confirm-content span {
        color: #8a94a6;
        font-size: 12px;
    }

    .announcement-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 24px;
    }

    .announcement-confirm-actions .confirm-delete {
        border-color: #c94a4a !important;
        background: #c94a4a !important;
        color: #fff !important;
    }

    .announcement-confirm-actions .confirm-delete:hover {
        border-color: #b83f3f !important;
        background: #b83f3f !important;
    }

    .announcement-confirm-actions .confirm-pin {
        border-color: #a66b12 !important;
        background: #a66b12 !important;
        color: #fff !important;
    }

    @media (max-width: 1250px) {
        .announcement-stats {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 1000px) {
        .announcement-filter-bar {
            align-items: stretch;
        }

        .announcement-search {
            flex-basis: 100%;
        }

        .announcement-filter-select {
            flex: 1 1 150px;
        }

        .announcement-filter-select select {
            width: 100%;
        }
    }

    @media (max-width: 650px) {
        .announcement-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .announcement-stat-card {
            gap: 9px;
            padding: 12px;
        }

        .announcement-stat-icon {
            flex-basis: 34px;
            width: 34px;
            height: 34px;
            font-size: 17px;
        }

        .announcement-stat-label {
            font-size: 10px;
        }

        .announcement-stat-card strong {
            font-size: 20px;
        }

        .announcement-filter-bar {
            flex-direction: column;
        }

        .announcement-search,
        .announcement-filter-select,
        .announcement-filter-button,
        .announcement-reset-button {
            width: 100%;
            box-sizing: border-box;
        }

        .announcement-filter-button,
        .announcement-reset-button {
            justify-content: center;
        }

        .announcement-confirm-dialog {
            padding: 22px;
        }

        .announcement-confirm-actions {
            flex-direction: column-reverse;
        }

        .announcement-confirm-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('announcementConfirmModal');
    const title = document.getElementById('announcementConfirmTitle');
    const message = document.getElementById('announcementConfirmMessage');
    const note = document.getElementById('announcementConfirmNote');
    const icon = document.getElementById('announcementConfirmIcon');
    const confirmButton = document.getElementById('announcementConfirmAction');
    const cancelButton = document.getElementById('announcementCancelAction');
    const overlay = document.querySelector('[data-close-announcement-modal]');

    let activeForm = null;

    function openModal(form, action) {
        activeForm = form;

        const announcementTitle = form.dataset.announcementTitle || 'this announcement';

        if (action === 'delete') {
            title.textContent = 'Delete Announcement?';
            message.textContent = 'Are you sure you want to delete "' + announcementTitle + '"?';
            note.textContent = 'This action cannot be undone. Associated media files may also be deleted.';
            icon.textContent = '!';
            icon.classList.remove('pin');
            confirmButton.textContent = 'Delete Announcement';
            confirmButton.className = 'btn confirm-delete';
        } else {
            const pinAction = form.dataset.confirmPin;

            title.textContent = pinAction + ' Announcement?';
            message.textContent = 'Do you want to ' + pinAction.toLowerCase() + ' "' + announcementTitle + '"?';
            note.textContent = 'The pin setting can be changed again later.';
            icon.textContent = '★';
            icon.classList.add('pin');
            confirmButton.textContent = pinAction + ' Announcement';
            confirmButton.className = 'btn confirm-pin';
        }

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        cancelButton.focus();
    }

    function closeModal() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        activeForm = null;
    }

    document.querySelectorAll('.announcement-delete-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            openModal(form, 'delete');
        });
    });

    document.querySelectorAll('.announcement-pin-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            openModal(form, 'pin');
        });
    });

    confirmButton.addEventListener('click', function () {
        if (activeForm) {
            activeForm.submit();
        }
    });

    cancelButton.addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('open')) {
            closeModal();
        }
    });
});
</script>

@endsection
