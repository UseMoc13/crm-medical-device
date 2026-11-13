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
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\ServiceTicketController;
use App\Http\Controllers\ServiceTicketWorkLogController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ServiceHistoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportController;


Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::resource('roles', RoleController::class);
Route::resource('users', UserController::class);
Route::resource('brands', BrandController::class);
Route::resource('warehouses', WarehouseController::class);
Route::resource('contacts', ContactController::class);
Route::resource('leads', LeadController::class);
Route::resource('opportunities', OpportunityController::class);
Route::post(
    '/quotations/{quotation}/change-status',
    [QuotationController::class, 'changeStatus']
)
    ->whereUuid('quotation')
    ->name('quotations.change-status');
Route::resource('quotations', QuotationController::class);
Route::resource('product-categories', ProductCategoryController::class);
Route::resource('products', ProductController::class);
Route::resource('contracts', ContractController::class);
Route::post('/deliveries/{delivery}/items', [DeliveryController::class, 'addItem'])
    ->whereUuid('delivery')
    ->name('deliveries.items.store');

Route::delete('/deliveries/{delivery}/items/{item}', [DeliveryController::class, 'removeItem'])
    ->whereUuid('delivery')
    ->whereUuid('item')
    ->name('deliveries.items.destroy');

Route::resource('deliveries', DeliveryController::class);
Route::resource('customers', CustomerController::class);
Route::resource('installations', InstallationController::class);
Route::resource('warranties', WarrantyController::class);
// Technician progress notes; independent from resolution or stock movements.
Route::post('/service-tickets/{serviceTicket}/work-logs', [ServiceTicketWorkLogController::class, 'store'])
    ->whereUuid('serviceTicket')->name('service-tickets.work-logs.store');

Route::resource('service-tickets', ServiceTicketController::class);

Route::get('/contracts/{contract}/download', [ContractController::class, 'download'])
    ->name('contracts.download');

Route::get('/sales_orders', [SalesOrderController::class, 'index'])
    ->name('sales_orders.index');

Route::post('/sales_orders/from-quotation/{quotationId}', [
    SalesOrderController::class,
    'storeFromQuotation',
])->name('sales_orders.from_quotation');

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

Route::delete('/serial-numbers/{serialNumber}', [SerialNumberController::class, 'destroy'])
    ->name('serial-numbers.destroy');

// Maintenance
Route::resource('maintenance', MaintenanceController::class);

Route::get('/maintenance/{maintenance}/records/create', [
    MaintenanceController::class,
    'createRecord',
])->name('maintenance.records.create');

Route::post('/maintenance/{maintenance}/records', [
    MaintenanceController::class,
    'storeRecord',
])->name('maintenance.records.store');

Route::get('/maintenance/{maintenance}/records/{record}/edit', [
    MaintenanceController::class,
    'editRecord',
])->name('maintenance.records.edit');

Route::put('/maintenance/{maintenance}/records/{record}', [
    MaintenanceController::class,
    'updateRecord',
])->name('maintenance.records.update');

Route::delete('/maintenance/{maintenance}/records/{record}', [
    MaintenanceController::class,
    'destroyRecord',
])->name('maintenance.records.destroy');

// Service History (read-only)
Route::get('/service-history', [ServiceHistoryController::class, 'index'])
    ->name('service-history.index');

Route::get('/service-history/{serialNumber}', [ServiceHistoryController::class, 'show'])
    ->name('service-history.show');

// Billing & Reporting
Route::resource('invoices', InvoiceController::class);

Route::patch('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])
    ->name('invoices.status');

Route::get('/reports', [ReportController::class, 'index'])
    ->name('reports.index');

Route::get('/reports/export/invoices', [ReportController::class, 'exportInvoices'])
    ->name('reports.export.invoices');

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

// Sales Orders: register specific GET routes before the resource show route.
Route::get('/sales-orders/export', [SalesOrderController::class, 'export'])
    ->name('sales-orders.export');

Route::get('/sales-orders/{salesOrder}/download', [SalesOrderController::class, 'download'])
    ->whereUuid('salesOrder')
    ->name('sales-orders.download');

Route::resource('sales-orders', SalesOrderController::class)
    ->only(['index', 'show']);

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
Route::patch('/installations/{installation}/change-status', [InstallationController::class, 'changeStatus'])
    ->whereUuid('installation')->name('installations.change-status');

Route::patch('/installations/{installation}/checklists/{checklist}', [InstallationController::class, 'updateChecklist'])
    ->whereUuid('installation')->whereUuid('checklist')->name('installations.checklists.update');

Route::patch(
    '/deliveries/{delivery}/change-status',
    [DeliveryController::class, 'changeStatus']
)->name('deliveries.change-status');

// Installation -> Warranty Activation (independent of CRUD & existing status routes)
\Illuminate\Support\Facades\Route::post('/installations/{installation}/activate-warranties', [\App\Http\Controllers\InstallationWarrantyActivationController::class, 'store'])
    ->whereUuid('installation')->name('installations.warranties.activate');

// Service Ticket workflow: dedicated status change, distinct from regular CRUD update.
Route::patch('/service-tickets/{serviceTicket}/change-status', [ServiceTicketController::class, 'changeStatus'])
    ->whereUuid('serviceTicket')
    ->name('service-tickets.change-status');

// Service Ticket parts: detail-page CRUD, no inventory deduction at this stage.
Route::post('/service-tickets/{serviceTicket}/parts', [\App\Http\Controllers\ServiceTicketPartController::class, 'store'])
    ->whereUuid('serviceTicket')->name('service-tickets.parts.store');
Route::patch('/service-tickets/{serviceTicket}/parts/{part}', [\App\Http\Controllers\ServiceTicketPartController::class, 'update'])
    ->whereUuid('serviceTicket')->whereUuid('part')->name('service-tickets.parts.update');
Route::delete('/service-tickets/{serviceTicket}/parts/{part}', [\App\Http\Controllers\ServiceTicketPartController::class, 'destroy'])
    ->whereUuid('serviceTicket')->whereUuid('part')->name('service-tickets.parts.destroy');

// Maintenance status workflow (dedicated PATCH endpoint; existing CRUD is preserved).
Route::patch(
    '/maintenance/{maintenance}/change-status',
    [\App\Http\Controllers\MaintenanceWorkflowController::class, 'changeStatus']
)
    ->whereUuid('maintenance')
    ->name('maintenance.change-status');
