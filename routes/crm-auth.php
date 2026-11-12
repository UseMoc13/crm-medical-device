<?php

use App\Http\Controllers\Auth\CrmLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [CrmLoginController::class, 'show'])->name('login');
Route::post('/login', [CrmLoginController::class, 'login'])->name('login.store');
Route::post('/logout', [CrmLoginController::class, 'logout'])->name('logout');
