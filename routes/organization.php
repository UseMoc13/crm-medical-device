<?php

use App\Http\Controllers\OrganizationController;
use App\Http\Middleware\EnsureCrmAuthenticated;
use App\Http\Middleware\EnsureCrmOrganizationAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureCrmAuthenticated::class, EnsureCrmOrganizationAdmin::class])
    ->group(function (): void {
        Route::get('/organization', [OrganizationController::class, 'index'])->name('organization.index');
        Route::patch('/organization/roles/{role}/parent', [OrganizationController::class, 'updateRoleParent'])
            ->whereUuid('role')->name('organization.roles.parent');
        Route::patch('/organization/users/{user}/manager', [OrganizationController::class, 'updateUserManager'])
            ->whereUuid('user')->name('organization.users.manager');
    });
