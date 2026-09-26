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

        <button class="btn">
            Export
        </button>

        <button class="btn primary">
            + New Activity
        </button>

    </div>

</div>


<div class="grid kpis">

    <div class="card kpi">

        <div class="label">
            Total Customers
        </div>

        <div class="value">
            0
        </div>

        <div class="trend">
            Customer database
        </div>

    </div>


    <div class="card kpi">

        <div class="label">
            Active Leads
        </div>

        <div class="value">
            0
        </div>

        <div class="trend">
            Current leads
        </div>

    </div>


    <div class="card kpi">

        <div class="label">
            Opportunities
        </div>

        <div class="value">
            0
        </div>

        <div class="trend">
            Active opportunities
        </div>

    </div>


    <div class="card kpi">

        <div class="label">
            Service Tickets
        </div>

        <div class="value">
            0
        </div>

        <div class="trend">
            Current service tickets
        </div>

    </div>

</div>


<div class="grid two" style="margin-top: 16px;">

    <div class="card">

        <div class="card-head">
            <h3>Sales Overview</h3>
        </div>

        <div class="card-body">

            <div class="empty">
                Sales data will appear here.
            </div>

        </div>

    </div>


    <div class="card">

        <div class="card-head">
            <h3>Recent Activities</h3>
        </div>

        <div class="card-body">

            <div class="empty">
                No recent activities.
            </div>

        </div>

    </div>

</div>

@endsection