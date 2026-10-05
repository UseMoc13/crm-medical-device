@extends('layouts.app')

@section('title', 'Brand Details')

@section('content')

<div class="page-head">

    <div>

        <h1>Brand Details</h1>

        <p>
            View brand information and products associated with this brand.
        </p>

    </div>

    <div class="actions">

        <a
            href="{{ route('brands.index') }}"
            class="btn"
        >
            ← Back to Brands
        </a>

        <a
            href="{{ route('brands.edit', $brand) }}"
            class="btn primary"
        >
            Edit Brand
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


<div class="card brand-show-card">

    <div class="card-head">

        <div>

            <h3>Brand Information</h3>

            <p>
                Detailed information about this product brand.
            </p>

        </div>

    </div>


    <div class="card-body">

        <div class="brand-detail-layout">


            {{-- Brand Profile --}}

            <div class="brand-profile">

                <div class="brand-profile-icon">
                    B
                </div>

                <div>

                    <h2>
                        {{ $brand->brand_name }}
                    </h2>

                    <span>
                        Product Brand
                    </span>

                </div>

            </div>


            {{-- Brand Details --}}

            <div class="brand-details">


                <div class="detail-item">

                    <span>
                        Brand Name
                    </span>

                    <strong>
                        {{ $brand->brand_name }}
                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Products
                    </span>

                    <strong>
                        {{ $brand->products_count }}
                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Created
                    </span>

                    <strong>
                        {{ optional($brand->created_at)->format('d M Y, H:i') ?? '-' }}
                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Last Updated
                    </span>

                    <strong>
                        {{ optional($brand->updated_at)->format('d M Y, H:i') ?? '-' }}
                    </strong>

                </div>


            </div>


            {{-- Description --}}

            <div class="brand-description-box">

                <h4>
                    Description
                </h4>

                @if($brand->description)

                    <p>
                        {{ $brand->description }}
                    </p>

                @else

                    <p class="muted">
                        No description provided.
                    </p>

                @endif

            </div>


        </div>

    </div>

</div>


<style>

.brand-show-card {
    overflow: visible !important;
}

.brand-detail-layout {
    display: grid;
    gap: 28px;
}

.brand-profile {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-bottom: 22px;
    border-bottom: 1px solid #edf0f5;
}

.brand-profile-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 58px;
    height: 58px;
    flex-shrink: 0;
    border-radius: 14px;
    background: #eaf7f3;
    color: #167d70;
    font-size: 20px;
    font-weight: 700;
}

.brand-profile h2 {
    margin: 0 0 5px;
    color: #17284f;
    font-size: 19px;
    font-weight: 700;
}

.brand-profile span {
    color: #7d8797;
    font-size: 11px;
}

.brand-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.detail-item {
    padding: 15px 16px;
    border: 1px solid #e7ebf1;
    border-radius: 9px;
    background: #fafbfd;
}

.detail-item span {
    display: block;
    margin-bottom: 7px;
    color: #8a94a6;
    font-size: 11px;
}

.detail-item strong {
    color: #34415c;
    font-size: 13px;
    font-weight: 600;
}

.brand-description-box {
    padding-top: 5px;
}

.brand-description-box h4 {
    margin: 0 0 9px;
    color: #17284f;
    font-size: 13px;
    font-weight: 700;
}

.brand-description-box p {
    margin: 0;
    color: #718096;
    font-size: 13px;
    line-height: 1.7;
    white-space: pre-line;
}

.brand-description-box .muted {
    color: #a0a8b6;
}

@media (max-width: 600px) {

    .brand-details {
        grid-template-columns: 1fr;
    }

}

</style>

@endsection