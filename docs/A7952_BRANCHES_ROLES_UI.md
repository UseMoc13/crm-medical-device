# A7.9.5.2 — Branches UI refresh aligned to Roles

## Scope
- Source reference: `resources/views/roles/index.blade.php`, `roles/_form.blade.php`, `roles/show.blade.php`, `roles/_style.blade.php` from CRM `Big-Changes`.
- Changes **only** the six files in `resources/views/branches/`. No migrations, SQL, model, route or controller changes.
- One module stylesheet: `_style.blade.php` included once per Branches page.
- Preserves all CRUD routes/actions, backend permission checks, filtering, pagination, user/team relations, CSRF, and duplicate submission safeguards already in the backend.
- Keeps the shared layout's success/error toast handling and uses the existing validation error toast.

## Install (PowerShell, root of crm-medical-device)
1. Run `git status --short` and save any unfinished work.
2. Backup: `Copy-Item resources/views/branches "$HOME/CRM-Backups/branches-before-a7952" -Recurse -Force` (create the target backup directory first if necessary).
3. Extract this ZIP **to the CRM project root** and allow replacement of the six `resources/views/branches/*.blade.php` files only.
4. `php tools/a7952_branches_ui_check.php`
5. `php tools/a7951_branches_quick_check.php`
6. `php artisan view:clear`
7. Visit `/branches` and test search, sorting, create, detail, edit, modal deletion, and small screens.

The ZIP also includes `tools/a7952_branches_ui_check.php`, a read-only source checker.

## Rollback
Restore the six files from the backup taken in step 2. Do not reset the database.

## Validation limits
Source-level checks are available here; browser rendering, database and UAT checks must be run in your development environment.
