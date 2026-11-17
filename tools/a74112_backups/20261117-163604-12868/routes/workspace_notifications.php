<?php

use App\Http\Controllers\WorkspaceNotificationController;
use App\Http\Middleware\EnsureCrmAuthenticated;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureCrmAuthenticated::class])->group(function () {
    Route::get('workspace-notifications', [WorkspaceNotificationController::class, 'index'])
        ->name('workspace-notifications.index');
    Route::post('workspace-notifications/{notification}/open', [WorkspaceNotificationController::class, 'open'])
        ->whereUuid('notification')->name('workspace-notifications.open');
    Route::patch('workspace-notifications/{notification}/read', [WorkspaceNotificationController::class, 'read'])
        ->whereUuid('notification')->name('workspace-notifications.read');
    Route::patch('workspace-notifications/read-all', [WorkspaceNotificationController::class, 'readAll'])
        ->name('workspace-notifications.read-all');
});
