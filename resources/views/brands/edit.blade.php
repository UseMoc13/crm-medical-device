@extends('layouts.app')

@section('title', 'Edit Brand')

@section('content')

<div class="page-head">

    <div>

        <h1>Edit Brand</h1>

        <p>
            Update the information and description of this brand.
        </p>

    </div>

    <div class="actions">

        <a
            href="{{ route('brands.show', $brand) }}"
            class="btn"
        >
            ← Back to Brand
        </a>

    </div>

</div>


@if($errors->any())

    <div class="alert error">

        <strong>Please check the following errors:</strong>

        <ul>

            @foreach($errors->all() as $error)

                <li>
                    {{ $error }}
                </li>

            @endforeach

        </ul>

    </div>

@endif


<div class="card brand-create-card">

    <div class="card-head">

        <div>

            <h3>Brand Information</h3>

            <p>
                Update the brand information and description.
            </p>

        </div>

    </div>


    <div class="card-body brand-create-body">

        <form
            method="POST"
            action="{{ route('brands.update', $brand) }}"
            id="brandEditForm"
        >

            @csrf
            @method('PUT')


            <div class="brand-form-layout">


                {{-- =====================================================
                     LEFT : BRAND FORM
                ====================================================== --}}

                <div class="brand-form-main">


                    <div class="section-divider first-section">

                        <div>

                            <h4>Brand Information</h4>

                            <p>
                                Update the basic information used to identify this brand.
                            </p>

                        </div>

                    </div>


                    {{-- Brand Name --}}

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
                            autocomplete="off"
                            required
                        >

                        <span class="form-hint">
                            Name of the product manufacturer or brand.
                        </span>

                        @error('brand_name')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Description --}}

                    <div class="form-group brand-description">

                        <label for="description">
                            Description
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            placeholder="Enter brand description, manufacturer information, or other relevant details..."
                        >{{ old('description', $brand->description) }}</textarea>

                        <span class="form-hint">
                            Optional description or additional information about this brand.
                        </span>

                        @error('description')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Form Actions --}}

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
                        >
                            Update Brand
                        </button>

                    </div>


                </div>


                {{-- =====================================================
                     RIGHT : BRAND PREVIEW
                ====================================================== --}}

                <aside class="brand-preview-panel">

                    <div class="preview-label">
                        BRAND PREVIEW
                    </div>


                    <div class="preview-brand-head">

                        <div class="preview-brand-icon">
                            B
                        </div>

                        <div class="preview-brand-main">

                            <h3 id="previewBrandName">
                                {{ $brand->brand_name }}
                            </h3>

                            <span>
                                Product Brand
                            </span>

                        </div>

                    </div>


                    <div class="preview-divider"></div>


                    <div class="preview-description">

                        <div class="preview-description-title">
                            Description
                        </div>

                        <p id="previewDescription">

                            {{ $brand->description ?: 'No description provided.' }}

                        </p>

                    </div>


                    <div class="preview-divider"></div>


                    <div class="preview-note">

                        <strong>
                            Brand Information
                        </strong>

                        <p>
                            Changes made here will be reflected wherever this brand is used in the CRM system.
                        </p>

                    </div>

                </aside>


            </div>

        </form>

    </div>

</div>


<style>

/* =========================================================
   BRAND FORM
========================================================= */

.brand-create-card,
.brand-create-body,
.brand-form-layout,
.brand-form-main {
    overflow: visible !important;
}

.brand-form-layout {

    display: grid;

    grid-template-columns:
        minmax(0, 1.25fr)
        minmax(320px, .75fr);

    gap: 32px;

    align-items: start;

    width: 100%;

}

.brand-form-main {
    min-width: 0;
}


/* =========================================================
   SECTION
========================================================= */

.section-divider {

    display: flex;

    align-items: center;

    margin: 25px 0 18px;

    padding-top: 20px;

    border-top: 1px solid #edf0f5;

}

.section-divider.first-section {

    margin-top: 0;

    padding-top: 0;

    border-top: 0;

}

.section-divider h4 {

    margin: 0 0 4px;

    color: #17284f;

    font-size: 13px;

    font-weight: 700;

}

.section-divider p {

    margin: 0;

    color: #8a94a6;

    font-size: 11px;

    line-height: 1.5;

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

    height: 42px;

    padding: 0 12px;

}

.form-group textarea {

    min-height: 150px;

    padding: 11px 12px;

    line-height: 1.6;

    resize: vertical;

}

.form-group input:focus,
.form-group textarea:focus {

    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);

}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #a0a8b6;
}


/* =========================================================
   HINT / ERROR
========================================================= */

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

    line-height: 1.5;

}


/* =========================================================
   ACTIONS
========================================================= */

.form-actions {

    display: flex;

    justify-content: flex-end;

    align-items: center;

    gap: 8px;

    padding-top: 16px;

    margin-top: 8px;

    border-top: 1px solid #edf0f5;

}


/* =========================================================
   PREVIEW
========================================================= */

.brand-preview-panel {

    position: -webkit-sticky;

    position: sticky;

    top: 92px;

    align-self: start;

    height: fit-content;

    padding: 24px;

    border: 1px solid #e6eaf0;

    border-radius: 12px;

    background: #fafbfd;

    box-sizing: border-box;

    min-width: 0;

    z-index: 5;

}

.preview-label {

    margin-bottom: 20px;

    color: #8a94a6;

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 1.2px;

}

.preview-brand-head {

    display: flex;

    align-items: center;

    gap: 13px;

}

.preview-brand-icon {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 48px;

    height: 48px;

    flex-shrink: 0;

    border-radius: 12px;

    background: #eaf7f3;

    color: #167d70;

    font-size: 17px;

    font-weight: 700;

}

.preview-brand-main {
    min-width: 0;
}

.preview-brand-main h3 {

    margin: 0 0 4px;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #17284f;

    font-size: 17px;

    font-weight: 700;

}

.preview-brand-main span {

    display: block;

    color: #7d8797;

    font-size: 11px;

}

.preview-divider {

    height: 1px;

    margin: 21px 0;

    background: #e5e9ef;

}

.preview-description-title {

    margin-bottom: 8px;

    color: #34415c;

    font-size: 11px;

    font-weight: 700;

}

.preview-description p {

    margin: 0;

    color: #8a94a6;

    font-size: 10px;

    line-height: 1.7;

    white-space: pre-line;

    word-break: break-word;

}

.preview-note {

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


@media (max-width: 900px) {

    .brand-form-layout {
        grid-template-columns: 1fr;
    }

    .brand-preview-panel {

        position: static;

        order: -1;

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

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const brandName =
            document.getElementById('brand_name');

        const description =
            document.getElementById('description');

        const previewBrandName =
            document.getElementById('previewBrandName');

        const previewDescription =
            document.getElementById('previewDescription');


        function updatePreview() {

            previewBrandName.textContent =
                brandName.value.trim() ||
                'New Brand';


            previewDescription.textContent =
                description.value.trim() ||
                'No description provided.';

        }


        brandName.addEventListener(
            'input',
            updatePreview
        );

        description.addEventListener(
            'input',
            updatePreview
        );


        updatePreview();

    }
);

</script>

@endsection