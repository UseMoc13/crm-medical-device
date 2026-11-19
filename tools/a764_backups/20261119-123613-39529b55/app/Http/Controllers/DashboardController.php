<?php

namespace App\Http\Controllers;

use App\Services\CrmDashboardViewData;

/** A7.6.3: all Dashboard KPI/Activity values come from permission-aware read model. */
class DashboardController extends Controller
{
    public function index(CrmDashboardViewData $dashboard)
    {
        return view('dashboard.index', $dashboard->forCurrentUser());
    }
}