@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')

<div class="page-head">

    <div>

        <h1>Edit Role</h1>

        <p>
            Update the basic information and purpose of this role.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('roles.show', $role) }}"
            class="btn"
        >
            ← Back to Role
        </a>

    </div>

</div>


@if($errors->any())

<div class="alert error">

    <strong>
        Please fix the following errors:
    </strong>

    <ul>

        @foreach($errors->all() as $error)

            <li>
                {{ $error }}
            </li>

        @endforeach

    </ul>

</div>

@endif


<div class="card">

    <div class="card-head">

        <div>

            <h3>Role Information</h3>

            <p>
                Update the basic information and purpose of this role.
            </p>

        </div>

    </div>


    <div class="card-body">

        <form
            method="POST"
            action="{{ route('roles.update', $role) }}"
            class="role-form"
        >

            @csrf

            @method('PUT')


            <div class="role-form-layout">


                {{-- =================================================
                     LEFT : FORM
                ================================================== --}}

                <div class="role-form-fields">


                    {{-- Role Name --}}

                    <div class="form-group">

                        <label for="role_name">

                            Role Name

                            <span class="required">
                                *
                            </span>

                        </label>


                        <input
                            type="text"
                            id="role_name"
                            name="role_name"
                            value="{{ old('role_name', $role->role_name) }}"
                            placeholder="e.g. Sales"
                            maxlength="50"
                            required
                            autofocus
                        >


                        <span class="field-hint">
                            Use a clear name that represents the user's responsibility.
                        </span>


                        @error('role_name')

                            <span class="field-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Description --}}

                    <div class="form-group">

                        <label for="description">
                            Description
                        </label>


                        <textarea
                            id="description"
                            name="description"
                            rows="8"
                            placeholder="Describe the responsibility and purpose of this role..."
                        >{{ old('description', $role->description) }}</textarea>


                        <span class="field-hint">
                            Optional. Provide a short explanation of what this role is responsible for.
                        </span>


                        @error('description')

                            <span class="field-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>

                </div>


                {{-- =================================================
                     RIGHT : ROLE PREVIEW
                ================================================== --}}

                <div class="role-preview-panel">

                    <div class="preview-header">

                        <div>

                            <span class="preview-label">
                                ROLE PREVIEW
                            </span>

                            <h4 id="previewRoleName">
                                {{ $role->role_name }}
                            </h4>

                        </div>


                        <div class="preview-icon">
                            ⚙
                        </div>

                    </div>


                    <div class="preview-divider"></div>


                    <div class="preview-section">

                        <span class="preview-section-label">
                            Role Name
                        </span>

                        <strong id="previewRoleNameDetail">
                            {{ $role->role_name }}
                        </strong>

                    </div>


                    <div class="preview-section">

                        <span class="preview-section-label">
                            Description
                        </span>

                        <p id="previewDescription">

                            {{ $role->description ?: 'Role description will appear here.' }}

                        </p>

                    </div>


                    <div class="preview-users">

                        <div class="preview-users-icon">
                            ♙
                        </div>


                        <div>

                            <span>
                                Assigned Users
                            </span>

                            <strong>
                                {{ $role->users()->count() }} Users
                            </strong>

                        </div>

                    </div>


                    <div class="preview-note">

                        <span class="preview-note-icon">
                            i
                        </span>

                        <p>
                            Updating this role will not change the users currently assigned to it.
                        </p>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 FORM ACTIONS
            ================================================== --}}

            <div class="form-actions">

                <a
                    href="{{ route('roles.show', $role) }}"
                    class="btn"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="btn primary"
                >
                    Update Role
                </button>

            </div>

        </form>

    </div>

</div>


<style>

/* =========================================================
   FORM LAYOUT
========================================================= */

.role-form {
    width: 100%;
}


.role-form-layout {

    display: grid;

    grid-template-columns:
        minmax(0, 1.25fr)
        minmax(320px, .75fr);

    gap: 32px;

    align-items: stretch;
}


/* =========================================================
   LEFT FORM
========================================================= */

.role-form-fields {
    min-width: 0;
}


.form-group {
    margin-bottom: 25px;
}


.form-group label {

    display: block;

    margin-bottom: 8px;

    color: #17284f;

    font-size: 13px;

    font-weight: 600;
}


.required {
    color: #c94a4a;
}


/* =========================================================
   INPUT
========================================================= */

.form-group input,
.form-group textarea {

    width: 100%;

    box-sizing: border-box;

    border: 1px solid #d9dee8;

    border-radius: 8px;

    background: #fff;

    color: #17284f;

    font-family: inherit;

    font-size: 13px;

    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}


.form-group input {

    height: 44px;

    padding: 0 13px;
}


.form-group textarea {

    min-height: 180px;

    padding: 12px 13px;

    resize: vertical;

    line-height: 1.6;
}


.form-group input::placeholder,
.form-group textarea::placeholder {

    color: #a1a9b7;
}


.form-group input:hover,
.form-group textarea:hover {

    border-color: #b7c0cf;
}


