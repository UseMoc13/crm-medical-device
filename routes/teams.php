<?php

use App\Http\Controllers\CrmTeamController;
use App\Http\Middleware\EnsureCrmAuthenticated;
use App\Http\Middleware\EnsureCrmOrganizationAdmin;
use Illuminate\Support\Facades\Route;

// Team administration is Super Admin-only in A7.4.6.
// Membership is metadata; it does not grant permissions to CRM records.
Route::middleware([EnsureCrmAuthenticated::class, EnsureCrmOrganizationAdmin::class])
    ->group(function (): void {
        Route::resource('teams', CrmTeamController::class)
            ->parameters(['teams' => 'team']);
    });
