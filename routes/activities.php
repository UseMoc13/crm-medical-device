<?php

use App\Http\Controllers\ActivityController;
use App\Http\Middleware\EnsureCrmAuthenticated;
use Illuminate\Support\Facades\Route;

// A7.2.1: Route middleware must be a class or registered alias, not a Closure.
// Fail closed until Laravel can identify the authenticated user.
Route::middleware([EnsureCrmAuthenticated::class])->group(function () {
    Route::resource('activities', ActivityController::class)
        ->parameters(['activities' => 'activity']);
});
