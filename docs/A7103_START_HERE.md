# A7.10.3 — Role-Aware CRUD Buttons (Laravel/Blade)

## Goal

Only display CRM Create, View, Edit/Update and Delete actions when the logged-in active user's **existing** RBAC grants permit the corresponding **named route**. A role with `view` + `update`, but without `delete`, can see View/Edit but not Delete. Multi-role permissions combine as a union, matching `CrmPermissionResolver`. Only a protected system Super Admin bypasses CRM permission checks.

**Backend authorization is mandatory.** This package changes *presentation only*. It does **not** grant new permissions, rewrite middleware, change data scopes or alter the database. Direct POST/PUT/PATCH/DELETE requests still pass through the existing `EnforceCrmRouteAuthorization` and business validation. Hiding a button is not a substitute for 403 authorization.

## Design

- `app/Support/CrmViewAction.php`: permission-aware, request-scoped caching, denies guests, inactive accounts, unmapped routes. Evaluates `CrmRouteAuthorizationPolicy` and `CrmPermissionResolver`, so module + page + workflow rules match backend middleware. Organization and Team actions remain protected-system-Super-Admin-only because they use independent middleware.
- `tools/a7103_crud_ui_installer.php`: conservative automated Blade patcher. It wraps complete `<a>`, `<form>` and route-backed `<button>` elements in `@if(\App\Support\CrmViewAction::allows('route.name'))`, with source markers. It also recognizes JS action triggers for Delivery status, Installation status/checklists, Service Ticket status/parts, and Maintenance status. It does not change visual CSS/JS layouts.
- The installer scans **all** Blade views (including sidebar, index, show, create, edit and applicable partials). Files and guard counts depend on the exact local branch. It skips script/style/Blade comment content. Already-guarded elements are ignored on subsequent runs.
- Unknown/unmanaged workspace actions (To-Do/Notes/Calendar) are not granted or automatically reclassified. They retain their separate backend guards and need individual UI decisions in a later batch.
- Some buttons built exclusively from JavaScript without a route or recognized action attribute cannot be classified automatically. Manual browser UAT remains required, especially on highly customized pages.

## Safe install — PowerShell

1. Check you are on the correct project branch and save any uncommitted source changes. Back up your database if you are also doing other operations, though this patch itself **never writes database data**.
2. Extract the ZIP into the **root** `C:\Users\William\Desktop\crm-medical-device`, preserving directory structure.
3. Preview all proposed Blade changes (NO files modified):

```powershell
php tools/a7103_source_check.php
php tools/a7103_crud_ui_installer.php --check
```

4. Only after reviewing the count and making a Git checkpoint, apply the change:

```powershell
php tools/a7103_crud_ui_installer.php --apply
php tools/a7103_crud_ui_installer.php --check
php tools/a7103_source_check.php
php artisan optimize:clear
```

5. Run the new tests in the existing safe isolated test setup (SQLite in memory, not the CRM PostgreSQL database):

```powershell
$env:APP_ENV = 'testing'
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = ':memory:'
$env:DB_URL = ''
$env:CACHE_STORE = 'array'
$env:SESSION_DRIVER = 'array'
$env:QUEUE_CONNECTION = 'sync'
php artisan test --filter='(CrmRoleAwareCrudVisibilityTest|CrmViewActionRouteGuardTest|CrmRouteAuthorizationHttpTest)'
Remove-Item Env:APP_ENV,Env:DB_CONNECTION,Env:DB_DATABASE,Env:DB_URL,Env:CACHE_STORE,Env:SESSION_DRIVER,Env:QUEUE_CONNECTION -ErrorAction SilentlyContinue
```

**Note:** The environment cleanup snippet removes only variables explicitly set for this testing session. If you had existing process-level overrides, record and restore their old values first. For most users, run the test command directly with the repository's `phpunit.xml` test environment instead of changing shell variables. Do not run migrations or seeds.

6. Optionally check Blade compilation on your local Windows installation:

```powershell
php artisan view:cache
php artisan view:clear
```

## Browser UAT matrix

Use a synthetic test role with Products `view + update` but no `create/delete`. Log out and log back in after modifying role permissions. Confirm:

| Role/grant | Expected UI |
|---|---|
| View only | View action; no New/Edit/Delete |
| View + Update | View/Edit; no New/Delete |
| View + Create | View/New; no Edit/Delete |
| View + Delete | View/Delete; no New/Edit |
| Protected Super Admin | All CRUD actions, subject to record workflow/validation |
| Technician without Delivery status workflow | No Mark In Transit/Confirm Delivered/Cancel buttons |
| Technician with Service Ticket work-log permission but not assigned | Work Log author policy still hides/disallows submission |
| Disabled/inactive user | No permitted actions; authentication middleware should reject session |

Independently try direct PATCH/DELETE endpoints as a role lacking those permissions: expected **403 with zero data modification**. Status constraints and record ownership/branch remain additional backend checks, even when a button is visible.

## Rollback

The installer prints a `[BACKUP_ID]` like `YYYYMMDD-HHMMSS-xxxxxxxx`, and stores originals plus SHA-256 manifest in `storage/app/private/a7103_crud_ui_backups/<ID>/`.

```powershell
php tools/a7103_crud_ui_installer.php --rollback=YOUR_BACKUP_ID
php artisan optimize:clear
```

Rollback refuses to overwrite any view modified after this patch. It does not remove the new helper/tests; those are inactive once all Blade guards are rolled back.

## Constraints

- No migrations, seeding, role-grant edits or production release approval.
- This installer is not a proof of complete UI coverage; review custom dynamic controls manually.
- If `--check` fails on your branch, send its **full output**. Do not run `--apply` after preflight failure.
- Existing response caching privacy protections must remain enabled; the UI is tailored to the current authenticated user.
- Role Permission editor already stores the CRUD grants; this task only synchronizes Blade action visibility with those grants.
