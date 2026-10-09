@extends('layouts.app')

@section('title', 'Edit Brand')

@section('content')

<div class="page-head">
    <div>
        <h1>Edit Brand</h1>
        <p>Update the information and description of this brand.</p>
    </div>

    <div class="brand-page-actions">
        <a href="{{ route('brands.show', $brand) }}" class="btn">
            <span aria-hidden="true">&larr;</span>
            Back to Brand
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

@if($errors->any())
    <div class="alert error brand-validation-alert">
        <strong>Please check the following errors:</strong>

        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card brand-edit-card">
    <div class="card-head">
        <div>
            <h3>Brand Information</h3>
            <p>Update the brand information and description.</p>
        </div>
    </div>

    <div class="card-body brand-edit-body">
        <form
            method="POST"
            action="{{ route('brands.update', $brand) }}"
            id="brandEditForm"
        >
            @csrf
            @method('PUT')

            <div class="brand-form-layout">

                {{-- LEFT: EDIT FORM --}}
                <div class="brand-form-main">

                    <div class="section-divider first-section">
                        <div>
                            <h4>Basic Information</h4>
                            <p>
                                Update the basic information used to identify this brand.
                            </p>
                        </div>
                    </div>

                    {{-- BRAND NAME --}}
                    <div class="form-group">
                        <label for="brand_name">
                            Brand Name
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="brand_name"
                            name="brand_name"
                            value="{{ old('brand_name', $brand->brand_name) }}"
                            placeholder="e.g. Philips"
                            maxlength="255"
                            autocomplete="organization"
                            required
                            autofocus
                            aria-describedby="brandNameHint"
                            class="@error('brand_name') is-invalid @enderror"
                        >

                        <span class="form-hint" id="brandNameHint">
                            Enter the product manufacturer or brand name.
                        </span>

                        @error('brand_name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="form-group brand-description-group">
                        <label for="description">
                            Description
                            <span class="optional-label">Optional</span>
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            rows="6"
                            placeholder="Enter brand description, manufacturer information, or other relevant details..."
                            aria-describedby="descriptionHint"
                            class="@error('description') is-invalid @enderror"
                        >{{ old('description', $brand->description) }}</textarea>

                        <span class="form-hint" id="descriptionHint">
                            Add information that helps identify or describe this brand.
                        </span>

                        @error('description')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- FORM ACTIONS --}}
                    <div class="form-actions">
                        <a
                            href="{{ route('brands.show', $brand) }}"
                            class="btn"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="btn primary"
                            id="updateBrandButton"
                        >
                            Update Brand
                            <span aria-hidden="true">&rarr;</span>
                        </button>
                    </div>
                </div>

                {{-- RIGHT: LIVE PREVIEW --}}
                <aside class="brand-preview-panel">

                    <div class="preview-panel-header">
                        <span class="preview-label">BRAND PREVIEW</span>

                        <span class="preview-live-badge">
                            <span class="preview-live-dot"></span>
                            Live Preview
                        </span>
                    </div>

                    <div class="preview-brand-head">
                        <div class="preview-brand-icon" id="previewBrandIcon">
                            {{ strtoupper(mb_substr($brand->brand_name ?: 'B', 0, 1)) }}
                        </div>

                        <div class="preview-brand-main">
                            <h3 id="previewBrandName">
                                {{ old('brand_name', $brand->brand_name) ?: 'Brand Name' }}
                            </h3>

                            <span>Product Brand</span>
                        </div>
                    </div>

                    <div class="preview-divider"></div>

                    <div class="preview-description">
                        <div class="preview-section-heading">
                            <span class="preview-section-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M7 4.5h10A2.5 2.5 0 0 1 19.5 7v10a2.5 2.5 0 0 1-2.5 2.5H7A2.5 2.5 0 0 1 4.5 17V7A2.5 2.5 0 0 1 7 4.5Z"
                                        stroke="currentColor"
                                        stroke-width="1.6"
                                    />
                                    <path
                                        d="M8 9h8M8 12h8M8 15h5"
                                        stroke="currentColor"
                                        stroke-width="1.6"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </span>

                            <span>Description</span>
                        </div>

                        <p id="previewDescription">{{ old('description', $brand->description) ?: 'No description provided.' }}</p>
                    </div>

                    <div class="preview-divider"></div>

                    <div class="preview-note">
                        <div class="preview-note-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="9"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                />
                                <path
                                    d="M12 11v5M12 8h.01"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </div>

                        <div>
                            <strong>Brand Information</strong>
                            <p>
                                Changes will be reflected wherever this brand is referenced in the CRM system after you save.
                            </p>
                        </div>
                    </div>

                    <div class="preview-footer">
                        <span class="preview-footer-label">Current Record</span>
                        <span class="preview-record-id">
                            #{{ $brand->brand_id }}
                        </span>
                    </div>

                </aside>
            </div>
        </form>
    </div>
