# A7.10.7.2 — Strict PostgreSQL CIDR Loopback Recognition

## Reason

The diagnostic shows `DB_HOST=127.0.0.1`, `server_addr=127.0.0.1/32`, `client_addr=127.0.0.1/32`, port 5432, database `crm_medical_device`, and permission `workspace_audit.view` missing. PostgreSQL emits the full network mask when converting `inet_server_addr()` to text. A7.10.7 expected exactly `127.0.0.1` or `::1`; the mismatch incorrectly rejected the local connection.

## Safety

This package **adds only new files**, leaving original A7.10.7 and existing application code intact. The new command is the A7.10.7 single-permission recovery logic with only its endpoint comparator made CIDR-aware. It retains these guards:

- `APP_ENV=local` and PostgreSQL driver
- Database name exactly `crm_medical_device`
- Host config restricted to `localhost`, `127.0.0.1`, `::1`
- `DB_URL`, if set, must also be loopback
- Actual PostgreSQL server address **exactly** `127.0.0.1` or `127.0.0.1/32`; IPv6 loopback `::1` or `/128` equivalent accepted
- `migrations` record for `2026_11_18_010000_create_workspace_audit_events` exists
- Permission metadata must be absent or match `workspace_audit.view`, module `workspace_audit`, action `view`; conflict prevents writes
- Never grants to any role or user; never changes existing permissions, roles, user_roles or role_permissions

`127.0.0.1/24`, `127.0.0.2`, private-network addresses and IPv4-mapped IPv6 are rejected. This is not a bypass for remote databases.

## Installation and commands

Extract the ZIP into the project root. It contains five **new** paths and replaces none.

```powershell
php tools/a71072_source_check.php
php artisan test --filter=CrmPgLoopbackEndpointPolicyTest
php tools/a71072_workspace_audit_permission.php --check
```

If the result is `A7107_READY_TO_REPAIR`, create a PostgreSQL backup with `pg_dump` and confirm it is the intended disposable local development database before applying:

```powershell
php tools/a71072_workspace_audit_permission.php --apply --confirm=A7107-ONE-PERMISSION
php tools/a71072_workspace_audit_permission.php --check
```

Expected results: `A7107_AUDIT_PERMISSION_CREATED` then `A7107_PERMISSION_PRESENT`. `--check` and the source/test commands never write business data. `--apply` inserts only the one missing catalogue row. Existing roles are not granted `workspace_audit.view` automatically.

Afterward, create a **new synthetic role** in the browser and confirm the redirect. Re-run isolated HTTP tests with SQLite `:memory:` only:

```powershell
$env:APP_ENV = 'testing'
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = ':memory:'
php artisan test --filter=WorkspaceRoleAuditAccessHttpTest
```

Restore the original process environment values after these tests. Do not run this command on a PostgreSQL test database.

## Recovery / rollback

If nothing was applied, there is nothing to rollback. The new files can be removed after source is checkpointed. If the single catalogue row was added, **do not automatically delete it**: it is required by the existing application feature and may later be referenced by a role permission. Back up and review dependencies before any manual reversal. This package intentionally contains **no automated rollback of database rows**.

Do not run `migrate:fresh`, `db:wipe`, seeders or migration rollbacks to fix this condition.
