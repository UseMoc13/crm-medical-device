# A7.11.2 — Full Test/UAT Pack (read-only installer)

**Purpose:** accelerate CRM completion by connecting existing test suites and all 15 manual UAT scenarios. It does NOT claim manual UAT is done, does NOT create staging DB, and does NOT run destructive operations.

## Installation

Extract into a directory, copy the `payload/tools/*` and `payload/docs/*` files into the **same paths** in project root `C:\Users\William\Desktop\crm-medical-device` (or use `php a7112_install.php --check` then `--apply`). Installer only adds new A7112-named files, refuses to overwrite any differing file, and neither changes controllers/Blade/DB nor changes existing A7.10.x fixes.

```
php .\a7112_install.php --check
php .\a7112_install.php --apply
php tools/a7112_safe_preflight.php
powershell -NoProfile -ExecutionPolicy Bypass -File tools/a7112_run_regression.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File tools/a7112_run_regression.ps1 -Run -FullSuite
php tools/a7112_init_ledger.php
```

**CAUTION:** PowerShell regression creates a private local test log; isolated PHPUnit tests may write **only to SQLite :memory:** according to the verified phpunit.xml contract. Preflight blocks when that configuration differs. It never runs a database reset/migration or browses business data. You should still verify custom test cases do not independently connect to external databases before any broad suite (existing tests may include explicit opt-in PostgreSQL tests and skips).

Reports: `storage/app/private/a7112_reports/` (do not commit/share unreviewed contents).
Manual UAT: `docs/A7112_MANUAL_UAT_PLAYBOOK.md` and the existing A7.8.4 ledger validator.
Rollback: new files can be deleted if unchanged; installer cannot overwrite existing source. Avoid deleting any file with work/evidence added later.
