@extends('layouts.app')

@section('title', 'Reports')

@section('content')

@php
    $rp = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $label = fn ($value) => ucwords(str_replace('_', ' ', (string) $value)) ?: 'Unspecified';

    $currentRange = [
        'date_from' => $from->toDateString(),
        'date_to' => $to->toDateString(),
    ];

    $monthMax = max(1, collect($monthly)->max(fn ($m) => max($m['invoiced'], $m['paid'])));

    $statusColors = [
        'Paid' => 'teal', 'Sent' => 'blue', 'Draft' => 'gray', 'Cancelled' => 'red',
    ];
    $colorClass = fn ($key) => ['Paid' => '', 'Sent' => 'blue', 'Draft' => 'gray', 'Cancelled' => 'red'][$key] ?? 'gray';

    $ticketColor = fn ($status) => match (strtolower($status)) {
        'open' => 'blue', 'in_progress' => 'amber', 'on_hold' => 'amber',
        'resolved', 'closed' => '', default => 'gray',
    };
    $priorityColor = fn ($priority) => match (strtolower($priority)) {
        'urgent' => 'red', 'high' => 'amber', 'medium' => 'blue', default => 'gray',
    };
    $resultColor = fn ($result) => match (true) {
        str_contains(strtolower($result), 'fail') => 'red',
        str_contains(strtolower($result), 'follow') => 'amber',
        str_contains(strtolower($result), 'notes') => 'blue',
        default => '',
    };
@endphp

<div class="page-head">
    <div>
        <h1>Reports</h1>
        <p>
            Financial, service, and pipeline reporting for
            {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}.
        </p>
    </div>

    <div class="actions">
        <button type="button" class="btn" onclick="window.print()">Print</button>

        @if($canExportInvoiceCsv)
            {{-- A7103:reports.export.invoices --}}@if(\App\Support\CrmViewAction::allows('reports.export.invoices'))
<a href="{{ route('reports.export.invoices', $currentRange) }}" class="btn primary">
                Export Invoices (CSV)
            </a>
@endif{{-- /A7103 --}}
        @else
            <span class="btn" title="Invoice View permission and full Invoice scope required" aria-disabled="true">Export Invoices (CSV) — Restricted</span>
        @endif
    </div>
</div>

@if($errors->any())
    <div class="alert error">
        <strong>Please check the period:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


{{-- PERIOD --}}
<div class="card no-print" style="margin-bottom:6px">
    <div class="card-body">

        {{-- A7103:reports.index --}}@if(\App\Support\CrmViewAction::allows('reports.index'))
<form method="GET" action="{{ route('reports.index') }}" class="rp-filter">

            <div class="rp-field">
                <label for="date_from">From</label>
                <input type="date" id="date_from" name="date_from" value="{{ $from->toDateString() }}">
            </div>

            <div class="rp-field">
                <label for="date_to">To</label>
                <input type="date" id="date_to" name="date_to" value="{{ $to->toDateString() }}">
            </div>

            <button type="submit" class="btn primary rp-btn">Apply Period</button>
        </form>
@endif{{-- /A7103 --}}

        <div class="rp-presets">
            <span>Quick select:</span>

            @foreach($presets as $name => $range)
                {{-- A7103:reports.index --}}@if(\App\Support\CrmViewAction::allows('reports.index'))
<a
                    href="{{ route('reports.index', $range) }}"
                    class="rp-preset {{ $range === $currentRange ? 'active' : '' }}"
                >
                    {{ $name }}
                </a>
@endif{{-- /A7103 --}}
            @endforeach
        </div>

    </div>
</div>


@if($canViewFinancial)
{{-- FINANCIAL KPI --}}
<div class="rp-section-title">
    Financial Overview
    <small>Invoiced = Sent + Paid · Draft and Cancelled are excluded</small>
</div>

