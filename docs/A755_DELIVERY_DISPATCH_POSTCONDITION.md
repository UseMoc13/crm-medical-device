# A7.5.5 — Delivery dispatch postcondition (Big-Changes)

## Purpose

Block `pending -> in_transit` if the stock-out recorded in the same database transaction does not exactly match the serial-numbered Delivery allocations per warehouse/product. This reuses `CrmDeliveryCompletionGuard` already used before `in_transit -> delivered`.

### What changes

Only one additional `CrmDeliveryCompletionGuard::assertCanComplete($locked)` call in `DeliveryController::changeStatus()` immediately after `dispatchAllocatedUnits($locked)` while still inside `DB::transaction`. It is a fail-closed postcondition: a validation exception rolls back inventory decrements, stock movements and serial status changes. Existing `delivered` guard remains unchanged.

### What does NOT change

- No database migrations, permission grants, report flags or seed changes.
- No alteration of existing Delivery Items, stock movements, serials, invoice statuses or installation statuses.
- No automatic historical remediation; existing A7.6.5.10 flagged records will still be flagged until separately reviewed.
- No direct modification of UI. Existing ValidationException handling is retained.

## Installation

Extract the package **into the Laravel project root**, the folder containing `artisan`, while on `Big-Changes`. The archive contains `install_a755_dispatch_postcondition.php` at its top level and `a755_dispatch_payload/` underneath.

```
git branch --show-current
php install_a755_dispatch_postcondition.php --dry-run
php install_a755_dispatch_postcondition.php
php tools/a755_dispatch_quick_check.php
```

Installer stops on unexpected source, stale branch, modified destination test files or invalid syntax. Backup is written under `tools/a755_backups/` before controller modification; second installation is idempotent.

## Testing

First confirm that `APP_ENV=testing`, `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:` are set for PHPUnit. Only then run:

```
php artisan test --filter=CrmDeliveryDispatchPostcondition
php artisan test --filter=CrmDeliveryCompletionGuardTest
php artisan crm:integration:regression
```

Feature test uses a separate ephemeral SQLite database and will skip if PDO SQLite is unavailable. It does not exercise real PostgreSQL Delivery HTTP requests; verify with synthetic staging data before production release.

## Manual staging acceptance

1. Test `pending -> in_transit` with one allocated serial and enough stock: movement and serial status match; transition succeeds.
2. Test an artificial mismatched movement while still inside the transaction: status, inventory and movements all roll back.
3. Test `in_transit -> delivered`: existing completion guard still works.
4. Re-run `crm:integration:regression` and `tools/a7659_release_gate.php`. Existing four historical findings may persist; a clean newly created delivery does not erase a legacy finding.
5. Keep `CRM_REPORT_AGGREGATE_PRIVACY_PILOT_ENABLED=false` pending finance/management policy approval.

## Historical anomalies

The A7.6.5.10 output showed one shared Delivery record in both critical findings (missing item warehouse and mismatch). Do not guess that its warehouse can be inferred from existing `stock_movements`; verify original dispatch documents, warehouse/product allocation and audit history. For local-only details, existing commands `php artisan crm:integration:inspect --details --limit=10` and `php artisan crm:integration:remediation-review --details --limit=10` are available. **Redact identifiers and financial data before sharing logs.**

## Rollback

Restore the backed-up `DeliveryController.php` from the location printed by the installer, then optionally remove the three added tests/check/doc files. Do not run `migrate:fresh` or re-seed for this patch.
