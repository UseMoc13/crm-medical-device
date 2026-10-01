@extends('layouts.app')

@section('title', 'Edit User')

@section('content')

<div class="page-head">

    <div>

        <h1>Edit User</h1>

        <p>
            Update user account information, role assignment, branch, and status.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('users.show', $user) }}"
            class="btn"
        >
            ← Back to User
        </a>

    </div>

</div>


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


<div class="card">

    <div class="card-head">

        <div>

            <h3>User Information</h3>

            <p>
                Update the information associated with this user account.
            </p>

        </div>

    </div>


    <div class="card-body">

        <form
            method="POST"
            action="{{ route('users.update', $user) }}"
            id="userEditForm"
        >

            @csrf

            @method('PUT')


            <div class="user-form-layout">


                {{-- =================================================
                     LEFT : FORM
                ================================================== --}}

                <div class="user-form-main">


                    {{-- Name --}}

                    <div class="form-group">

                        <label for="name">
                            Name
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            placeholder="Enter user name"
                            maxlength="100"
                            required
                        >

                        <span class="form-hint">
                            Full name of the user.
                        </span>

                        @error('name')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Email --}}

                    <div class="form-group">

                        <label for="email">
                            Email
                            <span class="required">*</span>
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            placeholder="example@company.com"
                            maxlength="255"
                            required
                        >

                        <span class="form-hint">
                            Email address used for the user account.
                        </span>

                        @error('email')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Role --}}

                    <div class="form-group">

                        <label for="role_id">
                            Role
                            <span class="required">*</span>
                        </label>

                        <select
                            id="role_id"
                            name="role_id"
                            required
                        >

                            <option value="">
                                Select Role
                            </option>

                            @foreach($roles as $role)

                                <option
                                    value="{{ $role->role_id }}"
                                    @selected(
                                        old(
                                            'role_id',
                                            $user->role_id
                                        ) === $role->role_id
                                    )
                                >
                                    {{ $role->role_name }}
                                </option>

                            @endforeach

                        </select>

                        <span class="form-hint">
                            Determines the user's role within the CRM.
                        </span>

                        @error('role_id')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Branch --}}

                    <div class="form-group">

                        <label for="branch_id">
                            Branch
                        </label>

                        <select
                            id="branch_id"
                            name="branch_id"
                        >

                            <option value="">
                                No Branch
                            </option>

                            @foreach($branches as $branch)

                                <option
                                    value="{{ $branch->branch_id }}"
                                    @selected(
                                        old(
                                            'branch_id',
                                            $user->branch_id
                                        ) === $branch->branch_id
                                    )
                                >
                                    {{ $branch->branch_name }}
                                </option>

                            @endforeach

                        </select>

                        <span class="form-hint">
                            Optional branch assignment for this user.
                        </span>

                        @error('branch_id')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Phone --}}

                    <div class="form-group">

                        <label for="phone">
                            Phone
                        </label>

                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $user->phone) }}"
                            placeholder="Enter phone number"
                            maxlength="30"
                        >

                        <span class="form-hint">
                            Optional contact number.
                        </span>

                        @error('phone')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Password --}}

                    <div class="form-group">

                        <label for="password">
                            New Password
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Leave blank to keep current password"
                            minlength="8"
                            maxlength="255"
                        >

                        <span class="form-hint">
                            Leave this field blank if you do not want to change the current password.
                        </span>

                        @error('password')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Status --}}

                    <div class="form-group">

                        <label for="status">
                            Status
                            <span class="required">*</span>
                        </label>

                        <select
                            id="status"
                            name="status"
                            required
                        >

                            <option
                                value="active"
                                @selected(
                                    old(
                                        'status',
                                        $user->status
                                    ) === 'active'
                                )
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                @selected(
                                    old(
                                        'status',
                                        $user->status
                                    ) === 'inactive'
                                )
                            >
                                Inactive
                            </option>

                        </select>

                        <span class="form-hint">
                            Inactive users should not be used for active CRM assignments.
                        </span>

                        @error('status')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Form Actions --}}

                    <div class="form-actions">

                        <a
                            href="{{ route('users.show', $user) }}"
                            class="btn"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="btn primary"
                        >
                            Save Changes
                        </button>

                    </div>

                </div>


                {{-- =================================================
                     RIGHT : PREVIEW
                ================================================== --}}

                <aside class="user-preview-panel">

                    <div class="preview-label">
                        USER PREVIEW
                    </div>


                    <div class="preview-user">

                        <div
                            class="preview-avatar"
                            id="previewAvatar"
                        >
                            {{ strtoupper(
                                substr(
                                    $user->name,
                                    0,
                                    1
                                )
                            ) }}
                        </div>


                        <div class="preview-user-main">

                            <h3 id="previewName">
                                {{ $user->name }}
                            </h3>

                            <span id="previewEmail">
                                {{ $user->email }}
                            </span>

                        </div>

                    </div>


                    <div class="preview-divider"></div>


                    <div class="preview-detail">

                        <span>
                            Role
                        </span>

                        <strong id="previewRole">

                            {{ $user->role?->role_name ?? 'No Role' }}

                        </strong>

                    </div>


                    <div class="preview-detail">

                        <span>
                            Branch
                        </span>

                        <strong id="previewBranch">

                            {{ $user->branch?->branch_name ?? 'No Branch' }}

                        </strong>

                    </div>


                    <div class="preview-detail">

                        <span>
                            Phone
                        </span>

                        <strong id="previewPhone">

                            {{ $user->phone ?: '-' }}

                        </strong>

                    </div>


                    <div class="preview-detail">

                        <span>
                            Status
                        </span>

                        <strong
                            id="previewStatus"
                            class="preview-status {{ $user->status === 'inactive' ? 'inactive' : 'active' }}"
                        >
                            {{ $user->status === 'inactive' ? 'Inactive' : 'Active' }}
                        </strong>

                    </div>


                    <div class="preview-note">

                        <strong>
                            Password
                        </strong>

                        <p>
                            Leave the password field blank to keep the current password unchanged.
                        </p>

                    </div>

                </aside>

            </div>

        </form>

    </div>

