@extends('layouts.app')

@section('title', 'User Details')

@section('content')

<div class="page-head">

    <div>

        <h1>User Details</h1>

        <p>
            View account information, role assignment, and CRM status.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('users.index') }}"
            class="btn"
        >
            ← Back to Users
        </a>


        <a
            href="{{ route('users.edit', $user) }}"
            class="btn primary"
        >
            ✎ Edit User
        </a>

    </div>

</div>


@if(session('success'))

<div class="alert success">

    {{ session('success') }}

</div>

@endif


<div class="user-detail-layout">


    {{-- =====================================================
         USER PROFILE
    ====================================================== --}}

    <div class="card">

        <div class="card-body">

            <div class="user-profile-header">

                <div class="user-profile-avatar">

                    {{ strtoupper(
                        substr(
                            $user->name,
                            0,
                            1
                        )
                    ) }}

                </div>


                <div class="user-profile-info">

                    <h2>
                        {{ $user->name }}
                    </h2>

                    <p>
                        {{ $user->email }}
                    </p>

                    <div class="user-profile-meta">

                        @if($user->role)

                            <span class="user-role-badge">
                                {{ $user->role->role_name }}
                            </span>

                        @endif


                        @if($user->status === 'active')

                            <span class="user-status active">
                                Active
                            </span>

                        @else

                            <span class="user-status inactive">
                                Inactive
                            </span>

                        @endif

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
         ACCOUNT INFORMATION
    ====================================================== --}}

    <div class="card">

        <div class="card-head">

            <div>

                <h3>Account Information</h3>

                <p>
                    Basic information associated with this user account.
                </p>

            </div>

        </div>


        <div class="card-body">

            <div class="detail-grid">


                <div class="detail-item">

                    <span>
                        Name
                    </span>

                    <strong>
                        {{ $user->name }}
                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Email
                    </span>

                    <strong>
                        {{ $user->email }}
                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Phone
                    </span>

                    <strong>

                        @if($user->phone)

                            {{ $user->phone }}

                        @else

                            <span class="muted">
                                No phone number
                            </span>

                        @endif

                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Status
                    </span>

                    <strong>

                        @if($user->status === 'active')

                            <span class="user-status active">
                                Active
                            </span>

                        @else

                            <span class="user-status inactive">
                                Inactive
                            </span>

                        @endif

                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Role
                    </span>

                    <strong>

                        @if($user->role)

                            {{ $user->role->role_name }}

                        @else

                            <span class="muted">
                                No Role
                            </span>

                        @endif

                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Branch
                    </span>

                    <strong>

                        @if($user->branch)

                            {{ $user->branch->branch_name }}

                        @else

                            <span class="muted">
                                No Branch
                            </span>

                        @endif

                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Last Login
                    </span>

                    <strong>

                        @if($user->last_login_at)

                            {{ $user->last_login_at->format('d M Y, H:i') }}

                        @else

                            <span class="muted">
                                Never
                            </span>

                        @endif

                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Created
                    </span>

                    <strong>

                        @if($user->created_at)

                            {{ $user->created_at->format('d M Y, H:i') }}

                        @else

                            <span class="muted">
                                -
                            </span>

                        @endif

                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Updated
                    </span>

                    <strong>

                        @if($user->updated_at)

                            {{ $user->updated_at->format('d M Y, H:i') }}

                        @else

                            <span class="muted">
                                -
                            </span>

                        @endif

                    </strong>

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
         CRM RELATION
    ====================================================== --}}

    <div class="card">

        <div class="card-head">

            <div>

                <h3>CRM Assignment</h3>

                <p>
                    Current relationship of this user with CRM data.
                </p>

            </div>

        </div>


        <div class="card-body">

            <div class="assignment-note">

                <div class="assignment-icon">
                    i
                </div>


                <div>

                    <strong>
                        Role & CRM Access
                    </strong>

                    <p>

                        This user's role determines its category
                        within the CRM system. CRM records such as
                        customers and leads can be assigned to this user.

                    </p>

                </div>

            </div>

        </div>

    </div>


</div>


<style>

/* =========================================================
   DETAIL LAYOUT
========================================================= */

.user-detail-layout {
    display: flex;

    flex-direction: column;

    gap: 18px;
}


/* =========================================================
   PROFILE HEADER
========================================================= */

.user-profile-header {
    display: flex;

    align-items: center;

    gap: 18px;

    padding: 8px 2px;
}

.user-profile-avatar {
    width: 68px;

    height: 68px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 16px;

    background: #eaf7f3;

    color: #167d70;

    font-size: 25px;

    font-weight: 700;
}

.user-profile-info {
    min-width: 0;
}

.user-profile-info h2 {
    margin: 0 0 4px;

    color: #17284f;

    font-size: 22px;
}

.user-profile-info > p {
    margin: 0 0 10px;

    color: #7d8797;

    font-size: 12px;
}

.user-profile-meta {
    display: flex;

    align-items: center;

    gap: 7px;

    flex-wrap: wrap;
}


/* =========================================================
   BADGES
========================================================= */

.user-role-badge {
    display: inline-flex;

    align-items: center;

    padding: 5px 9px;

    border-radius: 6px;

    background: #eef2f8;

    color: #344d78;

    font-size: 11px;

    font-weight: 600;
}

.user-status {
    display: inline-flex;

    align-items: center;

    padding: 5px 9px;

    border-radius: 6px;

    font-size: 11px;

    font-weight: 600;

    white-space: nowrap;
}

.user-status.active {
    background: #eaf7f3;

    color: #167d70;
}

.user-status.inactive {
    background: #f1f3f6;

    color: #7d8797;
}


/* =========================================================
   DETAIL GRID
========================================================= */

.detail-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    column-gap: 40px;

    row-gap: 0;
}

.detail-item {
    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 20px;

    min-height: 58px;

    padding: 15px 0;

    border-bottom: 1px solid #edf0f5;
}

.detail-item:nth-last-child(-n + 2) {
    border-bottom: none;
}

.detail-item > span {
    flex-shrink: 0;

    color: #8a94a6;

    font-size: 11px;
}

.detail-item > strong {
    max-width: 65%;

    color: #34415c;

    font-size: 12px;

    text-align: right;

    overflow-wrap: anywhere;
}


/* =========================================================
   ASSIGNMENT
========================================================= */

.assignment-note {
    display: flex;

    align-items: flex-start;

    gap: 13px;

    padding: 15px;

    border-radius: 9px;

    background: #f7f9fc;
}

.assignment-icon {
    width: 28px;

    height: 28px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: #eaf7f3;

    color: #167d70;

    font-size: 12px;

    font-weight: 700;
}

.assignment-note strong {
    display: block;

    margin-bottom: 4px;

    color: #34415c;

    font-size: 12px;
}

.assignment-note p {
    margin: 0;

    color: #7d8797;

    font-size: 11px;

    line-height: 1.6;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 700px) {

    .detail-grid {
        grid-template-columns: 1fr;
    }

    .detail-item:nth-last-child(-n + 2) {
        border-bottom: 1px solid #edf0f5;
    }

    .detail-item:last-child {
        border-bottom: none;
    }

    .user-profile-header {
        align-items: flex-start;
    }

}

</style>

@endsection