<div class="rp-kpis">
    <div class="rp-kpi">
        <span>Total Invoiced</span>
        <strong>{{ $rp($kpi->invoiced) }}</strong>
        <small>{{ number_format($kpi->total_invoices) }} invoices created in this period</small>
    </div>

    <div class="rp-kpi success">
        <span>Paid</span>
        <strong>{{ $rp($kpi->paid) }}</strong>
        <small>Collection rate {{ $collectionRate }}%</small>
    </div>

    <div class="rp-kpi info">
        <span>Outstanding</span>
        <strong>{{ $rp($kpi->outstanding) }}</strong>
        <small>Sent, awaiting payment</small>
    </div>

    <div class="rp-kpi danger">
        <span>Overdue</span>
        <strong>{{ $rp($kpi->overdue_amount) }}</strong>
        <small>{{ number_format($kpi->overdue_count) }} invoice(s) past due date</small>
    </div>
</div>


@else
<div class="card rp-block"><div class="card-head"><h3>Financial Overview</h3></div>
    <div class="card-body"><div class="rp-empty">Financial Overview restricted for this account.</div></div></div>
@endif

@if($canViewOperations)
{{-- SERVICE KPI --}}
<div class="rp-section-title">Service Overview</div>

<div class="rp-kpis">
    <div class="rp-kpi">
        <span>Service Tickets Opened</span>
        <strong>{{ number_format($tickets['total']) }}</strong>
        <small>{{ number_format($tickets['open']) }} still unresolved</small>
    </div>

    <div class="rp-kpi">
        <span>Avg. Resolution Time</span>
        <strong>
            @if($tickets['avg_hours'] !== null)
                {{ $tickets['avg_hours'] >= 48
                    ? number_format($tickets['avg_hours'] / 24, 1) . ' days'
                    : number_format($tickets['avg_hours'], 1) . ' hrs' }}
            @else
                -
            @endif
        </strong>
        <small>Closed tickets only</small>
    </div>

    <div class="rp-kpi">
        <span>Maintenance Performed</span>
        <strong>{{ number_format($maintenance['records']) }}</strong>
        <small>{{ number_format($maintenance['schedules']) }} schedule(s) planned in this period</small>
    </div>

    <div class="rp-kpi danger">
        <span>Overdue Maintenance</span>
        <strong>{{ number_format($maintenance['overdue_now']) }}</strong>
        <small>Open schedules past their date (as of today)</small>
    </div>
</div>


@else
<div class="card rp-block"><div class="card-head"><h3>Service Overview</h3></div>
    <div class="card-body"><div class="rp-empty">Service Overview restricted for this account.</div></div></div>
@endif

@if($canViewFinancial)
{{-- MONTHLY CHART --}}
<section class="card rp-block" style="margin-top:26px">
    <div class="card-head">
        <div>
            <h3>Revenue by Month</h3>
            <p>Invoiced vs. paid amount per invoice month (last 24 months at most).</p>
        </div>
    </div>

    <div class="card-body">
        @if(collect($monthly)->sum(fn ($m) => $m['invoiced'] + $m['paid']) > 0)

            <div class="rp-legend">
                <span><i class="inv"></i>Invoiced</span>
                <span><i class="paid"></i>Paid</span>
            </div>

            <div class="rp-chart-wrap">
                <div class="rp-chart">
                    @foreach($monthly as $month)
                        <div class="rp-month" title="{{ $month['label'] }} — Invoiced {{ $rp($month['invoiced']) }} · Paid {{ $rp($month['paid']) }}">
                            <div class="rp-bars">
                                <div class="rp-bar inv" style="height: {{ round($month['invoiced'] / $monthMax * 100, 1) }}%"></div>
                                <div class="rp-bar paid" style="height: {{ round($month['paid'] / $monthMax * 100, 1) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="rp-labels">
                    @foreach($monthly as $month)
                        <span>{{ $month['label'] }}</span>
                    @endforeach
                </div>
            </div>

            <div class="rp-peak">Highest month: {{ $rp($monthMax) }} · hover a bar for exact values</div>

        @else
            <div class="rp-empty">No invoices were issued in this period.</div>
        @endif
    </div>