</div>


<style>

/* =========================================================
   USER FORM LAYOUT
========================================================= */

.user-form-layout {
    display: grid;

    grid-template-columns:
        minmax(0, 1.25fr)
        minmax(320px, .75fr);

    gap: 32px;

    align-items: start;
}

.user-form-main {
    min-width: 0;
}


/* =========================================================
   FORM
========================================================= */

.form-group {
    margin-bottom: 19px;
}

.form-group label {
    display: block;

    margin-bottom: 7px;

    color: #17284f;

    font-size: 13px;

    font-weight: 600;
}

.required {
    color: #c94a4a;
}

.form-group input,
.form-group select {
    width: 100%;

    height: 42px;

    box-sizing: border-box;

    padding: 0 12px;

    border: 1px solid #d9dee8;

    border-radius: 8px;

    background: #fff;

    color: #17284f;

    font-size: 13px;

    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.form-group input::placeholder {
    color: #a0a8b6;
}

.form-hint {
    display: block;

    margin-top: 6px;

    color: #8a94a6;

    font-size: 11px;

    line-height: 1.5;
}

.form-error {
    display: block;

    margin-top: 6px;

    color: #c94a4a;

    font-size: 11px;
}


/* =========================================================
   FORM ACTIONS
========================================================= */

.form-actions {
    display: flex;

    justify-content: flex-end;

    gap: 8px;

    padding-top: 8px;

    margin-top: 8px;

    border-top: 1px solid #edf0f5;
}


/* =========================================================
   PREVIEW
========================================================= */

.user-preview-panel {
    position: sticky;

    top: 24px;

    padding: 24px;

    border: 1px solid #e6eaf0;

    border-radius: 12px;

    background: #fafbfd;
}

.preview-label {
    margin-bottom: 20px;

    color: #8a94a6;

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 1.2px;
}

.preview-user {
    display: flex;

    align-items: center;

    gap: 13px;
}

.preview-avatar {
    width: 48px;

    height: 48px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 12px;

    background: #eaf7f3;

    color: #167d70;

    font-size: 17px;

    font-weight: 700;
}

.preview-user-main {
    min-width: 0;
}

.preview-user-main h3 {
    margin: 0 0 4px;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #17284f;

    font-size: 17px;
}

.preview-user-main span {
    display: block;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #7d8797;

    font-size: 11px;
}

.preview-divider {
    height: 1px;

    margin: 21px 0;

    background: #e5e9ef;
}

.preview-detail {
    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 20px;

    padding: 10px 0;
}

.preview-detail span {
    color: #8a94a6;

    font-size: 11px;
}

.preview-detail strong {
    max-width: 190px;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #34415c;

    font-size: 12px;

    text-align: right;
}

.preview-status {
    display: inline-flex;

    padding: 4px 8px;

    border-radius: 6px;

    font-size: 10px !important;
}

.preview-status.active {
    background: #eaf7f3;

    color: #167d70 !important;
}

.preview-status.inactive {
    background: #f1f3f6;

    color: #7d8797 !important;
}

.preview-note {
    margin-top: 20px;

    padding: 13px 14px;

    border-radius: 8px;

    background: #f1f5f7;
}

.preview-note strong {
    display: block;

    margin-bottom: 5px;

    color: #34415c;

    font-size: 11px;
}

.preview-note p {
    margin: 0;

    color: #8a94a6;

    font-size: 10px;

    line-height: 1.6;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .user-form-layout {
        grid-template-columns: 1fr;
    }

    .user-preview-panel {
        position: static;

        order: -1;
    }

}


@media (max-width: 600px) {

    .form-actions {
        flex-direction: column-reverse;
    }

    .form-actions .btn {
        width: 100%;

        justify-content: center;
    }

}

</style>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const nameInput =
            document.getElementById('name');

        const emailInput =
            document.getElementById('email');

        const roleSelect =
            document.getElementById('role_id');

        const branchSelect =
            document.getElementById('branch_id');

        const phoneInput =
            document.getElementById('phone');

        const statusSelect =
            document.getElementById('status');


        const previewAvatar =
            document.getElementById('previewAvatar');

        const previewName =
            document.getElementById('previewName');

        const previewEmail =
            document.getElementById('previewEmail');

        const previewRole =
            document.getElementById('previewRole');

        const previewBranch =
            document.getElementById('previewBranch');

        const previewPhone =
            document.getElementById('previewPhone');

        const previewStatus =
            document.getElementById('previewStatus');


        function updatePreview() {

            const name =
                nameInput.value.trim();

            const email =
                emailInput.value.trim();

            const roleText =
                roleSelect.options[
                    roleSelect.selectedIndex
                ]?.text || 'No Role';

            const branchText =
                branchSelect.options[
                    branchSelect.selectedIndex
                ]?.text || 'No Branch';

            const phone =
                phoneInput.value.trim();

            const status =
                statusSelect.value;


            previewName.textContent =
                name || 'User';

            previewEmail.textContent =
                email || 'user@example.com';

            previewRole.textContent =
                roleSelect.value
                    ? roleText
                    : 'No Role';

            previewBranch.textContent =
                branchSelect.value
                    ? branchText
                    : 'No Branch';

            previewPhone.textContent =
                phone || '-';

            previewStatus.textContent =
                status === 'inactive'
                    ? 'Inactive'
                    : 'Active';

            previewStatus.classList.remove(
                'active',
                'inactive'
            );

            previewStatus.classList.add(
                status === 'inactive'
                    ? 'inactive'
                    : 'active'
            );


            previewAvatar.textContent =
                name
                    ? name
                        .charAt(0)
                        .toUpperCase()
                    : 'U';

        }


        [
            nameInput,
            emailInput,
            roleSelect,
            branchSelect,
            phoneInput,
            statusSelect
        ].forEach(
            function (element) {

                element.addEventListener(
                    'input',
                    updatePreview
                );

                element.addEventListener(
                    'change',
                    updatePreview
                );

            }
        );


        updatePreview();

    }
);

</script>

@endsection