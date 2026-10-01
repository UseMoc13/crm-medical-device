@extends('layouts.app')

@section('title', 'Users')

@section('content')

<div class="page-head">

    <div>

        <h1>Users</h1>

        <p>
            Manage system users, roles, branches, and account status within the CRM system.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('users.create') }}"
            class="btn primary"
        >
            + New User
        </a>

    </div>

</div>


@if(session('success'))

<div class="alert success">

    {{ session('success') }}

</div>

@endif


@if(session('error'))

<div class="alert error">

    {{ session('error') }}

</div>

@endif


<div class="card">

    <div class="card-head">

        <div>

            <h3>User List</h3>

            <p>
                {{ $users->total() }} users found
            </p>

        </div>

    </div>


    <div class="card-body">


        {{-- =====================================================
             FILTER BAR
        ====================================================== --}}

        <form
            method="GET"
            action="{{ route('users.index') }}"
            class="user-filter-bar"
        >


            {{-- Search --}}

            <div class="user-search">

                <span class="user-search-icon">
                    ⌕
                </span>


                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search users..."
                    autocomplete="off"
                >


                @if(request('search'))

                    <a
                        href="{{ route(
                            'users.index',
                            request()->except(
                                'search',
                                'page'
                            )
                        ) }}"
                        class="user-search-clear"
                        title="Clear search"
                    >
                        ×
                    </a>

                @endif

            </div>


            {{-- Role --}}

            <div class="user-filter-select">

                <select name="role_id">

                    <option value="">
                        All Roles
                    </option>


                    @foreach($roles as $role)

                        <option
                            value="{{ $role->role_id }}"
                            @selected(
                                request('role_id') === $role->role_id
                            )
                        >
                            {{ $role->role_name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Status --}}

            <div class="user-filter-select">

                <select name="status">

                    <option value="">
                        All Status
                    </option>

                    <option
                        value="active"
                        @selected(request('status') === 'active')
                    >
                        Active
                    </option>

                    <option
                        value="inactive"
                        @selected(request('status') === 'inactive')
                    >
                        Inactive
                    </option>

                </select>

            </div>


            {{-- Sort --}}

            <div class="user-filter-select">

                <select name="sort">

                    <option
                        value="created_at"
                        @selected($sort === 'created_at')
                    >
                        Created Date
                    </option>


                    <option
                        value="name"
                        @selected($sort === 'name')
                    >
                        Name
                    </option>


                    <option
                        value="email"
                        @selected($sort === 'email')
                    >
                        Email
                    </option>


                    <option
                        value="status"
                        @selected($sort === 'status')
                    >
                        Status
                    </option>


                    <option
                        value="updated_at"
                        @selected($sort === 'updated_at')
                    >
                        Updated Date
                    </option>

                </select>

            </div>


            {{-- Direction --}}

            <div class="user-filter-select">

                <select name="direction">

                    <option
                        value="asc"
                        @selected($direction === 'asc')
                    >
                        ↑ Ascending
                    </option>


                    <option
                        value="desc"
                        @selected($direction === 'desc')
                    >
                        ↓ Descending
                    </option>

                </select>

            </div>


            {{-- Apply --}}

            <button
                type="submit"
                class="btn user-filter-button"
            >
                Filter
            </button>


            {{-- Reset --}}

            @if(request()->hasAny([
                'search',
                'role_id',
                'status',
                'sort',
                'direction'
            ]))

                <a
                    href="{{ route('users.index') }}"
                    class="btn user-reset-button"
                >
                    Reset
                </a>

            @endif

        </form>


        {{-- =====================================================
             ACTIVE FILTER INFORMATION
        ====================================================== --}}

        @if(
            request('search') ||
            request('role_id') ||
            request('status')
        )

            <div class="user-filter-summary">

                <span>
                    Showing filtered results
                </span>


                @if(request('search'))

                    <span class="filter-chip">

                        Search:
                        "{{ request('search') }}"

                    </span>

                @endif


                @if(request('role_id'))

                    @php

                        $selectedRole =
                            $roles->firstWhere(
                                'role_id',
                                request('role_id')
                            );

                    @endphp


                    @if($selectedRole)

                        <span class="filter-chip">

                            Role:
                            "{{ $selectedRole->role_name }}"

                        </span>

                    @endif

                @endif


                @if(request('status'))

                    <span class="filter-chip">

                        Status:
                        "{{ ucfirst(request('status')) }}"

                    </span>

                @endif

            </div>

        @endif


        {{-- =====================================================
             USER TABLE
        ====================================================== --}}

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>

                        <th>
                            User
                        </th>

                        <th>
                            Role
                        </th>

                        <th>
                            Branch
                        </th>

                        <th>
                            Phone
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Created
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($users as $user)

                    <tr>


                        {{-- User --}}

                        <td>

                            <div class="user-table-profile">

                                <div class="user-avatar">

                                    {{ strtoupper(
                                        substr(
                                            $user->name,
                                            0,
                                            1
                                        )
                                    ) }}

                                </div>


                                <div class="user-table-info">

                                    <a
                                        href="{{ route(
                                            'users.show',
                                            $user
                                        ) }}"
                                        class="user-name-link"
                                    >

                                        <strong>
                                            {{ $user->name }}
                                        </strong>

                                    </a>


                                    <span>
                                        {{ $user->email }}
                                    </span>

                                </div>

                            </div>

                        </td>


                        {{-- Role --}}

                        <td>

                            @if($user->role)

                                <span class="user-role-badge">

                                    {{ $user->role->role_name }}

                                </span>

                            @else

                                <span class="muted">
                                    No Role
                                </span>

                            @endif

                        </td>


                        {{-- Branch --}}

                        <td>

                            @if($user->branch)

                                <span class="user-branch">

                                    {{ $user->branch->branch_name }}

                                </span>

                            @else

                                <span class="muted">
                                    No Branch
                                </span>

                            @endif

                        </td>


                        {{-- Phone --}}

                        <td>

                            @if($user->phone)

                                {{ $user->phone }}

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Status --}}

                        <td>

                            @if($user->status === 'active')

                                <span class="user-status active">

                                    Active

                                </span>

                            @else

                                <span class="user-status inactive">

                                    Inactive

                                </span>

                            @endif

                        </td>


                        {{-- Created --}}

                        <td>

                            @if($user->created_at)

                                {{ $user->created_at->format('d M Y') }}

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Actions --}}

                        <td>

                            <div class="table-actions">


                                {{-- View --}}

                                <a
                                    href="{{ route(
                                        'users.show',
                                        $user
                                    ) }}"
                                    class="action-btn view"
                                    title="View User"
                                    aria-label="View User"
                                >
                                    👁
                                </a>


                                {{-- Edit --}}

                                <a
                                    href="{{ route(
                                        'users.edit',
                                        $user
                                    ) }}"
                                    class="action-btn edit"
                                    title="Edit User"
                                    aria-label="Edit User"
                                >
                                    ✎
                                </a>


                                {{-- Delete --}}

                                <form
                                    action="{{ route(
                                        'users.destroy',
                                        $user
                                    ) }}"
                                    method="POST"
                                    class="delete-form"
                                    data-user-name="{{ $user->name }}"
                                >

                                    @csrf

                                    @method('DELETE')


                                    <button
                                        type="submit"
                                        class="action-btn delete"
                                        title="Delete User"
                                        aria-label="Delete User"
                                    >
                                        🗑
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>


                    @empty

                    <tr>

                        <td colspan="7">

                            <div class="empty">

                                No users found.

                            </div>

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =====================================================
             PAGINATION
        ====================================================== --}}

        @if($users->hasPages())

            <div class="pagination">

                {{ $users->links() }}

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     DELETE CONFIRMATION MODAL
========================================================= --}}

