<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\WorkspaceCollaborationController;
use App\Http\Middleware\EnsureCrmAuthenticated;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureCrmAuthenticated::class])->group(function () {
    // Keep a visible inbox separate from the existing owner-scoped Activity index.
    Route::get('activities/shared', [WorkspaceCollaborationController::class, 'sharedActivities'])->name('activities.shared');
    Route::get('activities/{activity}/collaboration', [WorkspaceCollaborationController::class, 'manageActivity'])->whereUuid('activity')->name('activities.collaboration.manage');
    Route::post('activities/{activity}/collaboration', [WorkspaceCollaborationController::class, 'addActivity'])->whereUuid('activity')->name('activities.collaboration.add');
    Route::delete('activities/{activity}/collaboration/{member}', [WorkspaceCollaborationController::class, 'removeActivity'])->whereUuid('activity')->whereUuid('member')->name('activities.collaboration.remove');
    Route::post('activities/{activity}/collaboration/entries', [WorkspaceCollaborationController::class, 'entryActivity'])->middleware('throttle:30,1')->whereUuid('activity')->name('activities.collaboration.entry');
    Route::get('activities/{activity}', [WorkspaceCollaborationController::class, 'showActivity'])->whereUuid('activity')->name('activities.show');
    Route::resource('activities', ActivityController::class)->except(['show'])->parameters(['activities'=>'activity']);
});
