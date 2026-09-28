@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="page-head">

    <div>
        <h1>Dashboard</h1>

        <p>
            Overview of CRM Medical Device
        </p>
    </div>

    <div class="actions">

        <button
            class="btn"
            type="button"
            onclick="notify('Export feature is not available yet.')"
        >
            Export
        </button>

        <button
            class="btn primary"
            type="button"
            onclick="notify('New Activity feature is not available yet.')"
        >
            + New Activity
        </button>

    </div>

</div>


{{-- =========================================================
     KPI
========================================================= --}}

<div class="grid kpis">

    {{-- Customers --}}

    <div class="card kpi">

        <div class="label">
            Total Customers
        </div>

        <div class="value">
            {{ $stats['customers'] }}
        </div>

        <div class="trend">
            Customer database
        </div>

    </div>


    {{-- Leads --}}

    <div class="card kpi">

        <div class="label">
            Active Leads
        </div>

        <div class="value">
            {{ $stats['leads'] }}
        </div>

        <div class="trend">
            Current leads
        </div>

    </div>


    {{-- Opportunities --}}

    <div class="card kpi">

        <div class="label">
            Opportunities
        </div>

        <div class="value">
            {{ $stats['opportunities'] }}
        </div>

        <div class="trend">
            Active opportunities
        </div>

    </div>


    {{-- Service Tickets --}}

    <div class="card kpi">

        <div class="label">
            Service Tickets
        </div>

        <div class="value">
            {{ $stats['service_tickets'] }}
        </div>

        <div class="trend">
            Current service tickets
        </div>

    </div>

</div>


{{-- =========================================================
     DASHBOARD CONTENT
========================================================= --}}

<div
    class="grid two"
    style="margin-top: 16px;"
>


    {{-- =====================================================
         SALES OVERVIEW
    ====================================================== --}}

    <div class="card">

        <div class="card-head">

            <h3>
                Sales Overview
            </h3>

        </div>

        <div class="card-body">

            <div class="empty">

                Sales data will appear here.

            </div>

        </div>

    </div>


    {{-- =====================================================
         RECENT ACTIVITIES
    ====================================================== --}}

    <div class="card">

        <div class="card-head">

            <h3>
                Recent Activities
            </h3>

        </div>

        <div class="card-body">

            @if($recentActivities->isEmpty())

                <div class="empty">
                    No recent activities.
                </div>

            @else

                <div class="list">

                    @foreach($recentActivities as $activity)

                        <div class="list-item">

                            <div>

                                <strong>
                                    {{ $activity->title ?? 'Activity' }}
                                </strong>

                                <small>
                                    {{ $activity->description ?? '' }}
                                </small>

                            </div>

                        </div>

                    @endforeach

                </div>

            @endif

        </div>

    </div>

</div>

@endsection