</div>

<style>
/* =========================================================
   PAGE ACTIONS
========================================================= */

.brand-page-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    flex-wrap: wrap;
}

.brand-validation-alert {
    margin-bottom: 18px;
}

.brand-validation-alert ul {
    margin: 8px 0 0;
    padding-left: 20px;
}

.brand-validation-alert li {
    margin: 4px 0;
    line-height: 1.5;
}

/* =========================================================
   MAIN CARD
========================================================= */

.brand-edit-card {
    min-width: 0;
    overflow: hidden;
}

.brand-edit-body {
    padding: 25px;
}

.brand-form-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(290px, .8fr);
    align-items: start;
    gap: 30px;
    width: 100%;
}

.brand-form-main {
    min-width: 0;
}

/* =========================================================
   SECTION HEADING
========================================================= */

.section-divider {
    display: flex;
    align-items: center;
    margin: 24px 0 20px;
    padding-top: 20px;
    border-top: 1px solid #edf0f5;
}

.section-divider.first-section {
    margin-top: 0;
    padding-top: 0;
    border-top: 0;
}

.section-divider h4 {
    margin: 0 0 5px;
    color: #17284f;
    font-size: 13px;
    font-weight: 700;
}

.section-divider p {
    margin: 0;
    color: #8993a4;
    font-size: 11px;
    line-height: 1.7;
}

/* =========================================================
   FORM ELEMENTS
========================================================= */

.form-group {
    margin-bottom: 23px;
}

.form-group label {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 8px;
    color: #34415c;
    font-size: 12px;
    font-weight: 600;
}

.required {
    color: #dc5555;
    font-size: 13px;
}

.optional-label {
    padding: 3px 6px;
    border-radius: 4px;
    background: #f1f3f7;
    color: #8a94a6;
    font-size: 9px;
    font-weight: 500;
}

.form-group input,
.form-group textarea {
    display: block;
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #dfe5ed;
    border-radius: 8px;
    background: #fff;
    color: #34415c;
    font-family: inherit;
    font-size: 12px;
    transition:
        border-color .18s ease,
        box-shadow .18s ease,
        background .18s ease;
}

.form-group input {
    height: 43px;
    padding: 0 13px;
}

.form-group textarea {
    min-height: 150px;
    padding: 12px 13px;
    line-height: 1.7;
    resize: vertical;
}

.form-group input:hover,
.form-group textarea:hover {
    border-color: #cbd5e2;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .10);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #a3acba;
}

.form-group input.is-invalid,
.form-group textarea.is-invalid {
    border-color: #dc5555;
}

.form-group input.is-invalid:focus,
.form-group textarea.is-invalid:focus {
    box-shadow: 0 0 0 3px rgba(220, 85, 85, .09);
}

.form-hint {
    display: block;
    margin-top: 7px;
    color: #8993a4;
    font-size: 10px;
    line-height: 1.7;
}

.form-error {
    display: block;
    margin-top: 7px;
    color: #c74343;
    font-size: 11px;
    line-height: 1.6;
}

/* =========================================================
   FORM ACTIONS
========================================================= */

.form-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 9px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #edf0f5;
}

.form-actions .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 38px;
    padding: 0 15px;
    font-size: 11px;
    text-decoration: none;
    cursor: pointer;
}

.form-actions button:disabled {
    opacity: .65;
    cursor: wait;
}

/* =========================================================
   BRAND PREVIEW PANEL
========================================================= */

.brand-preview-panel {
    position: sticky;
    top: 90px;
    align-self: start;
    min-width: 0;
    padding: 22px;
    border: 1px solid #e4eaf1;
    border-radius: 12px;
    background: #fbfcfe;
}

.preview-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 23px;
}

.preview-label {
    color: #7d8797;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 1.1px;
}

