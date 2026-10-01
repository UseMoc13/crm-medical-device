@extends('layouts.app')

@section('title', 'Roles')

@section('content')

<div class="page-head">

    <div>

        <h1>Roles</h1>

        <p>
            Manage user roles and access categories within the CRM system.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('roles.create') }}"
            class="btn primary"
        >
            + New Role
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

            <h3>Role List</h3>

            <p>
                {{ $roles->total() }} roles found
            </p>

        </div>

    </div>


    <div class="card-body">


        {{-- =====================================================
             FILTER BAR
        ====================================================== --}}

        <form
            method="GET"
            action="{{ route('roles.index') }}"
            class="role-filter-bar"
        >


            {{-- Search --}}

            <div class="role-search">

                <span class="role-search-icon">
                    ⌕
                </span>


                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search roles..."
                    autocomplete="off"
                >


                @if(request('search'))

                    <a
                        href="{{ route(
                            'roles.index',
                            request()->except(
                                'search',
                                'page'
                            )
                        ) }}"
                        class="role-search-clear"
                        title="Clear search"
                    >
                        ×
                    </a>

                @endif

            </div>


            {{-- Sort --}}

            <div class="role-filter-select">

                <select name="sort">

                    <option
                        value="created_at"
                        @selected($sort === 'created_at')
                    >
                        Created Date
                    </option>


                    <option
                        value="role_name"
                        @selected($sort === 'role_name')
                    >
                        Role Name
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

            <div class="role-filter-select">

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
                class="btn role-filter-button"
            >
                Filter
            </button>


            {{-- Reset --}}

            @if(request()->hasAny([
                'search',
                'sort',
                'direction'
            ]))

                <a
                    href="{{ route('roles.index') }}"
                    class="btn role-reset-button"
                >
                    Reset
                </a>

            @endif

        </form>


        {{-- =====================================================
             ACTIVE FILTER INFORMATION
        ====================================================== --}}

        @if(request('search'))

            <div class="role-filter-summary">

                <span>
                    Showing filtered results
                </span>


                <span class="filter-chip">

                    Search:
                    "{{ request('search') }}"

                </span>

            </div>

        @endif


        {{-- =====================================================
             ROLE TABLE
        ====================================================== --}}

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>

                        <th>
                            Role
                        </th>

                        <th>
                            Description
                        </th>

                        <th>
                            Users
                        </th>

                        <th>
                            Created
                        </th>

                        <th>
                            Updated
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($roles as $role)

                    <tr>


                        {{-- Role --}}

                        <td>

                            <a
                                href="{{ route(
                                    'roles.show',
                                    $role
                                ) }}"
                                class="role-name-link"
                            >

                                <strong>
                                    {{ $role->role_name }}
                                </strong>

                            </a>

                        </td>


                        {{-- Description --}}

                        <td>

                            @if($role->description)

                                <span class="role-description">

                                    {{ $role->description }}

                                </span>

                            @else

                                <span class="muted">
                                    No description
                                </span>

                            @endif

                        </td>


                        {{-- Users --}}

                        <td>

                            @if($role->users_count > 0)

                                <span class="role-user-badge">

                                    {{ $role->users_count }}

                                    {{ $role->users_count == 1
                                        ? 'User'
                                        : 'Users'
                                    }}

                                </span>

                            @else

                                <span class="role-user-badge empty">

                                    No Users

                                </span>

                            @endif

                        </td>


                        {{-- Created --}}

                        <td>

                            @if($role->created_at)

                                {{ $role->created_at->format('d M Y') }}

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Updated --}}

                        <td>

                            @if($role->updated_at)

                                {{ $role->updated_at->format('d M Y') }}

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
                                        'roles.show',
                                        $role
                                    ) }}"
                                    class="action-btn view"
                                    title="View Role"
                                    aria-label="View Role"
                                >
                                    👁
                                </a>


                                {{-- Edit --}}

                                <a
                                    href="{{ route(
                                        'roles.edit',
                                        $role
                                    ) }}"
                                    class="action-btn edit"
                                    title="Edit Role"
                                    aria-label="Edit Role"
                                >
                                    ✎
                                </a>


                                {{-- Delete --}}

                                <form
                                    action="{{ route(
                                        'roles.destroy',
                                        $role
                                    ) }}"
                                    method="POST"
                                    class="delete-form"
                                    data-role-name="{{ $role->role_name }}"
                                >

                                    @csrf

                                    @method('DELETE')


                                    <button
                                        type="submit"
                                        class="action-btn delete"
                                        title="Delete Role"
                                        aria-label="Delete Role"
                                    >
                                        🗑
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>


                    @empty

                    <tr>

                        <td colspan="6">

                            <div class="empty">

                                No roles found.

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

        @if($roles->hasPages())

            <div class="pagination">

                {{ $roles->links() }}

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     DELETE CONFIRMATION MODAL
========================================================= --}}