</section>


@else
<div class="card rp-block"><div class="card-head"><h3>Revenue by Month</h3></div>
    <div class="card-body"><div class="rp-empty">Revenue by Month restricted for this account.</div></div></div>
@endif

{{-- STATUS + CUSTOMERS --}}
<div class="rp-grid">

    @if($canViewFinancial)
    <section class="card rp-block">
        <div class="card-head">
            <div>
                <h3>Invoices by Status</h3>
                <p>Count and total amount of invoices created in this period.</p>
            </div>
        </div>

        <div class="card-body">
            @include('reports._bars', [
                'rows' => $statusBreakdown->map(fn ($row) => [
                    'label' => $row->status,
                    'sub' => number_format($row->total) . ' invoice(s)',
                    'value' => (float) $row->amount,
                    'display' => $rp($row->amount),
                    'color' => $colorClass($row->status),
                ]),
                'emptyText' => 'No invoices in this period.',
            ])
        </div>
    </section>
    @else
    <section class="card rp-block"><div class="card-head"><h3>Invoices by Status</h3></div>
        <div class="card-body"><div class="rp-empty">Invoices by Status restricted for this account.</div></div></section>
    @endif

    <section class="card rp-block">
        <div class="card-head">
            <div>
                <h3>Top Customers</h3>
                <p>Highest invoiced amount (Sent + Paid).</p>
            </div>
        </div>

        <div class="card-body">
            @if($canViewReportDetails)
            @include('reports._bars', [
                'rows' => $topCustomers->map(fn ($row) => [
                    'label' => $row->name,
                    'sub' => number_format($row->invoices) . ' invoice(s)',
                    'value' => (float) $row->amount,
                    'display' => $rp($row->amount),
                    'color' => 'blue',
                ]),
                'emptyText' => 'No issued invoices in this period.',
            ])
            @else
                <div class="rp-empty">Invoice details restricted for this account.</div>
            @endif
        </div>
    </section>

</div>


{{-- PRODUCTS + PIPELINE --}}
<div class="rp-grid" style="margin-top:18px">

    <section class="card rp-block">
        <div class="card-head">
            <div>
                <h3>Top Products</h3>
                <p>Best-selling products by invoiced revenue.</p>
            </div>
        </div>

        <div class="card-body">
            @if($canViewReportDetails)
            @include('reports._bars', [
                'rows' => $topProducts->map(fn ($row) => [
                    'label' => $row->name,
                    'sub' => trim(($row->product_code ? $row->product_code . ' · ' : '') . number_format($row->quantity) . ' unit(s) sold'),
                    'value' => (float) $row->amount,
                    'display' => $rp($row->amount),
                ]),
                'emptyText' => 'No product sales in this period.',
            ])
            @else
                <div class="rp-empty">Invoice details restricted for this account.</div>
            @endif
        </div>
    </section>

    @if($canViewPipeline)
    <section class="card rp-block">
        <div class="card-head">
            <div>
                <h3>Sales Pipeline</h3>
                <p>Estimated value of opportunities by stage (current snapshot).</p>
            </div>
        </div>

        <div class="card-body">
            @include('reports._bars', [
                'rows' => $pipeline->map(fn ($row) => [
                    'label' => $label($row->stage),
                    'sub' => number_format($row->total) . ' opportunity(ies)',
                    'value' => (float) $row->value,
                    'display' => $rp($row->value),
                    'color' => 'amber',
                ]),
                'emptyText' => 'No opportunities recorded.',
            ])
        </div>
    </section>
    @else
    <section class="card rp-block"><div class="card-head"><h3>Sales Pipeline</h3></div>
        <div class="card-body"><div class="rp-empty">Sales Pipeline restricted for this account.</div></div></section>
    @endif

</div>


