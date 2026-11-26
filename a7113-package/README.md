# A7.11.3 — Sidebar Module Header Visibility

## Symptoms
A role without any permitted pages in a CRM module still sees the empty module heading in the sidebar.

## Patch scope
- `resources/views/components/sidebar.blade.php`: wrap **seven** CRM navigation headings in an `@if(...)` condition that mirrors their existing `CrmViewAction::allows('<route>.index')` gates. Administration additionally checks `WorkspaceAuditAccess::canView(...)`, matching its Audit Logs link. The underlying links, route permission checks, sidebar styles, controllers and DB are not modified.
- `tools/a7113_sidebar_check.php`: new, read-only structural source regression checker.
- Overview and General & Workspace headings stay visible because they contain routes not governed by the CRM module RBAC catalogue (Dashboard, Calendar, To-Do, etc.).

**Important:** This is a UI visibility fix only. HTTP authorization and data access controls remain unchanged.

## Requirements
Run from the Laravel project root containing `artisan` and `resources/views/components/sidebar.blade.php`. The installer uses exact SHA-256 checks based on the audited `Big-Changes` source. If the source is different or has already been edited, it refuses to overwrite it.

## Installation
Extract ZIP contents into a new `a7113-package` directory inside your Laravel root, then run:

```powershell
php .\a7113-package\a7113_installer.php --check
php .\a7113-package\a7113_installer.php --apply
php .\a7113-package\a7113_installer.php --check
```

Store the `BACKUP_ID` from the apply output.

## Regression testing
```powershell
php tools/a7113_sidebar_check.php
php artisan view:cache
php tools/a7108_full_audit.php --check
node tools/a7108_js_regression.cjs
php artisan test
```

Browser test: sign in with a **restricted role**, verify that an entire module heading disappears if all its page links are hidden; grant a single permitted page and confirm that the heading and that page appear. Check Administration with and without Audit Logs permission, and test a Super Admin account. Hard refresh the page after permission changes.

## Rollback
```powershell
php .\a7113-package\a7113_installer.php --rollback BACKUP_ID
```
The backup directory `.a7113_backups/` is local. Preserve it until validation. Rollback checks hashes before restoring and does not touch DB.

## Validation scope
Installer and source checker were simulated on a copy of the audited source. Laravel's full suite and runtime browser UI must be verified on the user's current workstation. Do not interpret a source check as live RBAC UAT.
