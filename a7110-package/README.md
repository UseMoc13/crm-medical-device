# A7.11.0 — Personal Note SQLite RBAC + Example Test

Scoped follow-up to A7.10.9 REV2. Applies only TWO test files. No controller, middleware, routing, Blade, A7.10.8 navigation, or production database changes.

- `WorkspacePersonalNoteHttpTest.php`: creates SQLite `permissions` and `role_permissions` in disposable test schema, and drops both on teardown. No automatic grants.
- `ExampleTest.php`: anonymous GET `/` is expected to redirect to the named login page rather than HTTP 200.

This is deliberately a test-fixture patch, not a production bug fix. Original source SHA-256 must match; otherwise installer refuses.

## Installation (PowerShell in CRM root)

```powershell
Expand-Archive -Path "$HOME\Downloads\A7110_PersonalNote_Test_Fix.zip" -DestinationPath . -Force
php .\a7110-package\a7110_installer.php --check
php .\a7110-package\a7110_installer.php --apply
php .\a7110-package\a7110_installer.php --check
```

Use only `--apply` after `CHECK_READY`. `ALREADY_APPLIED` is harmless and changes nothing.

## Verification

```powershell
php artisan test --filter=WorkspacePersonalNoteHttpTest
php artisan test --filter=ExampleTest
php tools/a7108_navigation_fix.php --check
node tools/a7108_js_regression.cjs
php tools/a7108_full_audit.php --check
php artisan test
```

## Rollback

`php .\a7110-package\a7110_installer.php --rollback YOUR_BACKUP_ID`

No backup is created if the patch is already applied. Rollback has SHA-256 conflict checks. Inspect manually if rollback reports a conflict. Full Laravel runtime tests are not executed in this package's isolated build environment.
