<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OpportunityItemController;

Route::resource('roles', RoleController::class);

Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('/customers', [CustomerController::class, 'index'])
    ->name('customers.index');

Route::get('/customers/create', [CustomerController::class, 'create'])
    ->name('customers.create');

Route::post('/customers', [CustomerController::class, 'store'])
    ->name('customers.store');

Route::get('/customers/{customer}', [CustomerController::class, 'show'])
    ->name('customers.show');

Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])
    ->name('customers.edit');

Route::put('/customers/{customer}', [CustomerController::class, 'update'])
    ->name('customers.update');

Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
    ->name('customers.destroy');

Route::resource(
    'contacts',
    ContactController::class
);

Route::resource(
    'leads',
    LeadController::class
);

Route::resource(
    'opportunities',
    OpportunityController::class
);

Route::resource(
    'users',
    UserController::class
);

Route::get(
    '/opportunities/{opportunity}/items/create',
    [OpportunityItemController::class, 'create']
)->name('opportunities.items.create');

Route::post(
    '/opportunities/{opportunity}/items',
    [OpportunityItemController::class, 'store']
)->name('opportunities.items.store');