<?php

use App\Http\Controllers\WorkspaceCalendarController;
use App\Http\Middleware\EnsureCrmAuthenticated;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureCrmAuthenticated::class])->group(function () {
    Route::get('/calendar', [WorkspaceCalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/create', [WorkspaceCalendarController::class, 'create'])->name('calendar.create');
    Route::post('/calendar', [WorkspaceCalendarController::class, 'store'])->name('calendar.store');
    Route::get('/calendar/events/{event}', [WorkspaceCalendarController::class, 'show'])->whereUuid('event')->name('calendar.show');
    Route::get('/calendar/events/{event}/edit', [WorkspaceCalendarController::class, 'edit'])->whereUuid('event')->name('calendar.edit');
    Route::match(['put','patch'], '/calendar/events/{event}', [WorkspaceCalendarController::class, 'update'])->whereUuid('event')->name('calendar.update');
    Route::delete('/calendar/events/{event}/day', [WorkspaceCalendarController::class, 'destroyDay'])->whereUuid('event')->name('calendar.destroy-day');
    Route::delete('/calendar/events/{event}', [WorkspaceCalendarController::class, 'destroy'])->whereUuid('event')->name('calendar.destroy');
});
