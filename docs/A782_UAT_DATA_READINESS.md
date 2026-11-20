# CRM A7.8.2 — UAT Sample Data Readiness (Read-only)

## Goal

A7.8.1 confirmed 44 named routes, 34 expected schema tables, and 7 foreign-key relationships. That does NOT establish whether realistic linked business data is present. A7.8.2 reports **only whether at least one record exists** in each required table, and whether any record spans seven core foreign-key paths.

It does **not** export customer data, count all rows, test business mutations, approve roles, or mark functional UAT as passed. Data presence is not necessarily enough for testing status flows.

## Safety model

- Only run from repo branch `Big-Changes` under `APP_ENV=local`.
- Explicit `--data-readonly` mode; PostgreSQL default connection and loopback host required. **Loopback tunneling can still point to remote databases**: verify `.env` target first.
- All business SQL uses source-controlled `SELECT EXISTS(SELECT 1 ... LIMIT 1)`, returning a single boolean. No names, business IDs, amounts, raw paths, or emails are emitted.
- A single `SET TRANSACTION READ ONLY` transaction, 5-second statement timeout, 1.5-second lock timeout, rollback in `finally`.
- Database query planner may still scan rows to answer EXISTS; these are **not zero-I/O queries**. Run against disposable/staging local DB, not production.
- Does not run `migrate`, `seed`, `CREATE INDEX`, `VACUUM`, `ANALYZE`, `EXPLAIN ANALYZE`, `INSERT`, `UPDATE`, `DELETE`, or file operations.

## Decision legend

- `HOLD_SCHEMA_GAPS`: a required table is absent; recheck A7.8.1.
- `SYNTHETIC_DATA_REQUIRED`: none of the 34 tables have sample records.
- `PARTIAL_SAMPLE_DATA_REVIEW`: at least one table is empty or at least one chain lacks a linked record; likely need synthetic records in A7.9.
- `DATA_COVERAGE_CANDIDATE_NOT_UAT_PASS`: every checked table has at least one record and every checked chain has a linked record; **still not functional UAT approval**.

## Verified chain probes

1. Customer → Opportunity
2. Customer → Opportunity → Quotation
3. Customer → Opportunity → Quotation → Sales Order
4. Customer → ... → Sales Order → Delivery
5. Customer → ... → Delivery → Installation
6. Customer → ... → Sales Order → Invoice
7. Serial Number → Warranty

Optional foreign keys, legacy data, or other valid workflows may cause a chain to report a gap. It is a **sample-coverage gap**, not proof of broken foreign-key constraints.

## Commands

```powershell
php tools/a782_quick_check.php
php tools/a782_readiness_smoke.php
php artisan test --filter=CrmUatDataReadinessTest
powershell -NoProfile -ExecutionPolicy Bypass -File tools/a782_run_checks.ps1
# only after checking .env points to intended local/staging PostgreSQL
php tools/a782_data_readiness.php --data-readonly
git status --short
```

## Holds carried into UAT

- A7.7.4.3: Announcement assets on public disk can bypass publication-page checks via direct URLs. Stakeholder visibility policy is still under review.
- Reporting aggregate and Invoice CSV privacy pilots must be explicitly approved before activation. A passing opt-in regression does not establish production configuration.
- Historical release holds remain outstanding until reviewed.
- Actual write/mutation UAT must use a dedicated disposable database after A7.9 synthetic data. This tool cannot establish delivery-stock or invoice-status postconditions.