.preview-live-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 7px;
    border: 1px solid #d9eee6;
    border-radius: 999px;
    background: #eff9f5;
    color: #167d70;
    font-size: 9px;
    font-weight: 600;
    white-space: nowrap;
}

.preview-live-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #15966f;
}

.preview-brand-head {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
}

.preview-brand-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 49px;
    height: 49px;
    flex-shrink: 0;
    border: 1px solid #d6eee5;
    border-radius: 12px;
    background: #eaf7f3;
    color: #167d70;
    font-size: 18px;
    font-weight: 700;
}

.preview-brand-main {
    min-width: 0;
}

.preview-brand-main h3 {
    margin: 0 0 5px;
    color: #17284f;
    font-size: 16px;
    font-weight: 700;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.preview-brand-main span {
    display: block;
    color: #8993a4;
    font-size: 10px;
}

.preview-divider {
    height: 1px;
    margin: 21px 0;
    background: #e8edf3;
}

/* =========================================================
   PREVIEW DESCRIPTION
========================================================= */

.preview-section-heading {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    color: #34415c;
    font-size: 11px;
    font-weight: 700;
}

.preview-section-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border: 1px solid #e2e8f0;
    border-radius: 7px;
    background: #fff;
    color: #718096;
}

.preview-section-icon svg {
    width: 15px;
    height: 15px;
}

.preview-description p {
    margin: 0;
    color: #718096;
    font-size: 11px;
    line-height: 1.8;
    white-space: pre-line;
    overflow-wrap: anywhere;
}

/* =========================================================
   PREVIEW NOTE
========================================================= */

.preview-note {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 13px;
    border: 1px solid #dcebe6;
    border-radius: 9px;
    background: #f0f8f5;
}

.preview-note-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 25px;
    height: 25px;
    flex-shrink: 0;
    border: 1px solid #d5e9e1;
    border-radius: 7px;
    background: #fff;
    color: #167d70;
}

.preview-note-icon svg {
    width: 15px;
    height: 15px;
}

.preview-note strong {
    display: block;
    margin-bottom: 5px;
    color: #245d50;
    font-size: 10px;
    font-weight: 700;
}

.preview-note p {
    margin: 0;
    color: #67867d;
    font-size: 10px;
    line-height: 1.7;
}

/* =========================================================
   PREVIEW FOOTER
========================================================= */

.preview-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #e8edf3;
}

.preview-footer-label {
    color: #8993a4;
    font-size: 10px;
}

.preview-record-id {
    color: #526078;
    font-size: 10px;
    font-weight: 700;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 950px) {
    .brand-form-layout {
        grid-template-columns: minmax(0, 1fr);
        gap: 25px;
    }

    .brand-preview-panel {
        position: static;
        order: -1;
    }
}

@media (max-width: 600px) {
    .brand-edit-body {
        padding: 17px;
    }

    .brand-page-actions {
        width: 100%;
        justify-content: flex-start;
    }

    .brand-preview-panel {
        padding: 17px;
    }

    .preview-panel-header {
        margin-bottom: 18px;
    }

    .form-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .form-actions .btn {
        width: 100%;
        box-sizing: border-box;
    }
}

@media (prefers-reduced-motion: reduce) {
    .form-group input,
    .form-group textarea {
        transition: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const brandNameInput = document.getElementById('brand_name');
    const descriptionInput = document.getElementById('description');

    const previewBrandName = document.getElementById('previewBrandName');
    const previewBrandIcon = document.getElementById('previewBrandIcon');
    const previewDescription = document.getElementById('previewDescription');

    const form = document.getElementById('brandEditForm');
    const submitButton = document.getElementById('updateBrandButton');

    if (
        !brandNameInput ||
        !descriptionInput ||
        !previewBrandName ||
        !previewBrandIcon ||
        !previewDescription
    ) {
        return;
    }

    function updatePreview() {
        const name = brandNameInput.value.trim();
        const description = descriptionInput.value.trim();

        previewBrandName.textContent = name || 'Brand Name';
        previewBrandIcon.textContent = name
            ? Array.from(name)[0].toUpperCase()
            : 'B';

        previewDescription.textContent =
            description || 'No description provided.';
    }

    brandNameInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);

    if (form && submitButton) {
        form.addEventListener('submit', function () {
            if (!form.checkValidity()) {
                return;
            }

            submitButton.disabled = true;
            submitButton.textContent = 'Updating Brand...';
        });
    }

    updatePreview();
});
</script>

@endsection
