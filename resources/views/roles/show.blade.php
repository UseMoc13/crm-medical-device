@extends('layouts.app')

@section('title', 'Role Details')

@section('content')

<div class="page-head">

    <div>
        <h1>Role Details</h1>

        <p>
            View detailed information about this role.
        </p>
    </div>

    <div class="actions">

        <a
            href="{{ route('roles.index') }}"
            class="btn"
        >
            ← Back to Roles
        </a>

        <a
            href="{{ route('roles.edit', $role) }}"
            class="btn primary"
        >
            ✎ Edit Role
        </a>

    </div>

</div>


@if(session('success'))

    <div class="alert success">
        {{ session('success') }}
    </div>

@endif


<div class="role-detail-grid">

    {{-- MAIN INFORMATION --}}
    <div class="card">

        <div class="card-head">

            <div>
                <h2>Role Information</h2>

                <p class="muted">
                    Basic information about this role.
                </p>
            </div>

        </div>


        <div class="card-body">

            <div class="detail-grid">

                <div class="detail-item">

                    <span class="detail-label">
                        Role Name
                    </span>

                    <strong class="detail-value">
                        {{ $role->role_name }}
                    </strong>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Users
                    </span>

                    <strong class="detail-value">
                        {{ $role->users()->count() }} Users
                    </strong>

                </div>


                <div class="detail-item full">

                    <span class="detail-label">
                        Description
                    </span>

                    <div class="detail-description">

                        @if($role->description)

                            {{ $role->description }}

                        @else

                            <span class="muted">
                                No description provided.
                            </span>

                        @endif

                    </div>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Created
                    </span>

                    <span class="detail-value">

                        {{ $role->created_at?->format('d M Y, H:i') ?? '-' }}

                    </span>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Last Updated
                    </span>

                    <span class="detail-value">

                        {{ $role->updated_at?->format('d M Y, H:i') ?? '-' }}

                    </span>

                </div>

            </div>

        </div>

    </div>


    {{-- USERS --}}
    <div class="card">

        <div class="card-head">

            <div>
                <h2>Assigned Users</h2>

                <p class="muted">
                    Users currently assigned to this role.
                </p>
            </div>

            <span class="count-badge">
                {{ $role->users()->count() }}
            </span>

        </div>


        <div class="card-body users-body">

            @php
                $users = $role->users()
                    ->orderBy('name')
                    ->get();
            @endphp


            @if($users->count())

                <div class="user-list">

                    @foreach($users as $user)

                        <div class="user-row">

                            <div class="user-avatar">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>


                            <div class="user-info">

                                <strong>
                                    {{ $user->name }}
                                </strong>

                                <span>
                                    {{ $user->email }}
                                </span>

                            </div>


                            <span
                                class="status-badge
                                {{ strtolower($user->status) === 'active'
                                    ? 'active'
                                    : 'inactive' }}"
                            >
                                {{ ucfirst($user->status) }}
                            </span>

                        </div>

                    @endforeach

                </div>

            @else

                <div class="empty">

                    <div class="empty-icon">
                        ♙
                    </div>

                    <strong>
                        No users assigned
                    </strong>

                    <p>
                        There are currently no users assigned
                        to this role.
                    </p>

                </div>

            @endif

        </div>

    </div>

</div>


<style>

.role-detail-grid {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 20px;
    margin-top: 20px;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.detail-item.full {
    grid-column: 1 / -1;
}

.detail-label {
    font-size: 12px;
    font-weight: 700;
    color: #7b8499;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.detail-value {
    color: #17284f;
    font-size: 15px;
}

.detail-description {
    color: #4d5870;
    line-height: 1.7;
    font-size: 14px;
}

.count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    height: 30px;
    padding: 0 10px;
    border-radius: 999px;
    background: #e9f8f3;
    color: #15966f;
    font-size: 13px;
    font-weight: 700;
}

.users-body {
    padding-top: 8px;
}

.user-list {
    display: flex;
    flex-direction: column;
}

.user-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 0;
    border-bottom: 1px solid #edf0f5;
}

.user-row:last-child {
    border-bottom: none;
}

.user-avatar {
    width: 38px;
    height: 38px;
    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #eaf0fb;
    color: #223a70;

    font-size: 14px;
    font-weight: 700;
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 3px;

    min-width: 0;
    flex: 1;
}

.user-info strong {
    color: #17284f;
    font-size: 14px;
}

.user-info span {
    color: #8a93a6;
    font-size: 12px;

    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.status-badge {
    display: inline-flex;
    align-items: center;

    padding: 5px 9px;
    border-radius: 999px;

    font-size: 11px;
    font-weight: 700;
}

.status-badge.active {
    background: #e8f8f1;
    color: #15966f;
}

.status-badge.inactive {
    background: #f1f3f6;
    color: #7d8492;
}

.empty-icon {
    font-size: 24px;
    margin-bottom: 8px;
}

.empty p {
    margin-top: 5px;
}


@media (max-width: 900px) {

    .role-detail-grid {
        grid-template-columns: 1fr;
    }

}


@media (max-width: 650px) {

    .detail-grid {
        grid-template-columns: 1fr;
    }

    .detail-item.full {
        grid-column: auto;
    }

    .user-row {
        align-items: flex-start;
    }

    .status-badge {
        margin-left: auto;
    }

}

</style>

@endsection