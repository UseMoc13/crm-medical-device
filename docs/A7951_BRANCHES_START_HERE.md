# A7.9.5.1 — Branches CRUD (CRM Medical Device)

## Source baseline

Implementation based on `UseMoc13/crm-medical-device`, GitHub `Big-Changes` branch inspected 2026-11-24. The database already has `branches` with UUID primary key; `users.branch_id` and `crm_teams.branch_id` already reference it. **No migrations or database reset required.**

The placeholder sidebar link was `href="#"`; the new resource is named `branches.*` and lives at `/branches`.

## Install

1. In the local Laravel CRM project (`C:\Users\William\Desktop\crm-medical-device`), protect your uncommitted changes: run `git status --short` and commit or back up any overlapping files first.
2. Extract ZIP into CRM project root, preserving the directory structure. **Do not delete your existing `app`, `resources`, `routes` or `tools` folders**; merge these folders.
3. In the project's PowerShell terminal:

   ```powershell
   php tools/install_a7951_branches.php --check
   php tools/install_a7951_branches.php --apply
   php tools/a7951_branches_quick_check.php
   php artisan optimize:clear
   php artisan route:list --path=branches
   php artisan test --filter=BranchRouteAuthorizationPolicyTest
   ```

4. Sign in as the **protected Super Admin**, visit `http://127.0.0.1:8000/branches`. The UAT database should contain two branches (`UAT-B1`, `UAT-B2`).
5. Verify `Users > Create/Edit > Branch` contains `Manage Branches →` and that the dropdown reflects new branches after reloading the user form.

The installer modifies five existing source files by exact anchors, making a private backup under `storage/app/private/a7951_branch_install_backups/<timestamp>` first. If the repository differs, it **aborts before changing any file** instead of guessing. The installer is idempotent; run `--check` first.

## Files introduced

- `app/Http/Controllers/BranchController.php` — CRUD, search/filter/sort/pagination, race-aware deletion and audit events.
- `routes/branches.php` — routes protected by `EnsureCrmAuthenticated` and `EnsureCrmOrganizationAdmin`.
- `resources/views/branches/index.blade.php`, `create.blade.php`, `edit.blade.php`, `show.blade.php` — CRUD views.
- `resources/views/branches/_form.blade.php` — common form.
- `resources/views/branches/_style.blade.php` — one module stylesheet.
- `tests/Unit/BranchRouteAuthorizationPolicyTest.php` — pure route authorization test; no database access.
- `tools/*` — safe installer/static checks.

Installer changes:

- `routes/web.php` adds `require __DIR__ . '/branches.php';`.
- `resources/views/components/sidebar.blade.php` replaces Branches placeholder with gated navigation.
- `app/Services/CrmRouteAuthorizationPolicy.php` includes `branches` in the Super Admin policy, in addition to explicit route middleware.
- `resources/views/users/create.blade.php` and `edit.blade.php` link to Branches; no changes to existing User field validation or role assignment logic.

## Business rules

- Read/write all restricted to Super Admin. No new grantable permission; non-admins cannot load list/show or mutate routes.
- `branch_code`: unique, uppercase normalized, 30 characters max, `[A-Z0-9_-]`.
- `branch_name`: required, 100 characters max.
- `phone`: nullable max 30; `address`: nullable max 5000.
- `status`: lowercase `active` / `inactive` (existing UAT convention, legacy mixed-case values displayed correctly).
- Deleting a Branch **refuses** when any User or Team, including archived Team, references it. This avoids silent reassignment and historical-link loss. Mark Inactive instead.
- Changes are logged to existing `audit_logs`, with UUID primary key, actor and before/after snapshots. If audit fails, CRUD transaction rolls back.
- Creating/editing a Branch does not automatically edit any user's existing branch. Existing Users and Teams forms continue using IDs.
- Flash success/error uses the existing global toast in `layouts.app`.

## Manual UAT

1. Super Admin: visit `/branches`, expect UAT-B1 and UAT-B2; search for `UAT-B1`, filter Active, sort Name, pagination.
2. Create an unused branch: code `UAT-TEMP`, name `Temporary Branch`, status Active.
3. Edit its address/phone/status and verify detail and index; ensure all other records unaffected.
4. Try to create another branch with the same code -> validation error.
5. Delete unused `UAT-TEMP` using confirmation modal -> success and audit entry.
6. Attempt deleting linked `UAT-B1` -> denied with error toast and no deletion (if UAT users/teams are assigned).
7. From Users Create/Edit, `Manage Branches →` opens `/branches`; after creating new branch, reload Users form and find it in dropdown.
8. Log in as non-Super Admin and request `/branches` directly, as well as POST/PATCH/DELETE using valid session and CSRF token; expect 403. Anonymous request should redirect to login.
9. After all operations, verify RBAC and team assignment forms still load, and audit entries only contain expected branch changes.

## Status / limitations

PHP syntax validated for controller, route and installer; a local installation fixture passed safe patching and second-run idempotence; static checker 49 checks passed. Real Laravel route registration, PostgreSQL CRUD and HTTP authorization **have not been executed** in this environment. Run UAT on the local database before merging/deployment. This package neither resets nor migrates data. Existing `crm_medical_device` synthetic dataset and the legacy backup remain untouched by this package.

## Installer hotfix A7.9.5.1-r2 (Windows CRLF)

The original installer searched for literal LF-only Blade snippets. On Windows,
`resources/views/users/create.blade.php` and `edit.blade.php` may contain CRLF,
so `--check` stopped with `Anchor mismatch` and **no project files changed**.
This hotfix normalizes line endings only in memory for anchor matching and
writes the original line ending style back. Its preflight still validates all
five target files before making backups or changes.

If the original ZIP was extracted already, it is sufficient to replace
`tools/install_a7951_branches.php` with the hotfixed version. Otherwise,
re-extract this ZIP into the project root. Re-run:

```powershell
php tools/install_a7951_branches.php --check
php tools/install_a7951_branches.php --apply
php tools/a7951_branches_quick_check.php
php artisan optimize:clear
php artisan route:list --path=branches
php artisan test --filter=BranchRouteAuthorizationPolicyTest
```

Expected: `[RESULT] BRANCHES_INTEGRATED`, quick check `checks=49`, seven
`branches.*` routes, and the unit test passing. `--check` and repeat
`--apply` after integration are safe and idempotent. This hotfix does not
run database migrations or modify CRM data. Backups of patched original
files are written under `storage/app/private/a7951_branch_install_backups/`.
