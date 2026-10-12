@extends('layouts.app')

@section('title', 'Serial Number Details')

@section('content')

@php
    $serialStatus = trim((string) ($serialNumber->status ?? 'Unknown'));
    $normalizedStatus = strtolower(str_replace(['_', '-'], ' ', $serialStatus));

    $statusStyles = [
        'available' => 'available',
        'reserved' => 'reserved',
        'in delivery' => 'delivery',
        'pending installation' => 'pending',
        'installed' => 'installed',
        'under maintenance' => 'maintenance',
        'out of service' => 'danger',
        'retired' => 'retired',
    ];

    $statusClass = $statusStyles[$normalizedStatus] ?? 'unknown';

    $serialCode = trim((string) ($serialNumber->serial_number ?? ''));
    $productName = $serialNumber->product_name ?? 'Unknown Product';
    $productCode = $serialNumber->product_code ?? null;
    $customerName = $serialNumber->customer_name ?? null;

    $purchaseDate = !empty($serialNumber->purchase_date)
        ? \Carbon\Carbon::parse($serialNumber->purchase_date)->format('d M Y')
        : null;

    $createdAt = !empty($serialNumber->created_at)
        ? \Carbon\Carbon::parse($serialNumber->created_at)->format('d M Y, H:i')
        : '-';

    $updatedAt = !empty($serialNumber->updated_at)
        ? \Carbon\Carbon::parse($serialNumber->updated_at)->format('d M Y, H:i')
        : '-';

    $hasCustomer = !empty($serialNumber->customer_id) && filled($customerName);
@endphp

<div class="page-head">
    <div>
        <h1>Serial Number Details</h1>
        <p>View device identification, product information, customer assignment, and serial number status.</p>
    </div>

    <div class="actions">
        <a href="{{ route('serial-numbers.index') }}" class="btn">
            &larr; Back to Serial Numbers
        </a>

        <a href="{{ route('serial-numbers.edit', $serialNumber->serial_number_id) }}" class="btn primary">
            Edit Serial Number
        </a>
    </div>
</div>

@if (session('success'))
    <div class="alert success">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert error">
        {{ session('error') }}
    </div>
@endif

