<?php

use App\Http\Controllers\TodoController;
use App\Http\Middleware\EnsureCrmAuthenticated;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureCrmAuthenticated::class])->group(function () {
    Route::patch('todos/{todo}/complete', [TodoController::class, 'complete'])->name('todos.complete');
    Route::resource('todos', TodoController::class)->parameters(['todos' => 'todo']);
});
