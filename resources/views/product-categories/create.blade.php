@extends('layouts.app')

@section('title', 'Create Product Category')

@section('content')

<div class="page-head">

    <div>

        <h1>
            Create Product Category
        </h1>

        <p>
            Create a category for classifying medical device products.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('product-categories.index') }}"
            class="btn"
        >
            ← Back to Categories
        </a>

    </div>

</div>


@if($errors->any())

    <div class="alert error">

        <strong>
            Please check the following errors:
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


<div class="card category-form-card">

    <div class="card-head">

        <div>

            <h3>
                Category Information
            </h3>

            <p>
                Enter the category name and description.
            </p>

        </div>

    </div>


    <div class="card-body">


        <form
            method="POST"
            action="{{ route('product-categories.store') }}"
        >

            @csrf


            <div class="category-form-layout">


                {{-- =================================================
                     MAIN FORM
                ================================================== --}}

                <div class="category-form-main">


                    <div class="section-divider first-section">

                        <div>

                            <h4>
                                Category Identity
                            </h4>

                            <p>
                                Basic information used to identify this category.
                            </p>

                        </div>

                    </div>


                    {{-- Category Name --}}

                    <div class="form-group">

                        <label for="category_name">

                            Category Name

                            <span class="required">
                                *
                            </span>

                        </label>

                        <input
                            type="text"
                            id="category_name"
                            name="category_name"
                            value="{{ old('category_name') }}"
                            placeholder="e.g. Patient Monitoring"
                            maxlength="100"
                            autocomplete="off"
                            required
                        >

                        <span class="form-hint">
                            Enter a unique name for this product category.
                        </span>

                        @error('category_name')

                            <span class="form-error">
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
                            placeholder="Describe the type of products included in this category..."
                        >{{ old('description') }}</textarea>

                        <span class="form-hint">
                            Optional description of the category.
                        </span>

                        @error('description')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Actions --}}

                    <div class="form-actions">

                        <a
                            href="{{ route('product-categories.index') }}"
                            class="btn"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="btn primary"
                        >
                            Create Category
                        </button>

                    </div>

                </div>


                {{-- =================================================
                     PREVIEW
                ================================================== --}}

                <aside class="category-preview-panel">

                    <div class="preview-label">
                        CATEGORY PREVIEW
                    </div>


                    <div class="preview-category-head">

                        <div class="preview-category-icon">
                            C
                        </div>


                        <div class="preview-category-main">

                            <h3 id="previewCategoryName">
                                New Category
                            </h3>

                            <span>
                                Product Category
                            </span>

                        </div>

                    </div>


                    <div class="preview-divider"></div>


                    <div class="preview-detail">

                        <span>
                            Name
                        </span>

                        <strong id="previewCategoryNameDetail">
                            New Category
                        </strong>

                    </div>


                    <div class="preview-detail">

                        <span>
                            Products
                        </span>

                        <strong>
                            0 products
                        </strong>

                    </div>


                    <div class="preview-divider"></div>


                    <div class="preview-description">

                        <div class="preview-description-title">
                            Description
                        </div>

                        <p id="previewDescription">
                            No description provided.
                        </p>

                    </div>


                    <div class="preview-note">

                        <strong>
                            Category Information
                        </strong>

                        <p>
                            Categories help organize products and make product filtering and management easier.
                        </p>

                    </div>

                </aside>

            </div>

        </form>

    </div>

</div>


<style>

.category-form-card,
.category-form-card .card-body,
.category-form-layout,
.category-form-main {

    overflow: visible !important;

}


.category-form-layout {

    display: grid;

    grid-template-columns:
        minmax(0, 1.25fr)
        minmax(320px, .75fr);

    gap: 32px;

    align-items: start;

}


.category-form-main {

    min-width: 0;

}


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


.form-group {

    margin-bottom: 20px;

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

}


.form-group input {

    height: 42px;

    padding: 0 12px;

}


.form-group textarea {

    min-height: 150px;

    padding: 11px 12px;

    resize: vertical;

    line-height: 1.6;

}


.form-group input:focus,
.form-group textarea:focus {

    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px
        rgba(43, 167, 160, .08);

}


.form-group input::placeholder,
.form-group textarea::placeholder {

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


.form-actions {

    display: flex;

    justify-content: flex-end;

    align-items: center;

    gap: 8px;

    padding-top: 16px;

    margin-top: 8px;

    border-top: 1px solid #edf0f5;

}


/* =====================================================
   PREVIEW
===================================================== */

.category-preview-panel {

    position: sticky;

    top: 92px;

    align-self: start;

    height: fit-content;

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


.preview-category-head {

    display: flex;

    align-items: center;

    gap: 13px;

}


.preview-category-icon {

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


.preview-category-main {

    min-width: 0;

}


.preview-category-main h3 {

    margin: 0 0 4px;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #17284f;

    font-size: 17px;

    font-weight: 700;

}


.preview-category-main span {

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

    padding: 9px 0;

}


.preview-detail > span {

    color: #8a94a6;

    font-size: 11px;

}


.preview-detail > strong {

    max-width: 190px;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #34415c;

    font-size: 12px;

    font-weight: 600;

    text-align: right;

}


.preview-description {

    margin-bottom: 20px;

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

    line-height: 1.6;

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

    .category-form-layout {

        grid-template-columns: 1fr;

    }


    .category-preview-panel {

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

        const name =
            document.getElementById(
                'category_name'
            );

        const description =
            document.getElementById(
                'description'
            );

        const previewName =
            document.getElementById(
                'previewCategoryName'
            );

        const previewNameDetail =
            document.getElementById(
                'previewCategoryNameDetail'
            );

        const previewDescription =
            document.getElementById(
                'previewDescription'
            );


        function updatePreview() {

            const nameValue =
                name.value.trim();


            const descriptionValue =
                description.value.trim();


            previewName.textContent =
                nameValue ||
                'New Category';


            previewNameDetail.textContent =
                nameValue ||
                'New Category';


            previewDescription.textContent =
                descriptionValue ||
                'No description provided.';

        }


        name.addEventListener(
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