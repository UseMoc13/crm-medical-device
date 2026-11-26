# A7.10.7 — Workspace Audit permission catalogue recovery

## Problem

A7.10.6's role templates are installed and tested, but saving a Role fails at `app/Services/WorkspaceRoleAuditGrant.php` with `Audit permission is not seeded. Run php artisan migrate.` The `2026_11_18_010000_create_workspace_audit_events` migration is recorded `Ran` yet the specific `workspace_audit.view` catalogue row is absent. A migration status entry is not a guarantee that a catalogue row still exists (e.g. if an imported or reset dataset replaced permissions). The specific reason for the missing record has not been proven.

The Role create/update methods are wrapped in a transaction, so the failed Role operation should be rolled back. Do not run `migrate:fresh`, `db:wipe`, `migrate:refresh`, a full database seeder, or manually create a Role to work around this exception. Do not modify `WorkspaceRoleAuditGrant` to bypass the missing permission.

## What this patch does

The ZIP adds **four new PHP files and this doc only**. No existing controller, Blade, middleware, migration or database data is changed merely by extracting the ZIP. The CLI tool is read-only unless you request `--apply --confirm=A7107-ONE-PERMISSION`.

The tool refuses anything other than `APP_ENV=local`, PostgreSQL, database `crm_medical_device`, configured host localhost/loopback and actual server loopback. It verifies tables/columns and the prior migration before permitting changes. It inserts **one row** into `permissions` only if `workspace_audit.view` is missing. If the permission is already correct, it does nothing. If existing metadata differs or another prerequisite is missing, it stops without rewriting it.

**No role grants**: `roles`, `role_permissions`, `users`, and `user_roles` are untouched. Ordinary users are not granted access to Workspace Audit Logs. Existing Role permission choices and 22 templates remain unchanged.

## Commands on local Windows PowerShell

Unzip to root: `C:\Users\William\Desktop\crm-medical-device`.

1. Check the new source and test the read-only diagnostic:

```powershell
php tools/a7107_source_check.php
php artisan migrate:status
php tools/a7107_workspace_audit_permission.php --check
```

Expected while missing: `A7107_READY_TO_REPAIR`.

2. Before writing, **take a PostgreSQL backup** of `crm_medical_device` and confirm its size/exit status. This is a one-record operation, but backup is still recommended for reproducibility.

3. Apply exactly one permission:

```powershell
php tools/a7107_workspace_audit_permission.php --apply --confirm=A7107-ONE-PERMISSION
php tools/a7107_workspace_audit_permission.php --check
```

Expected: `A7107_AUDIT_PERMISSION_CREATED`, followed by `A7107_PERMISSION_PRESENT`. Repeating --apply yields `A7107_ALREADY_READY` and writes nothing.

4. Confirm isolated unit tests, then retry the Role form in the browser:

```powershell
php artisan test --filter=CrmAuditPermissionSeedPolicyTest
php artisan test --filter=WorkspaceRoleAuditAccessHttpTest
```

Tests should run against the project's isolated SQLite testing setup; never run destructive test suites against `crm_medical_device`.

### Manual UAT

Create a synthetic Role with the Sales Support template and leave `View Audit Logs` unchecked; it should save successfully and **not** acquire Workspace Audit rights. Then edit a different synthetic Role and check the Audit Logs checkbox as Super Admin: it should save and should appear in role detail. Do not use production user identities. For both, ensure CRUD template permissions have not changed unexpectedly. Avoid reusing a role name that already exists.

If the script refuses due to server host, migration, wrong schema, or conflicting metadata, do not bypass the guard. Stop and investigate the local database configuration.
