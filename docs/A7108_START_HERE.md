# A7.10.8 — CRM Full-Page Navigation / CRUD Audit

This package covers two independent goals:
1. **Read-only static audit** of every current Blade template and application route: compile + PHP syntax, named URL links, missing route parameters, form HTTP verb/CSRF, controller actions, route duplication, static includes.
2. **Narrow corrective patch** for three real runtime errors on pages where role permissions hide workflow confirmation forms: Delivery Details, Installation Details, Maintenance Workflow. Also removes one accidental `1` in Delivery Details HTML.

## Compatibility & safety

- For the current Laravel 12 / PHP 8.2+ CRM codebase. Package adds **new files only** under `tools/` and `docs/`; no Blade file is replaced just by unzipping.
- Audit tool `--check` never sends HTTP mutation requests or modifies app/database files. It **boots Laravel** to inspect its registered routes; unusual service providers might perform their own reads during bootstrap. No migrations are executed.
- Navigation patch `--check` validates the *proposed* edits through the live Blade compiler and `php -l` and makes no changes. `--apply` updates **exactly three views** with checksums and backup. It does not modify database/roles/permissions/controllers/routes.
- If an anchor changed after this package was produced, `--check` fails closed. Never force a patch through changed code.
- The static audit does **not** prove browser behavior of page controllers with real DB state, nor does it click UI controls. These require the guided manual UAT matrix.
- **Do not run `migrate:fresh`, `db:wipe`, or application mutation requests against the operating database.** Use synthetic disposable records and/or isolated test environment for browser CRUD testing.

## Recommended order — PowerShell in repository root

```powershell
php tools/a7108_source_check.php
php tools/a7108_full_audit.php --self-test
php tools/a7108_navigation_fix.php --check
```

Only if `A7108_NAVIGATION_FIX_READY` appears:

```powershell
php tools/a7108_navigation_fix.php --apply
php tools/a7108_navigation_fix.php --check
node tools/a7108_js_regression.js
php artisan view:clear
php artisan view:cache
```

Then run the **full read-only audit**:

```powershell
php tools/a7108_full_audit.php --check 2>&1 | Tee-Object -FilePath a7108_audit_log.txt
```

`A7108_STATIC_AUDIT_COMPLETE` indicates **zero static ERRORs**, but WARN findings and browser runtime gaps still need inspection. `A7108_STATIC_AUDIT_FINDINGS` indicates one or more static issues; check file/line and classify whether the route is actively reachable (e.g. a legacy view not used by current controllers). The audit will not silently repair these.

Run isolated existing regression tests with the project test configuration (**SQLite `:memory:`**, verify `.env.testing` before testing):

```powershell
php artisan test --filter=CrmRouteAuthorizationHttpTest
php artisan test --filter=CrmRoleAwareCrudVisibilityTest
php artisan test --filter=CrmRoleTemplateCatalogTest
php artisan test --filter=WorkspaceRoleAuditAccessHttpTest
php artisan test --filter=CrmViewActionRouteGuardTest
```

For additional coverage, run all tests only after validating test isolation; do **not** run all unreviewed tests on the operational PostgreSQL DB.

## Known source findings (verified against `Big-Changes`)

- `resources/views/deliveries/show.blade.php`: click handler for `deliveryCancel` is registered unconditionally, while A7.10.5 permission guard may omit the entire form; also a stray `1` follows a closing `<div>`.
- `resources/views/installations/show.blade.php`: unconditional status confirmation handler when permission guards may omit the form; checklist JS must remain available independently.
- `resources/views/maintenance/_workflow.blade.php`: status form elements may be absent while the modal shell remains.
- `resources/views/stock-movements/edit.blade.php` contains a form to `stock-movements.update`, but current `routes/web.php` and `StockMovementController` do not expose regular Edit/Update endpoints. It appears to be a **legacy/orphaned page** and should not be reenabled blindly: correction movement is the documented supported path. The audit will report its unresolved route reference. Treat it as a cleanup finding unless there is a real accessible navigation path to it.

The exact **full-source audit outcome is not known until you run this tool against your current local project**. The preceding PHP/Blade fixture and Node DOM tests do not replace actual browser testing.

## Rollback

The apply command prints `[BACKUP_ID] YYYYMMDD-HHMMSS-abcdefgh` and stores checksummed source backups under `storage/app/private/a7108_nav_backups/`.

```powershell
php tools/a7108_navigation_fix.php --rollback=PASTE_BACKUP_ID
php artisan view:clear
```

The rollback refuses to overwrite views changed after the patch. **Do not use older A7103/A7105 rollback scripts unless specifically advised**; those would affect broader sets of files.

## Browser UAT

Follow `docs/A7108_UAT_MATRIX.md` using a Super Admin account, one View-only account, a staff account without role management access, and a scoped workflow account. Test safe synthetic data only. For each failure, capture URL, role, click, status code, timestamp, browser console error, and matching `storage/logs/laravel.log` excerpt. Do not share session cookies or credentials in logs.
