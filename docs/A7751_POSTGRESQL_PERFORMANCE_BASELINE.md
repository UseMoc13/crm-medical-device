# A7.7.5.1 — PostgreSQL Performance Baseline

## Scope and limits

- Local (`APP_ENV=local`) PostgreSQL (`DB_CONNECTION=pgsql`) with a loopback DB host only.
- Read-only transaction, per-statement timeout, and no `EXPLAIN ANALYZE`.
- Reads PostgreSQL `pg_stat_user_tables` statistics and approximate row counts; takes **estimated query plans** for a fixed set of read-only SELECT queries.
- Prints table names and fixed plan labels, estimated row counts, scan counts, node types and planner cost only. No customer names, IDs, SQL filters or sensitive data.
- **This is not a load test.** `EXPLAIN` estimates may be misleading on a small database or with stale statistics. A sequential scan is not automatically a problem.
- No migration, DDL, index creation, ANALYZE, VACUUM, seed, database reset, role changes or data writes.

## Tests

```powershell
php tools/a7751_quick_check.php
php tools/a7751_plan_smoke.php
php artisan test --filter=CrmQueryPlanDigestTest
php artisan test --filter=CrmPerformanceBaselineSourceTest
powershell -NoProfile -ExecutionPolicy Bypass -File tools/a7751_run_checks.ps1
```

## Run baseline (read-only PostgreSQL)

```powershell
php tools/a7751_pg_baseline.php --run
```

`--run` is mandatory. The command refuses non-local databases, non-PostgreSQL drivers and other APP_ENV values. This cannot guarantee a local tunnel does not lead to a remote database; check `.env` manually before running.

## Interpret

- `estimated_rows` are approximations from PostgreSQL statistics, not exact counts.
- `index_scans=0` does **not** prove an index is missing or that the plan is inefficient, especially for small datasets.
- Compare `invoice_date_wrapped` vs `invoice_date_range`, `ticket_date_wrapped` vs `ticket_date_range`, and `maintenance_date_wrapped` vs `maintenance_date_range`.
- Differences justify investigation, **not immediate index creation** or rewriting query logic.
- After A7.9 reset and controlled SQL seed insertion, run the same baseline again on the representative final dataset.
- Any next performance patch needs `EXPLAIN` evidence, test expectations and explicit rollback/QA plan.

## Evidence to share

Copy the command output (`[TABLE]`, `[PLAN]`, `RESULT`). It does not include business rows. Do not share `.env` or SQL dumps with credentials.
