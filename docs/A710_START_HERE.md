# A7.10.1 + A7.10.2 — Final Functional UAT and Security/RBAC Audit

Basis: `Big-Changes` after A7.9.9 (`operational=0, historical=4`). **This bundle adds only seven new files. It does not edit controllers/views, migrate schemas, assign roles, or modify existing records.**

## Install and safe commands

1. Confirm Git working tree, make a Git checkpoint, and retain PostgreSQL backup. Extract this ZIP at CRM project root, with directory structure intact.
2. Run the source-only checker: `php tools/a710_source_check.php`.
3. Run routes-only RBAC: `php tools/a710_route_security_gate.php --routes`.
4. Run existing workflow preflight: `php tools/a783_workflow_preflight.php --routes`.
5. On **Windows PowerShell**, run one command (not separate `--` flags):

   ```powershell
   powershell -ExecutionPolicy Bypass -File tools/a710_run_checks.ps1
   ```

   This runs selected isolated SQLite-memory tests with temporary **process-only** env overrides, then restores the previous process environment. It does NOT run every project's test or migrate a PostgreSQL database. You may use `-QuickOnly` to run source/routes checks without PHPUnit.

6. For the **optional local PostgreSQL READ-ONLY review**, first double-check `.env` points to `crm_medical_device` on `localhost` or `127.0.0.1`, with `APP_ENV=local`; then run:

   ```powershell
   powershell -ExecutionPolicy Bypass -File tools/a710_run_checks.ps1 -WithDatabase
   ```

   The dedicated DB review uses `SET TRANSACTION READ ONLY`, a statement timeout and rollback. The A799 follow-up gate uses `--limit=0 --strict` and only SELECT operations. Despite the loopback check, tunneled / forwarded databases cannot be detected; verify your DB target manually.

> **Do not** run migrations, seeds, browser write workflows, UAT payloads, or `php artisan test` without these isolated env overrides against valuable/production databases.

## What is checked

- **Route & RBAC:** 20 explicitly sensitive workflow and admin route contracts; expected HTTP method and exact module/page/workflow permission lists; `roles`, `users`, `branches` admin-only; `organization` and `teams` independent admin middleware; globally appended web auth/RBAC/cache guards; accidental incomplete catalogue entries; independent write routes are disclosed for separate review (not auto-cleared).
- **Automation:** selected existing A7.7/A7.8/A7.9 unit/feature tests for authentication, authorization, assignment, historical immutability, workflows, invoice/report privacy, private files, pagination and CSV.
- **DB integrity (opt-in):** `user_roles` primary-role alignment, invalid statuses, orphan role memberships/permission grants, active protected admin count and work-log author attribution. Counters only; no account names or record IDs printed by the A710 DB checker.
- **A799 regression:** operational assignment review must remain zero; four historical findings are preserved, not remediated by automation.

## Manual UAT / signoff: NOT AUTOMATICALLY PASSED

Keep using the 15-case ledger `docs/uat/a784_uat_execution_template.json` **as a template, never overwrite the original**. Copy it to a new name in a *disposable* UAT workspace. It begins with `NOT_RUN` for all cases and `HOLD` for policy decisions. Validation:

```powershell
php tools/a784_validate_uat_ledger.php --ledger=path/to/copy.json
```

The ledger is self-reported, and even all 15 PASS does **not** independently authorize production release. Use synthetic data and the appropriate roles, record browser screenshots, request method, expected/actual HTTP response, IDs in restricted evidence only (not public logs), business-state changes, and defects. See `docs/A781_UAT_MATRIX.md`, `docs/A782_UAT_EVIDENCE_TEMPLATE.md`, `docs/A783_MANUAL_UAT_EXECUTION.md`.

| Scenario | Manual evidence required (not inferred from passing unit tests) |
|---|---|
| UAT-01 | Guest session / active and inactive login, redirect or 401; no-store cache headers |
| UAT-02 | Read-only user direct POST/PATCH/DELETE denied with zero writes |
| UAT-03 | Customer/Contact/Lead/Opportunity end-to-end; non-Sales assignee rejected |
| UAT-04 | Quotation approval, Sales Order creation exactly once, duplicate refusal |
| UAT-05 | Stock ledger reconciliation, movement reversal/correction/underflow validation |
| UAT-06 | Delivery allocation, dispatch, completion, inventory postcondition |
| UAT-07 | Installation checklist completion, technician role restriction |
| UAT-08 | Serial-linked warranty activation and duplicate prevention |
| UAT-09 | Service Ticket: Technician-only assignment, author-attributed Work Log, parts/status, wrong actor 403 |
| UAT-10 | Maintenance: Technician-only assignee, historical Performer retained, workflow rules |
| UAT-11 | Invoice lifecycle, financial KPI/report parity, CSV access and formula escaping |
| UAT-12 | Private contract file download permitted/denied by role and record visibility |
| UAT-13 | **POLICY HOLD**: public attachment access for draft/scheduled/expired Announcement |
| UAT-14 | **POLICY HOLD**: multi-role dashboard/report aggregate privacy and financial access |
| UAT-15 | Pagination and large CSV without duplicate/missing rows or data leaks |

## Known unresolved decisions

1. **Announcement public-disk attachment exposure** (`UAT-13`): approval / implementation decision required. Do not treat source tests as fixing direct unauthenticated asset URLs.
2. **Financial reporting privacy pilot** (`UAT-14`): role-specific KPI/report exposure requires explicit business approval. Do not auto-enable a pilot or grant Invoice permissions to Sales/Management.
3. **Historical role exceptions**: four older finalized/performed records have ineligible assignees, but they are immutable historical records. Retain as evidence, not an operational blocker.
4. **Release condition**: tests and non-destructive gates are technical baselines only. Actual multi-role browser UAT, policy signoff, backup/restore rehearsal, infrastructure review and written stakeholder signoff are required before any deployment approval.

## Expected output labels

- `A710_SOURCE_READY` — source files syntactically present; not runtime proof.
- `A710_ROUTE_SECURITY_CONTRACT_READY` — route baseline matches; independent endpoints need separately enforced guards.
- `A710_RBAC_DATABASE_INTEGRITY_READY` — selected User/Role consistency checks clear.
- `A710_RBAC_DATABASE_REVIEW` — selected integrity checks clear, but non-blocking review indicators exist.
- `A710_AUTOMATED_BASELINE_READY` — selected automated checks returned 0; **manual UAT remains pending**.
- A failure or mismatch should be investigated; do not claim release PASS.

## Next milestone

**A7.10.3 — Backup / restore rehearsal, deployment preflight, rollback strategy.** Never connect these tooling scripts to production or mutate a business DB to satisfy a green summary.
