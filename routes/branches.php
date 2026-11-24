<?php

use App\Http\Controllers\BranchController;
use App\Http\Middleware\EnsureCrmAuthenticated;
use App\Http\Middleware\EnsureCrmOrganizationAdmin;
use Illuminate\Support\Facades\Route;

// A7.9.5.1: Branch master data changes can affect users, teams and data scope.
// All endpoints (including index/show) remain Super Admin-only.
Route::middleware([EnsureCrmAuthenticated::class, EnsureCrmOrganizationAdmin::class])
    ->group(function (): void {
        Route::resource('branches', BranchController::class)
            ->parameters(['branches' => 'branch']);
    });