<div
    class="delete-modal"
    id="deleteUserModal"
    aria-hidden="true"
>

    <div
        class="delete-modal-overlay"
        data-close-delete-modal
    ></div>


    <div
        class="delete-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="deleteUserModalTitle"
    >

        <div class="delete-modal-icon">
            !
        </div>


        <div class="delete-modal-content">

            <h3 id="deleteUserModalTitle">
                Delete User?
            </h3>


            <p>

                Are you sure you want to delete
                <strong id="deleteUserName"></strong>?

            </p>


            <span>
                A user that is currently assigned to CRM data cannot be deleted.
            </span>

        </div>


        <div class="delete-modal-actions">

            <button
                type="button"
                class="btn"
                id="cancelDeleteUser"
            >
                Cancel
            </button>


            <button
                type="button"
                class="btn delete-confirm-button"
                id="confirmDeleteUser"
            >
                Delete User
            </button>

        </div>

    </div>

</div>


<style>

/* =========================================================
   USER FILTER BAR
========================================================= */

.user-filter-bar {
    display: flex;

    align-items: center;

    gap: 10px;

    flex-wrap: wrap;

    margin-bottom: 18px;
}


/* =========================================================
   SEARCH
========================================================= */

.user-search {
    position: relative;

    flex: 1 1 300px;

    min-width: 240px;
}

