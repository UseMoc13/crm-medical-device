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
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SerialNumberController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AnnouncementManagementController;

Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::resource('roles', RoleController::class);
Route::resource('users', UserController::class);
Route::resource('brands', BrandController::class);
Route::resource('warehouses', WarehouseController::class);
Route::resource('contacts', ContactController::class);
Route::resource('leads', LeadController::class);
Route::resource('opportunities', OpportunityController::class);
Route::resource('quotations', QuotationController::class);
Route::resource('product-categories', ProductCategoryController::class);
Route::resource('products', ProductController::class);
Route::resource('customers', CustomerController::class);


Route::get('/inventories', [InventoryController::class, 'index'])
    ->name('inventories.index');

Route::get('/stock-movements', [StockMovementController::class, 'index'])
    ->name('stock-movements.index');

Route::get('/stock-movements/create', [StockMovementController::class, 'create'])
    ->name('stock-movements.create');

Route::post('/stock-movements', [StockMovementController::class, 'store'])
    ->name('stock-movements.store');

Route::get('/stock-movements/correction/create', [
    StockMovementController::class,
    'createCorrection',
])->name('stock-movements.correction.create');

Route::post('/stock-movements/{stockMovement}/correction', [
    StockMovementController::class,
    'storeCorrection',
])->whereUuid('stockMovement')
    ->name('stock-movements.correction.store');

Route::get('/stock-movements/{stockMovement}', [
    StockMovementController::class,
    'show',
])->whereUuid('stockMovement')
    ->name('stock-movements.show');

Route::get('/serial-numbers', [SerialNumberController::class, 'index'])
    ->name('serial-numbers.index');

Route::get('/serial-numbers/create', [SerialNumberController::class, 'create'])
    ->name('serial-numbers.create');

Route::post('/serial-numbers', [SerialNumberController::class, 'store'])
    ->name('serial-numbers.store');

Route::get('/serial-numbers/{serialNumber}', [SerialNumberController::class, 'show'])
    ->name('serial-numbers.show');

Route::get('/serial-numbers/{serialNumber}/edit', [SerialNumberController::class, 'edit'])
    ->name('serial-numbers.edit');

Route::put('/serial-numbers/{serialNumber}', [SerialNumberController::class, 'update'])
    ->name('serial-numbers.update');

Route::get('/opportunities/{opportunity}/items/create', [
    OpportunityItemController::class,
    'create',
])->name('opportunities.items.create');

Route::post('/opportunities/{opportunity}/items', [
    OpportunityItemController::class,
    'store',
])->name('opportunities.items.store');

Route::get('/opportunities/{opportunity}/items/{item}/edit', [
    OpportunityItemController::class,
    'edit',
])->name('opportunities.items.edit');

Route::put('/opportunities/{opportunity}/items/{item}', [
    OpportunityItemController::class,
    'update',
])->name('opportunities.items.update');

Route::delete('/opportunities/{opportunity}/items/{item}', [
    OpportunityItemController::class,
    'destroy',
])->name('opportunities.items.destroy');

Route::get('/quotations/{quotation}/items', [
    QuotationController::class,
    'items',
])->name('quotations.items');

Route::get('/opportunities/{opportunity}/items', [
    OpportunityController::class,
    'items',
])->name('opportunities.items');

Route::resource('sales_orders', SalesOrderController::class)
    ->only(['index']);

Route::controller(AnnouncementController::class)
    ->prefix('announcements')
    ->name('announcements.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{announcement}', 'show')
            ->whereUuid('announcement')
            ->name('show');
    });

Route::controller(AnnouncementManagementController::class)
    ->prefix('announcement-management')
    ->name('announcement-management.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');

        Route::post('/{announcement}/toggle-pin', 'togglePin')
            ->whereUuid('announcement')
            ->name('toggle-pin');

        Route::get('/{announcement}/edit', 'edit')
            ->whereUuid('announcement')
            ->name('edit');

        Route::get('/{announcement}', 'show')
            ->whereUuid('announcement')
            ->name('show');

        Route::put('/{announcement}', 'update')
            ->whereUuid('announcement')
            ->name('update');

        Route::delete('/{announcement}', 'destroy')
            ->whereUuid('announcement')
            ->name('destroy');
    });
