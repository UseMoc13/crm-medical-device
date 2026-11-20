# A7.7.6.2 — Dashboard, Pagination & Reporting Reliability

Target branch: `Big-Changes` (Laravel 12, PHP 8.2, PostgreSQL 18). The patch adds a single `orderByDesc('sales_order_id')` to `SalesOrderController::index` immediately before its `paginate(10)` call. The original created-at ordering remains primary. This makes page order deterministic when timestamps tie.

A dedicated SQLite `:memory:` feature test creates 25 synthetic orders with identical timestamps and asserts three pages (10, 10, 5), no duplicates, predictable ordering and bounded SELECT count. Dashboard unit tests check that restricted metrics are suppressed; Reports unit tests check the existing grant guard before business queries and preserve the invoice CSV sanitizer. **Report privacy pilots remain unchanged/HOLD.** This package does not modify Dashboard, Reports, authentication, RBAC, export, schema or data. Do not use these isolated tests as a production performance benchmark.

Commands:

```powershell
php tools/a7762_apply_pagination_patch.php --dry-run
php tools/a7762_apply_pagination_patch.php --apply
php tools/a7762_quick_check.php
php artisan test --filter=CrmSalesOrderPaginationSqliteIsolation
php artisan test --filter=CrmDashboardPresentationReliability
php artisan test --filter=CrmReportingReliabilityGuard
powershell -NoProfile -ExecutionPolicy Bypass -File tools/a7762_run_checks.ps1
php artisan optimize:clear
git diff -- app/Http/Controllers/SalesOrderController.php
git status --short
```

Only after successful tests should changes be staged and committed. Backup is under `%TEMP%/crm_a7762_*`. The patch runs only in the `Big-Changes` branch, requires the expected source pattern, and syntax-checks before modifying the controller. It is idempotent and uses OS temporary files so no `.tmp.php` files appear in the project.
