# A7.7.5.2 – Sales Order CSV N+1 Query Review

## Baseline

The PostgreSQL A7.7.5.1 report had zero estimated rows for many tables and low planner cost across sampled statements. It did not establish a missing index, so **no index/migration is recommended yet**.

## Concrete code issue

`SalesOrderController::filteredOrders()` constructs `SalesOrder::with(['quotation.opportunity.customer'])`. In the CSV streaming callback, `$query->cursor()` produces individual Eloquent models and **cannot eager-load related models**. Accessing `$order->quotation?->opportunity?->customer` therefore risks N+1 database queries.

Laravel documentation recommends `lazy()` for queries that need eager loading and bounded memory.

## Minimal scoped change

Replace the one CSV iterator:

```php
foreach ($query->cursor() as $order) {
```

with:

```php
foreach ($query->orderByDesc('sales_order_id')->lazy(200) as $order) {
```

This:
- loads up to 200 Sales Order models per batch plus eager-loaded relations;
- adds a deterministic tie-breaker for duplicate `created_at` timestamps;
- preserves existing filters, columns, amount calculations, CSV sanitizer, route and permissions;
- keeps the Invoice CSV and PDF export code unchanged.

### Caveats

`lazy(200)` uses multiple SELECT pages for large exports instead of one cursor query. Offset chunking does not guarantee a perfectly repeatable snapshot if orders are concurrently inserted or edited during export. For high-volume production exports, consider an explicit snapshot/keyset strategy in a separately reviewed change. This stage is intended to remove the documented N+1 risk without widening the scope.

## Safety

- No migrations, SQL writes, index creation, or data normalization.
- Patch tool requires branch `Big-Changes`, checks exact source anchors, creates `%TEMP%` backup.
- Tests use an isolated SQLite `:memory:` database and synthetic rows. Do not aim tests at a real CRM database.
- Performance improvement is **not empirically measured against production**. A populated staging benchmark is still required at A7.9.

## Verification

```powershell
php tools/a7752_apply_sales_order_lazy_patch.php --dry-run
php tools/a7752_apply_sales_order_lazy_patch.php --apply
php tools/a7752_quick_check.php
php artisan test --filter=CrmSalesOrderCsv
powershell -NoProfile -ExecutionPolicy Bypass -File tools/a7752_run_checks.ps1
php artisan optimize:clear
git diff -- app/Http/Controllers/SalesOrderController.php
git status --short
```

If the patch tool reports HOLD, **stop** and compare current source with the checked version; do not force-replace the file.
