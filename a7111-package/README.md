# A7.11.1 — Static Route Audit Closure

Base: `Big-Changes`, snapshot of uploaded `crm-a7109-audit-source.zip` including A7.10.8 navigation patches. Intended AFTER A7.10.9 REV2 and A7.11.0 tests.

## Verified source findings
- `resources/views/stock-movements/edit.blade.php`: unused legacy editor, references non-existent `stock-movements.update` and has NO route/controller that renders it. Deletion is an **archive-and-remove** operation: original file bytes are stored in installer backup and rollback restores them. This avoids adding any mutable stock-history endpoint.
- `resources/views/welcome.blade.php`: stock Laravel registration link is already conditional on `Route::has('register')`; because CRM does not register a public registration route, remove exactly that 7-line conditional block to eliminate the audit warning. Login link and all other welcome markup stay intact.
- No changes to PHP routes, controllers, database schema/data, permissions, three A7.10.8 navigation Blade files, or A7.11.0 tests.

## Installation (PowerShell, from CRM project root)

```powershell
Expand-Archive "$HOME\Downloads\A7111_Static_Route_Closure.zip" -DestinationPath . -Force
php .\a7111-package\a7111_installer.php --check
# Only apply if A7111_CHECK_READY
php .\a7111-package\a7111_installer.php --apply
php .\a7111-package\a7111_installer.php --check
```

IMPORTANT: If output says `A7111_ALREADY_APPLIED` before apply, the source already matches the patch; no install necessary and no new backup is created.

## Validation

```powershell
php artisan optimize:clear
php artisan view:cache
php tools/a7108_full_audit.php --check
php tools/a7108_navigation_fix.php --check
node tools/a7108_js_regression.cjs
php artisan test

git status --short
```

Expected static audit (on this exact source version): `errors=0 warnings=0`. Actual Laravel test outcome must be verified locally; 320 passing tests is the previous baseline, not an unverified claim for this stage.

## Rollback

```powershell
php .\a7111-package\a7111_installer.php --rollback a7111_YYYYMMDD_HHMMSS_XXXXXXXX
```

Use the **actual** `[BACKUP_ID]` from the apply output. Rollback refuses files modified since installation, and restores both welcome markup and the obsolete edit view.

## Safety
- Installer performs no Artisan commands, SQL, migrations, seeders, or database operations.
- Strict source SHA-256 checks; unknown source aborts.
- Validates expected hashes of the 3 previously fixed navigation pages.
- No public registration route or update endpoint is introduced.
- Backup directory `.a7111_backups/` is created inside project root (keep it private and out of version control).
- Manual UI and stock correction UAT still required: static audit alone does not verify workflows.