<div
    class="delete-modal"
    id="deleteRoleModal"
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
        aria-labelledby="deleteRoleModalTitle"
    >

        <div class="delete-modal-icon">
            !
        </div>


        <div class="delete-modal-content">

            <h3 id="deleteRoleModalTitle">
                Delete Role?
            </h3>


            <p>

                Are you sure you want to delete
                <strong id="deleteRoleName"></strong>?

            </p>


            <span>
                A role that is currently assigned to a user cannot be deleted.
            </span>

        </div>


        <div class="delete-modal-actions">

            <button
                type="button"
                class="btn"
                id="cancelDeleteRole"
            >
                Cancel
            </button>


            <button
                type="button"
                class="btn delete-confirm-button"
                id="confirmDeleteRole"
            >
                Delete Role
            </button>

        </div>

    </div>

</div>


<style>

/* =========================================================
   ROLE FILTER BAR
========================================================= */

.role-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}


/* =========================================================
   SEARCH
========================================================= */

.role-search {
    position: relative;

    flex: 1 1 300px;

    min-width: 240px;
}

.role-search input {
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

.role-search input:focus {
    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.role-search-icon {
    position: absolute;

    left: 13px;
    top: 50%;

    transform: translateY(-50%);

    color: #7d8797;

    font-size: 19px;

    pointer-events: none;
}

.role-search-clear {
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

.role-search-clear:hover {
    background: #edf1f5;

    color: #17284f;
}


/* =========================================================
   FILTER SELECT
========================================================= */

.role-filter-select select {
    min-width: 150px;

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

.role-filter-select select:hover {
    border-color: #b7c0cf;
}

.role-filter-select select:focus {
    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}


/* =========================================================
   FILTER BUTTONS
========================================================= */

.role-filter-button,
.role-reset-button {
    height: 40px;

    white-space: nowrap;
}

.role-reset-button {
    display: inline-flex;

    align-items: center;
}


/* =========================================================
   FILTER SUMMARY
========================================================= */

.role-filter-summary {
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
   ROLE NAME
========================================================= */

.role-name-link {
    color: inherit;

    text-decoration: none;
}

.role-name-link:hover {
    color: #223a70;
}


/* =========================================================
   DESCRIPTION
========================================================= */

.role-description {
    display: block;

    max-width: 340px;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #34415c;
}


/* =========================================================
   USER BADGE
========================================================= */

.role-user-badge {
    display: inline-flex;

    align-items: center;

    padding: 4px 9px;

    border-radius: 6px;

    background: #eaf7f3;

    color: #167d70;

    font-size: 11px;

    font-weight: 600;

    white-space: nowrap;
}

.role-user-badge.empty {
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

@media (max-width: 1100px) {

    .role-search {
        flex: 1 1 100%;
    }

    .role-filter-select {
        flex: 1 1 150px;
    }

    .role-filter-select select {
        width: 100%;
    }

}


@media (max-width: 700px) {

    .role-filter-bar {
        flex-direction: column;

        align-items: stretch;
    }

    .role-search,
    .role-filter-select,
    .role-filter-button,
    .role-reset-button {
        width: 100%;
    }

    .role-filter-button,
    .role-reset-button {
        justify-content: center;
    }

    .role-description {
        max-width: 220px;
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
                'deleteRoleModal'
            );


        const roleName =
            document.getElementById(
                'deleteRoleName'
            );


        const confirmButton =
            document.getElementById(
                'confirmDeleteRole'
            );


        const cancelButton =
            document.getElementById(
                'cancelDeleteRole'
            );


        const closeOverlay =
            document.querySelector(
                '[data-close-delete-modal]'
            );


        let deleteForm = null;


        function openDeleteModal(form) {

            deleteForm = form;


            const name =
                form.dataset.roleName ||
                'this role';


            roleName.textContent =
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