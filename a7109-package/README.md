# CRM Medical Device — A7.10.9 REV2 (source-locked installer)

Audited against user's `crm-a7109-audit-source.zip` (branch Big-Changes, local A7.10.8 modified views included). **Do not use previous A7109_CRM_Safe_Installer.zip.**

## Installation (PowerShell, from Laravel project root)

```powershell
Expand-Archive -Path "$HOME\Downloads\A7109_CRM_Audited_REV2.zip" -DestinationPath . -Force
php .\a7109-package\a7109_installer.php --check
php .\a7109-package\a7109_installer.php --apply
php .\a7109-package\a7109_installer.php --check
```

* Modes: `--check` dry run, `--apply` makes exact source-locked changes, `--rollback BACKUP_ID` restores the five files after verifying their current hashes.
* Backup: `storage/app/private/a7109_rev2_backups/<BACKUP_ID>/original/...`.
* **Does not connect to DB, migrate, seed, run `artisan test`, or touch A7.10.8 navigation views.**
* If a source file differs from the attached snapshot, installer refuses to write. It also ensures all three A7.10.8 navigation views match hashes from the snapshot.
* PHP syntax validation of payload tests is performed before writes. Blade still requires `view:cache` verification manually.

## Five changed files

1. `resources/views/auth/login.blade.php` — `login.store` -> `login.attempt` (registered POST route).
2. `tests/Unit/CrmReportPrivacyPilotSourceTest.php` — assert both top-list gates within bounded source blocks instead of counting all guarded list queries.
3. `tests/Feature/WorkspaceCollaborationHttpTest.php` — isolated SQLite permission tables, role grants just for the existing A7.4.8 collaboration test activity endpoint scenarios. Owner-only checks in controllers remain intact. Cleanup both tables.
4. `tests/Feature/WorkspaceNotificationHttpTest.php` — isolated SQLite permission tables without grants; cleanup.
5. `tests/Feature/WorkspaceNotificationPreferencesHttpTest.php` — same SQLite fixture tables; cleanup.

## Verification

```powershell
php artisan optimize:clear
php artisan view:cache
php artisan test --filter=CrmReportPrivacyPilotSourceTest
php artisan test --filter=WorkspaceCollaborationHttpTest
php artisan test --filter=WorkspaceNotificationHttpTest
php artisan test --filter=WorkspaceNotificationPreferencesHttpTest
node tools/a7108_js_regression.cjs
php tools/a7108_source_check.php
php tools/a7108_navigation_fix.php --check
php tools/a7108_full_audit.php --check
php artisan test
```

## Holds (explicitly NOT modified)

- `resources/views/stock-movements/edit.blade.php` still references unsupported `stock-movements.update` and remains a static audit error. Converting that historical edit screen to a correction workflow needs a separate scoped UI change; do not add an unsafe direct update route.
- `resources/views/welcome.blade.php` has optional `register` link wrapped with `Route::has('register')`; A7.10.8 reports a warning, not a broken runtime link.
- `tests/Feature/ExampleTest.php` expects status 200 on `/`, but the protected dashboard redirects to login (302). Separate assertion change only after verifying redirect target.
- New SQLite grants are explicitly test-only and do not apply to production. These fixtures must still be tested under a real Laravel test runtime. All earlier tests and real manual UAT still remain a gate.

## Rollback

```powershell
php .\a7109-package\a7109_installer.php --rollback <BACKUP_ID>
```

File hashes are checked before rollback. If someone edits the patched files after installation, rollback refuses to overwrite those edits. Git pre-change backup can be used for additional recovery.
