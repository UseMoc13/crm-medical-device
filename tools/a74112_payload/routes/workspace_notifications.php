<?php

use App\Http\Controllers\WorkspaceNotificationController;
use App\Http\Middleware\EnsureCrmAuthenticated;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureCrmAuthenticated::class])->group(function () {
    Route::get('workspace-notifications', [WorkspaceNotificationController::class, 'index'])
        ->name('workspace-notifications.index');
    Route::get('workspace-notifications/poll', [WorkspaceNotificationController::class, 'poll'])
        ->middleware('throttle:20,1')->name('workspace-notifications.poll');
    Route::get('workspace-notifications/settings', [WorkspaceNotificationController::class, 'settings'])
        ->name('workspace-notifications.settings');
    Route::patch('workspace-notifications/settings', [WorkspaceNotificationController::class, 'updateSettings'])
        ->middleware('throttle:15,1')->name('workspace-notifications.settings.update');
    Route::post('workspace-notifications/{notification}/open', [WorkspaceNotificationController::class, 'open'])
        ->whereUuid('notification')->name('workspace-notifications.open');
    Route::patch('workspace-notifications/{notification}/read', [WorkspaceNotificationController::class, 'read'])
        ->whereUuid('notification')->name('workspace-notifications.read');
    Route::patch('workspace-notifications/read-all', [WorkspaceNotificationController::class, 'readAll'])
        ->name('workspace-notifications.read-all');
});
