<?php

use App\Http\Controllers\Auth\CrmLoginController;
use Illuminate\Support\Facades\Route;

// Include once from routes/web.php. No extra middleware required: these are
// included by Laravel's "web" group with session + CSRF protection.
Route::get('login', [CrmLoginController::class, 'create'])->name('login');
Route::post('login', [CrmLoginController::class, 'store'])->name('login.attempt');
Route::post('logout', [CrmLoginController::class, 'destroy'])->name('logout');