@if($canViewOperations)
{{-- SERVICE DETAIL --}}
<div class="rp-grid" style="margin-top:18px">

    <section class="card rp-block">
        <div class="card-head">
            <div>
                <h3>Service Tickets</h3>
                <p>Tickets opened in this period.</p>
            </div>
        </div>

        <div class="card-body">
            <div class="rp-sub-title">By Status</div>
            @include('reports._bars', [
                'rows' => $tickets['by_status']->map(fn ($row) => [
                    'label' => $label($row->status),
                    'value' => (float) $row->total,
                    'display' => number_format($row->total),
                    'color' => $ticketColor($row->status),
                ]),
                'emptyText' => 'No tickets in this period.',
            ])

            <div class="rp-sub-title">By Priority</div>
            @include('reports._bars', [
                'rows' => $tickets['by_priority']->map(fn ($row) => [
                    'label' => $label($row->priority),
                    'value' => (float) $row->total,
                    'display' => number_format($row->total),
                    'color' => $priorityColor($row->priority),
                ]),
                'emptyText' => 'No tickets in this period.',
            ])
        </div>
    </section>

    <section class="card rp-block">
        <div class="card-head">
            <div>
                <h3>Maintenance</h3>
                <p>Schedules and completed activities in this period.</p>
            </div>
        </div>

        <div class="card-body">
            <div class="rp-sub-title">Schedules by Type</div>
            @include('reports._bars', [
                'rows' => $maintenance['by_type']->map(fn ($row) => [
                    'label' => $label($row->maintenance_type),
                    'value' => (float) $row->total,
                    'display' => number_format($row->total),
                    'color' => 'blue',
                ]),
                'emptyText' => 'No maintenance scheduled in this period.',
            ])

            <div class="rp-sub-title">Record Results</div>
            @include('reports._bars', [
                'rows' => $maintenance['by_result']->map(fn ($row) => [
                    'label' => $label($row->result),
                    'value' => (float) $row->total,
                    'display' => number_format($row->total),
                    'color' => $resultColor((string) $row->result),
                ]),
                'emptyText' => 'No maintenance records in this period.',
            ])
        </div>
    </section>

</div>


@else
<div class="card rp-block"><div class="card-head"><h3>Service & Maintenance</h3></div>
    <div class="card-body"><div class="rp-empty">Service & Maintenance restricted for this account.</div></div></div>
@endif

@if($canViewWarranty)
{{-- WARRANTY --}}
<section class="card rp-block" style="margin-top:18px">
    <div class="card-head">
        <div>
            <h3>Warranty Status</h3>
            <p>Current snapshot of all registered warranties.</p>
        </div>
    </div>

    <div class="card-body">

        <div class="rp-minis">
            <div class="rp-mini">
                <span>Total Warranties</span>
                <strong>{{ number_format($warranty['by_status']->sum('total')) }}</strong>
            </div>

            <div class="rp-mini">
                <span>Expiring in 30 Days</span>
                <strong>{{ number_format($warranty['expiring_soon']) }}</strong>
            </div>

            <div class="rp-mini">
                <span>Active</span>
                <strong>
                    {{ number_format($warranty['by_status']->first(fn ($row) => strtolower(trim($row->status)) === 'active')->total ?? 0) }}
                </strong>
            </div>
        </div>

        @include('reports._bars', [
            'rows' => $warranty['by_status']->map(fn ($row) => [
                'label' => $label($row->status),
                'value' => (float) $row->total,
                'display' => number_format($row->total),
                'color' => match (strtolower(trim($row->status))) {
                    'active' => '', 'expired' => 'red', 'claimed' => 'amber', default => 'gray',
                },
            ]),
            'emptyText' => 'No warranties recorded.',
        ])

    </div>
</section>

@else
<div class="card rp-block"><div class="card-head"><h3>Warranty Status</h3></div>
    <div class="card-body"><div class="rp-empty">Warranty Status restricted for this account.</div></div></div>
@endif

@include('reports._styles')

@endsection