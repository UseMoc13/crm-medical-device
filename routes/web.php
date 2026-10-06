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
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\BrandController;


Route::resource('roles', RoleController::class);

Route::resource('brands', BrandController::class);

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
    'quotations',
    QuotationController::class
);

Route::resource(
    'product-categories',
    ProductCategoryController::class
);

Route::resource(
    'products',
    ProductController::class
);

Route::get(
    '/brands',
    [BrandController::class, 'index']
)->name('brands.index');

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

Route::get(
    '/opportunities/{opportunity}/items/{item}/edit',
    [OpportunityItemController::class, 'edit']
)->name('opportunities.items.edit');

Route::put(
    '/opportunities/{opportunity}/items/{item}',
    [OpportunityItemController::class, 'update']
)->name('opportunities.items.update');

Route::delete(
    '/opportunities/{opportunity}/items/{item}',
    [OpportunityItemController::class, 'destroy']
)->name('opportunities.items.destroy');

Route::get(
    '/quotations/{quotation}/items',
    [QuotationController::class, 'items']
)->name('quotations.items');