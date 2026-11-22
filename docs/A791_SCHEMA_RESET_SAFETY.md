# A7.9.1 — PostgreSQL Schema Inventory & Reset Safety

**Status:** Read-only inventory. **NO RESET. NO SEEDING. NO PRODUCTION SIGNOFF.**

## Goal

The existing A7.8.1 preflight verified 34 UAT tables and 7 foreign-key hints, but full SQL INSERT generation must respect all columns, defaults, primary/foreign keys, unique constraints, CHECK constraints, enum labels and user-defined triggers. This tool reads `pg_catalog` metadata from public only and produces a stable fingerprint.

## Guardrails

- Must run under CLI on branch `Big-Changes`, `APP_ENV=local`, `DB_CONNECTION=pgsql`, loopback host.
- It is still **possible to point localhost through a tunnel or forwarded port at a remote database**. Check `.env` connection target and the active PostgreSQL cluster before running.
- Explicit `--schema-readonly`. `--snapshot` is an additional separate opt-in to create a **local** JSON file.
- PostgreSQL `SET TRANSACTION READ ONLY` plus statement/lock timeouts and `ROLLBACK` in `finally`.
- Five fixed catalog-only SELECTs; no `SELECT` against CRM tables and no SQL INSERT/UPDATE/DELETE, migration, TRUNCATE, DROP or database reset.
- Snapshot file contains schema/constraint definitions, including possible CHECK literals, but **no database name, credentials, customer data, row values or UUIDs from business tables**. Review before sharing. Snapshots may still reveal proprietary internal schema.
- Snapshot filename based on schema fingerprint. Existing snapshots are not overwritten.
- Snapshot exports are saved to `storage/app/private/a791_schema_snapshot_<fingerprint>.json`; exclude from Git commits, logs and public web downloads.

## Run

```powershell
php tools/a791_quick_check.php
php tools/a791_snapshot_smoke.php
php artisan test --filter=CrmUatSchemaSnapshotTest
powershell -NoProfile -ExecutionPolicy Bypass -File tools/a791_run_checks.ps1

# Before either of these two lines, verify the target database in .env:
php tools/a791_schema_preflight.php --schema-readonly
php tools/a791_schema_preflight.php --schema-readonly --snapshot
```

## Backup + reset policy (A7.9.2 or later)

Do NOT run reset commands on the current DB. Before *any* future reset:

1. Confirm dedicated disposable test database and permission to destroy its existing data; no production/staging/shared resources, and no forwarded localhost connection.
2. Confirm backup with `pg_dump` (custom format) and verify that the archive can be inspected/restored. Keep backup outside the project repository. This package does not execute `pg_dump` or `pg_restore`.
3. Document exact connection/cluster identity and backup artifact. Never paste passwords into the terminal/chat.
4. Review the schema snapshot to order INSERTs across all FK dependencies, respect statuses/enums/uniques and generate data only for the disposable test DB.
5. Execute UAT using the A7.8.4 ledger, retain the two policy HOLDs until approved, and rerun security regression before any release consideration.

## Output decision

`SCHEMA_INVENTORY_COLLECTED_RESET_NOT_APPROVED` — catalog ready to review. Does **NOT** authorize any reset.

`HOLD_SCHEMA_MANIFEST_GAPS` — at least one of the 34 UAT tables is missing; source/schema review required.

The absence of missing tables is not proof that all inserts, workflows, role access or production approvals are correct.