.form-group input:focus,
.form-group textarea:focus {

    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}


/* =========================================================
   HINT
========================================================= */

.field-hint {

    display: block;

    margin-top: 7px;

    color: #8a94a6;

    font-size: 11px;

    line-height: 1.5;
}


/* =========================================================
   ERROR
========================================================= */

.field-error {

    display: block;

    margin-top: 7px;

    color: #b42318;

    font-size: 12px;
}


/* =========================================================
   ROLE PREVIEW
========================================================= */

.role-preview-panel {

    min-height: 100%;

    box-sizing: border-box;

    padding: 22px;

    border: 1px solid #e2e6ed;

    border-radius: 12px;

    background:
        linear-gradient(
            180deg,
            #fafbfd 0%,
            #f7f9fc 100%
        );

    display: flex;

    flex-direction: column;
}


/* =========================================================
   PREVIEW HEADER
========================================================= */

.preview-header {

    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 15px;
}


.preview-label {

    display: block;

    margin-bottom: 5px;

    color: #8a94a6;

    font-size: 10px;

    font-weight: 700;

    letter-spacing: .08em;
}


.preview-header h4 {

    margin: 0;

    color: #17284f;

    font-size: 20px;

    font-weight: 700;

    word-break: break-word;
}


.preview-icon {

    width: 38px;

    height: 38px;

    display: flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;

    border-radius: 9px;

    background: #eaf7f3;

    color: #15966f;

    font-size: 17px;
}


/* =========================================================
   DIVIDER
========================================================= */

.preview-divider {

    height: 1px;

    margin: 20px 0;

    background: #e3e7ee;
}


/* =========================================================
   PREVIEW SECTION
========================================================= */

.preview-section {

    margin-bottom: 20px;
}


.preview-section-label {

    display: block;

    margin-bottom: 7px;

    color: #8a94a6;

    font-size: 10px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .05em;
}


.preview-section strong {

    display: block;

    color: #17284f;

    font-size: 14px;

    word-break: break-word;
}


.preview-section p {

    min-height: 60px;

    margin: 0;

    color: #596579;

    font-size: 12px;

    line-height: 1.65;

    word-break: break-word;
}


/* =========================================================
   USERS
========================================================= */

.preview-users {

    display: flex;

    align-items: center;

    gap: 11px;

    margin-top: auto;

    padding: 13px;

    border: 1px solid #e1e7ee;

    border-radius: 9px;

    background: #fff;
}


.preview-users-icon {

    width: 34px;

    height: 34px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 8px;

    background: #edf3ff;

    color: #315caa;

    font-size: 15px;
}


.preview-users span {

    display: block;

    margin-bottom: 2px;

    color: #8a94a6;

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: .04em;
}


.preview-users strong {

    display: block;

    color: #17284f;

    font-size: 13px;
}


/* =========================================================
   NOTE
========================================================= */

.preview-note {

    display: flex;

    align-items: flex-start;

    gap: 8px;

    margin-top: 13px;

    padding: 11px;

    border-radius: 8px;

    background: #f0f6f6;
}


.preview-note-icon {

    width: 17px;

    height: 17px;

    display: flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;

    border-radius: 50%;

    background: #dcefed;

    color: #167d70;

    font-size: 10px;

    font-weight: 700;
}


.preview-note p {

    margin: 0;

    color: #607076;

    font-size: 10px;

    line-height: 1.5;
}


/* =========================================================
   FORM ACTIONS
========================================================= */

.form-actions {

    display: flex;

    align-items: center;

    justify-content: flex-end;

    gap: 8px;

    margin-top: 30px;

    padding-top: 20px;

    border-top: 1px solid #edf0f5;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .role-form-layout {

        grid-template-columns: 1fr;

    }


    .role-preview-panel {

        min-height: auto;

    }


    .preview-users {

        margin-top: 10px;

    }

}


@media (max-width: 600px) {

    .form-actions {

        flex-direction: column-reverse;

        align-items: stretch;

    }


    .form-actions .btn {

        width: 100%;

        justify-content: center;

    }

}

</style>


<script>

/* =========================================================
   ROLE LIVE PREVIEW
========================================================= */

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const roleInput =
            document.getElementById(
                'role_name'
            );


        const descriptionInput =
            document.getElementById(
                'description'
            );


        const previewRoleName =
            document.getElementById(
                'previewRoleName'
            );


        const previewRoleNameDetail =
            document.getElementById(
                'previewRoleNameDetail'
            );


        const previewDescription =
            document.getElementById(
                'previewDescription'
            );


        function updatePreview() {

            const roleName =
                roleInput.value.trim();


            const description =
                descriptionInput.value.trim();


            previewRoleName.textContent =
                roleName || 'New Role';


            previewRoleNameDetail.textContent =
                roleName || 'New Role';


            previewDescription.textContent =
                description ||
                'Role description will appear here.';

        }


        roleInput.addEventListener(
            'input',
            updatePreview
        );


        descriptionInput.addEventListener(
            'input',
            updatePreview
        );


        updatePreview();

    }
);

</script>

@endsection