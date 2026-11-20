# A7.7.4.1 — Invoice & Sales Order CSV Formula Protection

Target: Laravel on `Big-Changes`, PHP 8.2+, VS Code PowerShell (Windows 11).

## Why this change is needed

- Invoice CSV currently writes database text directly through `fputcsv` without spreadsheet formula protection.
- Sales Order CSV protects selected text columns with a local regex; moving to a shared helper handles Unicode/invisible prefixes consistently.
- If an attacker-controlled customer name, note or document number starts with `=`, `+`, `-`, or `@` (possibly after whitespace or invisible characters), a spreadsheet program may interpret it as a formula when a staff member opens the CSV.
- Formula protection is **defense in depth**, not a replacement for user training or verified spreadsheet settings. Never enable macros/external links from untrusted exports.

## Files & impact

New files:

- `app/Support/CrmCsvCellSanitizer.php` — pure string transformation; NO DB access.
- `tests/Unit/CrmCsvCellSanitizerTest.php`
- `tests/Unit/CrmCsvExportSourceTest.php`
- `tools/a7741_apply_csv_patch.php` — controlled two-file PHP source patch.
- `tools/a7741_quick_check.php`, `tools/a7741_csv_policy_smoke.php`, `tools/a7741_run_checks.ps1`

The patch modifies only:

- `app/Http/Controllers/ReportController.php` — invoice CSV row emission.
- `app/Http/Controllers/SalesOrderController.php` — Sales Order CSV row emission.

**No DB migrations, changes to source records, permission grants, report scope flags, SQL queries, response headers, or new routes.** All CSV header fields are unchanged.

Only **text columns** are sanitized to preserve PostgreSQL `NUMERIC` values that PHP/PDO may return as numeric *strings*, especially negative amounts.

- Invoice CSV text indices: `[0, 3, 4, 5]`: invoice number, customer, Sales Order number, status.
- Sales Order CSV text indices: `[0, 1, 2, 5, 10]`: Sales Order number, quotation number, customer, status, notes.
- Monetary amounts and dates are not sanitized or reformatted.

## Installation (without installer)

1. Be on the `Big-Changes` branch. Keep your A7.7.3 middleware as is; this patch does not touch `bootstrap/app.php`.
2. Extract ZIP into the repository root. It adds files; it does **not** include controller replacements.
3. Run the preflight and patch explicitly:

```powershell
php tools/a7741_apply_csv_patch.php --dry-run
php tools/a7741_apply_csv_patch.php --apply
```

4. Verify using:

```powershell
php tools/a7741_quick_check.php
php tools/a7741_csv_policy_smoke.php
php artisan test --filter=CrmCsv
powershell -NoProfile -ExecutionPolicy Bypass -File tools/a7741_run_checks.ps1
php artisan optimize:clear
git diff -- app/Http/Controllers/ReportController.php app/Http/Controllers/SalesOrderController.php
git status --short
```

Backups of the two original controllers are stored under the Windows `%TEMP%` directory in `crm_a7741_*` folders, outside Git. No automatic rollback occurs if later external code changes are made; restore the backups manually if necessary.

The patch checks both original source fragments exactly and fails closed if either has changed. It also refuses to change files if only one has already been patched, and performs PHP syntax checks before writing anything. Repeating `--apply` after both are patched returns `Already ...`, without duplicating changes.

## Verification and boundaries

1. Open the CSV export from a role legitimately authorized to export it. A7.7.3 route RBAC and existing Report permission/data-scope checks still apply.
2. Check that customer names and notes beginning with formula characters are prefixed with an apostrophe in the CSV output. Normal values should look the same.
3. Check negative Invoice and Sales Order monetary values remain numeric when exported.
4. Verify no sensitive data leaked to unauthorized roles. Do not enable the reporting privacy pilot until its separate approval is complete.
5. File upload validation, private file download paths, and attachment exposure require separate A7.7.4 reviews. This patch **does not** declare those areas complete.

IMPORTANT: Run PHPUnit only with the repository's testing configuration (`phpunit.xml` uses SQLite `:memory:`); the tests in this package have no DB interaction.
