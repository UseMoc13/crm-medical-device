<?php

use App\Http\Controllers\WorkspaceAuditController;
use App\Http\Middleware\EnsureCrmAuthenticated;
use Illuminate\Support\Facades\Route;

// Read-only, explicit permission is enforced inside the controller (not only navigation).
Route::middleware([EnsureCrmAuthenticated::class])->group(function (): void {
    Route::get('/workspace-audit', [WorkspaceAuditController::class, 'index'])
        ->middleware('throttle:60,1')->name('workspace-audit.index');
});