<div class="serial-overview-grid">

    <section class="card serial-information-card">
        <div class="card-head">
            <div>
                <h3>Serial Number Information</h3>
                <p>Device identification and registration details.</p>
            </div>
        </div>

        <div class="card-body">

            <div class="serial-identity">
                <div class="serial-icon" aria-hidden="true">
                    SN
                </div>

                <div class="serial-identity-content">
                    <h2>{{ $serialCode ?: 'Unnamed Serial Number' }}</h2>
                    <span>{{ $productName }}</span>
                </div>

                <div class="serial-status">
                    <span class="status-badge {{ $statusClass }}">
                        <span class="status-dot"></span>
                        {{ $serialStatus }}
                    </span>
                </div>
            </div>

            <div class="serial-info-grid">

                <div class="info-item">
                    <span class="info-label">Serial Number</span>
                    <strong class="serial-value">{{ $serialCode ?: '-' }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Device Status</span>
                    <strong>
                        <span class="status-text {{ $statusClass }}">
                            {{ $serialStatus }}
                        </span>
                    </strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Product Name</span>
                    <strong>{{ $productName }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Product Code</span>
                    <strong>{{ $productCode ?: '-' }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Purchase Date</span>
                    <strong>{{ $purchaseDate ?: 'Not recorded' }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Customer Assignment</span>
                    <strong>{{ $hasCustomer ? $customerName : 'Unassigned' }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Created At</span>
                    <strong>{{ $createdAt }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Last Updated</span>
                    <strong>{{ $updatedAt }}</strong>
                </div>

            </div>
        </div>
    </section>

    <section class="card serial-summary-card">
        <div class="card-head">
            <div>
                <h3>Device Summary</h3>
                <p>Overview of this device's current registration.</p>
            </div>
        </div>

        <div class="card-body">
            <div class="serial-summary-list">

                <div class="serial-summary-item">
                    <div class="summary-item-icon identity-summary-icon" aria-hidden="true">
                        SN
                    </div>

                    <div class="summary-item-content">
                        <span>Device Identification</span>
                        <strong>{{ $serialCode ?: 'Not available' }}</strong>
                        <small>Registered serial number for this device</small>
                    </div>
                </div>

                <div class="serial-summary-item">
                    <div class="summary-item-icon product-summary-icon" aria-hidden="true">
                        P
                    </div>

                    <div class="summary-item-content">
                        <span>Registered Product</span>
                        <strong>{{ $productName }}</strong>
                        <small>{{ $productCode ? 'Product code: ' . $productCode : 'No product code available' }}</small>
                    </div>
                </div>

                <div class="serial-summary-item">
                    <div class="summary-item-icon status-summary-icon" aria-hidden="true">
                        ✓
                    </div>

                    <div class="summary-item-content">
                        <span>Current Device Status</span>
                        <strong>
                            <span class="status-text {{ $statusClass }}">{{ $serialStatus }}</span>
                        </strong>
                        <small>Latest status recorded in the CRM</small>
                    </div>
                </div>

                <div class="serial-summary-item">
                    <div class="summary-item-icon customer-summary-icon" aria-hidden="true">
                        C
                    </div>

                    <div class="summary-item-content">
                        <span>Customer Assignment</span>
                        <strong>{{ $hasCustomer ? $customerName : 'Unassigned' }}</strong>
                        <small>
                            {{ $hasCustomer
                                ? 'A customer is associated with this device'
                                : 'No customer is currently associated with this device' }}
                        </small>
                    </div>
                </div>

            </div>
        </div>
    </section>

</div>

<section class="card serial-product-card">
    <div class="card-head">
        <div>
            <h3>Product Information</h3>
            <p>Product associated with this serial number.</p>
        </div>
    </div>

    <div class="card-body">
        <div class="serial-related-layout">

            <div class="related-icon product-related-icon" aria-hidden="true">
                P
            </div>

            <div class="related-content">
                <span class="related-label">Registered Product</span>
                <h4>{{ $productName }}</h4>

                <div class="related-meta">
                    <div>
                        <span>Product Code</span>
                        <strong>{{ $productCode ?: '-' }}</strong>
                    </div>

                    <div>
                        <span>Serial Number</span>
                        <strong>{{ $serialCode ?: '-' }}</strong>
                    </div>
                </div>
            </div>

            <div class="related-indicator">
                <span class="related-indicator-dot"></span>
                Linked Product
            </div>

        </div>
    </div>
</section>

<section class="card serial-customer-card">
    <div class="card-head">
        <div>
            <h3>Customer Information</h3>
            <p>Customer currently associated with this device.</p>
        </div>
    </div>

    <div class="card-body">

        @if ($hasCustomer)

            <div class="serial-related-layout">

                <div class="related-icon customer-related-icon" aria-hidden="true">
                    C
                </div>

                <div class="related-content">
                    <span class="related-label">Assigned Customer</span>
                    <h4>{{ $customerName }}</h4>

                    <div class="related-meta">
                        <div>
                            <span>Assignment Status</span>
                            <strong>Customer Assigned</strong>
                        </div>

                        <div>
                            <span>Purchase Date</span>
                            <strong>{{ $purchaseDate ?: 'Not recorded' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="related-indicator">
                    <span class="related-indicator-dot"></span>
                    Linked Customer
                </div>

            </div>

        @else

            <div class="serial-empty-state">
                <div class="serial-empty-icon" aria-hidden="true">C</div>
                <h4>No Customer Assigned</h4>
                <p>This serial number is not currently associated with a customer. Edit the serial number to update its customer assignment when applicable.</p>

                <div class="empty-state-actions">
                    <a href="{{ route('serial-numbers.edit', $serialNumber->serial_number_id) }}" class="btn">
                        Edit Assignment
                    </a>
                </div>
            </div>

        @endif

    </div>
</section>

<section class="card serial-registration-card">
    <div class="card-head">
        <div>
            <h3>Registration Information</h3>
            <p>System identifiers and record timestamps.</p>
        </div>
    </div>

    <div class="card-body">
        <div class="serial-registration-grid">

            <div class="registration-item">
                <span class="info-label">Serial Number Record ID</span>
                <strong class="registration-id">{{ $serialNumber->serial_number_id }}</strong>
            </div>

            <div class="registration-item">
                <span class="info-label">Product Record ID</span>
                <strong class="registration-id">{{ $serialNumber->product_id }}</strong>
            </div>

            <div class="registration-item">
                <span class="info-label">Customer Record ID</span>
                <strong class="registration-id">{{ $serialNumber->customer_id ?: '-' }}</strong>
            </div>

            <div class="registration-item">
                <span class="info-label">Record Created</span>
                <strong>{{ $createdAt }}</strong>
            </div>

            <div class="registration-item">
                <span class="info-label">Record Last Updated</span>
                <strong>{{ $updatedAt }}</strong>
            </div>

        </div>
    </div>
</section>

<style>
    .serial-overview-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(280px, .85fr);
        gap: 18px;
        align-items: stretch;
        margin-bottom: 18px;
    }

    .serial-information-card,
    .serial-summary-card {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .serial-information-card .card-body,
    .serial-summary-card .card-body {
        flex: 1;
        min-width: 0;
    }

    .serial-product-card,
    .serial-customer-card,
    .serial-registration-card {
        min-width: 0;
        margin-bottom: 18px;
    }

    .serial-overview-grid .card-head h3,
    .serial-product-card .card-head h3,
    .serial-customer-card .card-head h3,
    .serial-registration-card .card-head h3 {
        margin: 0 0 5px;
        color: #17284f;
        font-size: 15px;
        font-weight: 700;
    }

    .serial-overview-grid .card-head p,
    .serial-product-card .card-head p,
    .serial-customer-card .card-head p,
    .serial-registration-card .card-head p {
        margin: 0;
        color: #8a94a6;
        font-size: 11px;
        line-height: 1.6;
    }

    .serial-identity {
        display: flex;
        align-items: center;
        gap: 13px;
        padding-bottom: 20px;
        margin-bottom: 21px;
        border-bottom: 1px solid #edf0f5;
    }

    .serial-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 50px;
        width: 50px;
        height: 50px;
        border-radius: 11px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 14px;
        font-weight: 700;
    }

    .serial-identity-content {
        flex: 1;
        min-width: 0;
    }

    .serial-identity-content h2 {
        margin: 0 0 5px;
        color: #17284f;
        font-size: 17px;
        font-weight: 700;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .serial-identity-content > span {
        color: #7d8797;
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .serial-status {
        flex-shrink: 0;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 9px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        flex-shrink: 0;
        border-radius: 50%;
        background: currentColor;
    }

    .status-badge.available,
    .status-text.available,
    .status-badge.installed,
    .status-text.installed {
        color: #167d70;
    }

    .status-badge.available,
    .status-badge.installed {
        background: #eaf7f3;
    }

    .status-badge.reserved,
    .status-text.reserved {
        color: #5264a6;
    }

    .status-badge.reserved {
        background: #eef2ff;
    }

    .status-badge.delivery,
    .status-text.delivery,
    .status-badge.pending,
    .status-text.pending {
        color: #a66b19;
    }

    .status-badge.delivery,
    .status-badge.pending {
        background: #fff4e5;
    }

    .status-badge.maintenance,
    .status-text.maintenance {
        color: #8a5aa6;
    }

    .status-badge.maintenance {
        background: #f4ecfa;
    }

    .status-badge.danger,
    .status-text.danger,
    .status-badge.retired,
    .status-text.retired {
        color: #b34b4b;
    }

    .status-badge.danger,
    .status-badge.retired {
        background: #fceeee;
    }

    .status-badge.unknown,
    .status-text.unknown {
        color: #596579;
    }

    .status-badge.unknown {
        background: #f1f3f6;
    }

    .status-text {
        font-size: 12px;
        font-weight: 700;
    }

    .serial-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px 24px;
    }

    .info-item,
    .registration-item {
        min-width: 0;
    }

    .info-label {
        display: block;
        margin-bottom: 6px;
        color: #8a94a6;
        font-size: 10px;
        letter-spacing: .35px;
        text-transform: uppercase;
    }

    .info-item strong,
    .registration-item strong {
        display: block;
        color: #34415c;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .serial-value {
        font-family: Consolas, Monaco, monospace;
        letter-spacing: .2px;
    }

    .serial-summary-list {
        display: flex;
        flex-direction: column;
    }

    .serial-summary-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 0;
    }

    .serial-summary-item + .serial-summary-item {
        border-top: 1px solid #edf0f5;
    }

    .summary-item-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        border-radius: 9px;
        font-size: 11px;
        font-weight: 700;
    }

    .identity-summary-icon,
    .product-summary-icon {
        background: #eaf7f3;
        color: #167d70;
    }

    .status-summary-icon {
        background: #eef2ff;
        color: #5264a6;
    }

    .customer-summary-icon {
        background: #fff4e5;
        color: #c27a28;
    }

    .summary-item-content {
        flex: 1;
        min-width: 0;
    }

    .summary-item-content > span {
        display: block;
        margin-bottom: 4px;
        color: #8a94a6;
        font-size: 10px;
    }

    .summary-item-content > strong {
        display: block;
        margin-bottom: 3px;
        color: #17284f;
        font-size: 12px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .summary-item-content > small {
        display: block;
        color: #9aa3b2;
        font-size: 10px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .serial-related-layout {
        display: flex;
        align-items: center;
        gap: 15px;
        min-width: 0;
        padding: 5px 0;
    }

    .related-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 46px;
        width: 46px;
        height: 46px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
    }

    .product-related-icon {
        background: #eaf7f3;
        color: #167d70;
    }

    .customer-related-icon {
        background: #eef2ff;
        color: #5264a6;
    }

    .related-content {
        flex: 1;
        min-width: 0;
    }

    .related-label {
        display: block;
        margin-bottom: 5px;
        color: #8a94a6;
        font-size: 10px;
        letter-spacing: .35px;
        text-transform: uppercase;
    }

    .related-content h4 {
        margin: 0 0 13px;
        color: #17284f;
        font-size: 14px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .related-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 32px;
    }

    .related-meta > div {
        display: flex;
        flex-direction: column;
        gap: 5px;
        min-width: 120px;
    }

    .related-meta span {
        color: #8a94a6;
        font-size: 10px;
    }

    .related-meta strong {
        color: #34415c;
        font-size: 11px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .related-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
        padding: 6px 9px;
        border-radius: 6px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .related-indicator-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .serial-empty-state {
        padding: 30px 20px;
        text-align: center;
    }

    .serial-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        margin: 0 auto 12px;
        border-radius: 10px;
        background: #f1f3f6;
        color: #8a94a6;
        font-size: 14px;
        font-weight: 700;
    }

    .serial-empty-state h4 {
        margin: 0 0 6px;
        color: #34415c;
        font-size: 13px;
    }

    .serial-empty-state p {
        max-width: 480px;
        margin: 0 auto;
        color: #9aa3b2;
        font-size: 11px;
        line-height: 1.7;
    }

    .empty-state-actions {
        margin-top: 15px;
    }

    .serial-registration-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px 24px;
    }

    .registration-id {
        font-family: Consolas, Monaco, monospace;
        font-size: 11px !important;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    @media (max-width: 950px) {
        .serial-overview-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 650px) {
        .serial-identity {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .serial-identity-content {
            flex-basis: calc(100% - 70px);
        }

        .serial-status {
            margin-left: 0;
        }

        .serial-info-grid,
        .serial-registration-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 17px;
        }

        .serial-related-layout {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .related-content {
            flex-basis: calc(100% - 65px);
        }

        .related-indicator {
            margin-left: 61px;
        }

        .related-meta {
            gap: 12px 20px;
        }
    }
</style>

@endsection