.user-search input {
    width: 100%;

    height: 40px;

    box-sizing: border-box;

    padding: 0 38px;

    border: 1px solid #d9dee8;

    border-radius: 8px;

    background: #fff;

    color: #17284f;

    font-size: 13px;

    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}

.user-search input:focus {
    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.user-search-icon {
    position: absolute;

    left: 13px;

    top: 50%;

    transform: translateY(-50%);

    color: #7d8797;

    font-size: 19px;

    pointer-events: none;
}

.user-search-clear {
    position: absolute;

    right: 10px;

    top: 50%;

    transform: translateY(-50%);

    width: 22px;

    height: 22px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    color: #7d8797;

    text-decoration: none;

    font-size: 18px;
}

.user-search-clear:hover {
    background: #edf1f5;

    color: #17284f;
}


/* =========================================================
   FILTER SELECT
========================================================= */

.user-filter-select select {
    min-width: 145px;

    height: 40px;

    padding: 0 34px 0 12px;

    border: 1px solid #d9dee8;

    border-radius: 8px;

    background-color: #fff;

    color: #34415c;

    font-size: 13px;

    cursor: pointer;

    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}

.user-filter-select select:hover {
    border-color: #b7c0cf;
}

.user-filter-select select:focus {
    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}


/* =========================================================
   FILTER BUTTONS
========================================================= */

.user-filter-button,
.user-reset-button {
    height: 40px;

    white-space: nowrap;
}

.user-reset-button {
    display: inline-flex;

    align-items: center;
}


/* =========================================================
   FILTER SUMMARY
========================================================= */

.user-filter-summary {
    display: flex;

    align-items: center;

    gap: 7px;

    flex-wrap: wrap;

    margin-bottom: 15px;

    font-size: 12px;

    color: #7d8797;
}

.filter-chip {
    padding: 5px 9px;

    border-radius: 6px;

    background: #f1f5f7;

    color: #34415c;

    font-size: 11px;
}


/* =========================================================
   USER TABLE PROFILE
========================================================= */

.user-table-profile {
    display: flex;

    align-items: center;

    gap: 11px;

    min-width: 220px;
}

.user-avatar {
    width: 36px;

    height: 36px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 9px;

    background: #eaf7f3;

    color: #167d70;

    font-size: 13px;

    font-weight: 700;
}

.user-table-info {
    min-width: 0;

    display: flex;

    flex-direction: column;

    gap: 3px;
}

.user-table-info > span {
    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #7d8797;

    font-size: 11px;
}

.user-name-link {
    color: inherit;

    text-decoration: none;
}

.user-name-link:hover {
    color: #223a70;
}


/* =========================================================
   ROLE BADGE
========================================================= */

.user-role-badge {
    display: inline-flex;

    align-items: center;

    padding: 4px 9px;

    border-radius: 6px;

    background: #eef2f8;

    color: #344d78;

    font-size: 11px;

    font-weight: 600;

    white-space: nowrap;
}


/* =========================================================
   BRANCH
========================================================= */

.user-branch {
    color: #34415c;

    font-size: 12px;

    white-space: nowrap;
}


/* =========================================================
   STATUS
========================================================= */

.user-status {
    display: inline-flex;

    align-items: center;

    padding: 4px 9px;

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
   DELETE MODAL
========================================================= */

.delete-modal {
    position: fixed;

    inset: 0;

    z-index: 9999;

    display: none;

    align-items: center;

    justify-content: center;
}

.delete-modal.open {
    display: flex;
}

.delete-modal-overlay {
    position: absolute;

    inset: 0;

    background: rgba(15, 24, 42, .45);

    backdrop-filter: blur(2px);
}

.delete-modal-dialog {
    position: relative;

    width: min(420px, calc(100% - 32px));

    background: #fff;

    border-radius: 14px;

    padding: 26px;

    box-shadow:
        0 20px 60px rgba(23, 40, 79, .20);

    animation: deleteModalIn .18s ease;
}

@keyframes deleteModalIn {

    from {
        opacity: 0;

        transform:
            translateY(8px)
            scale(.98);
    }

    to {
        opacity: 1;

        transform:
            translateY(0)
            scale(1);
    }

}

.delete-modal-icon {
    width: 44px;

    height: 44px;

    display: flex;

    align-items: center;

    justify-content: center;

    margin-bottom: 16px;

    border-radius: 50%;

    background: #fff0f0;

    color: #c94a4a;

    font-size: 21px;

    font-weight: 700;
}

.delete-modal-content h3 {
    margin: 0 0 8px;

    color: #17284f;

    font-size: 18px;
}

.delete-modal-content p {
    margin: 0 0 6px;

    color: #34415c;

    font-size: 13px;

    line-height: 1.6;
}

.delete-modal-content p strong {
    color: #17284f;
}

.delete-modal-content > span {
    color: #8a94a6;

    font-size: 12px;
}

.delete-modal-actions {
    display: flex;

    justify-content: flex-end;

    gap: 8px;

    margin-top: 24px;
}

.delete-confirm-button {
    background: #c94a4a !important;

    color: #fff !important;

    border-color: #c94a4a !important;
}

.delete-confirm-button:hover {
    background: #b83f3f !important;

    border-color: #b83f3f !important;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1250px) {

    .user-search {
        flex: 1 1 100%;
    }

    .user-filter-select {
        flex: 1 1 140px;
    }

    .user-filter-select select {
        width: 100%;
    }

}


@media (max-width: 700px) {

    .user-filter-bar {
        flex-direction: column;

        align-items: stretch;
    }

    .user-search,
    .user-filter-select,
    .user-filter-button,
    .user-reset-button {
        width: 100%;
    }

    .user-filter-button,
    .user-reset-button {
        justify-content: center;
    }

    .user-table-profile {
        min-width: 200px;
    }

    .delete-modal-dialog {
        padding: 22px;
    }

    .delete-modal-actions {
        flex-direction: column-reverse;
    }

    .delete-modal-actions .btn {
        width: 100%;

        justify-content: center;
    }

}

</style>


<script>

/* =========================================================
   DELETE CONFIRMATION
========================================================= */

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const modal =
            document.getElementById(
                'deleteUserModal'
            );


        const userName =
            document.getElementById(
                'deleteUserName'
            );


        const confirmButton =
            document.getElementById(
                'confirmDeleteUser'
            );


        const cancelButton =
            document.getElementById(
                'cancelDeleteUser'
            );


        const closeOverlay =
            document.querySelector(
                '[data-close-delete-modal]'
            );


        let deleteForm = null;


        function openDeleteModal(form) {

            deleteForm = form;


            const name =
                form.dataset.userName ||
                'this user';


            userName.textContent =
                name;


            modal.classList.add(
                'open'
            );


            modal.setAttribute(
                'aria-hidden',
                'false'
            );


            document.body.style.overflow =
                'hidden';

        }


        function closeDeleteModal() {

            modal.classList.remove(
                'open'
            );


            modal.setAttribute(
                'aria-hidden',
                'true'
            );


            document.body.style.overflow =
                '';


            deleteForm = null;

        }


        document
            .querySelectorAll(
                '.delete-form'
            )
            .forEach(
                function (form) {

                    form.addEventListener(
                        'submit',
                        function (event) {

                            event.preventDefault();

                            openDeleteModal(
                                form
                            );

                        }
                    );

                }
            );


        confirmButton.addEventListener(
            'click',
            function () {

                if (deleteForm) {

                    deleteForm.submit();

                }

            }
        );


        cancelButton.addEventListener(
            'click',
            closeDeleteModal
        );


        closeOverlay.addEventListener(
            'click',
            closeDeleteModal
        );


        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key === 'Escape' &&
                    modal.classList.contains(
                        'open'
                    )
                ) {

                    closeDeleteModal();

                }

            }
        );

    }
);

</script>

@endsection