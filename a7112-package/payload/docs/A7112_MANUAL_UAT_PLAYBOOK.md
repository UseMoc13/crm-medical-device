# A7.11.2 — Complete manual CRM UAT playbook

**Not executed.** Source-grounded by `docs/A781_UAT_MATRIX.md`, `docs/A783_MANUAL_UAT_EXECUTION.md`, and official A7.8.4 ledger. Passing automated tests does not turn a manual case into PASS.

## Mandatory UAT safety gate

- Only use a **dedicated disposable staging PostgreSQL database** with an independently verified connection/cluster identity, approved synthetic fixture dataset, known role accounts, verified backup and documented restoration procedure. Never run business-workflow tests against existing CRM customer/business data or live production. Loopback host is not proof of a safe database: a tunnel could point to production.
- Preserve `APP_ENV=local` / current `.env` configuration for browsing staging, but **do not** run migrations, `migrate:fresh`, `db:wipe`, seeders, reset tools, or `a799_assignment_reassign` as part of this package.
- For tests involving finance, stock, invoices and file permissions, record BEFORE and AFTER synthetic record counts/statuses and an audit log reference. Never copy private file contents, credentials, raw tokens, real customer emails or actual medical data into ledger/evidence.
- Re-run each scenario once with an allowed role and once with a disallowed role where relevant; check denied requests cause **zero business mutations**. Do not infer authorization from hidden buttons—test direct URL/form requests on a disposable environment.
- For workflow statuses, use route method from actual form/route listing, not guessed POST. No destructive retry on production.
- **UAT-13 and UAT-14 remain POLICY HOLD** until stakeholder decisions documented. Historic financial/privacy holds are independent.

## Execution table (fill only after real browser/HTTP UAT)

| ID | Primary role and precondition | Steps to perform on synthetic staging | Expected proof (capture status, screenshot, DB/audit diff) |
|---|---|---|---|
| UAT-01 | Guest | Open protected customer, report and CSV URLs; inspect response headers | Redirect/401, no customer/financial disclosure, private `no-store` |
| UAT-02 | Read-only | Directly submit create/update/status requests to protected routes; compare count and audit | 403; no data modification, no misleading success toast |
| UAT-03 | Sales/CRM | Create customer, contact, lead, opportunity with linked valid synthetic IDs; repeat invalid foreign ID | Correct relations; invalid ID rejected; forms display useful validation |
| UAT-04 | Sales/approver | Progress opportunity → quotation → approval → sales order; repeat conversion | Exactly one linked sales order; no duplicate; permission/approval enforced |
| UAT-05 | Warehouse | Create catalog/inventory synthetic fixtures; test stock in/out, reconciliation and correction; attempt negative stock | Ledger quantity reconciles; invalid correction/underflow denied; **no direct historical edit** |
| UAT-06 | Authorized dispatch | Confirm delivery → in-transit → delivered; attempt invalid cancel/status transition; inspect PATCH/CSRF and ledger | Status, quantities, warehouse/product stockout, audit postconditions consistent; no second dispatch |
| UAT-07 | Installer | Create delivery-linked installation, checklist, complete; attempt forbidden sequence | Delivery linkage and checklist preserved, status permissions enforced |
| UAT-08 | Warranty staff | Activate warranty using eligible completed installation and synthetic serial, retry activation | Customer+serial+installation linkage valid, no duplicate activation |
| UAT-09 | Technician | Open service ticket, assign, add worklog/parts, resolve; try unauthorized identity | Authorized worklog and status/audit; denied access; no unintended inventory reduction |
| UAT-10 | Maintenance staff | Schedule work, create execution record, complete; test invalid date/status | Time/status constraints, linked history and actor attribution preserved |
| UAT-11 | Billing/reporting | Sales order → invoice → payment/status; inspect date-scoped report/CSV with restricted and privileged roles | Exact decimal parity, sanitized CSV formula cells, no disclosure from direct CSV request |
| UAT-12 | Authorized and unrelated users | Download permitted contract/task submission; attempt direct path/foreign owner URL | Authorized download, unauthorized denied; private files and cache boundaries |
| UAT-13 | Public/admin | With policy decision and disposable synthetic attachment, check draft/scheduled/expired/published direct public URLs | **HOLD** until policy approved; UI visibility alone not evidence of public-URL denial |
| UAT-14 | Scoped multi-role | Test dashboard/report previews, CSV, multiple roles and restricted scopes | **HOLD** until pilot/scope policy approved; no permission union that leaks invoice/customer data |
| UAT-15 | Sales/reporting | Generate disposable 25+ equal-timestamp orders and >405 synthetic CSV rows; paginate and export | Stable pagination, no duplicate/missing rows, stable batch query count |

## Extra cross-cutting checks on every editable module

1. Create/edit/delete buttons visible ONLY with correct role; direct URL access matches server permission.
2. Form required/invalid/foreign-ID errors, CSRF, HTTP verbs, cancel/back link and breadcrumb are correct.
3. Toast success/error appears and disappears; confirmation modal cannot cause double submission; no silent state mutation.
4. Search/filter/sort, empty view, pagination and a 2nd user in another branch do not leak rows.
5. Desktop browser workflow and keyboard navigation; browser reload/back is safe after POST/PATCH (redirect-after-post).
6. Sensitive response headers and downloads carry suitable caching controls; audit log records allowed changes without protected free text.

## Run ledger and triage

1. `php tools/a7112_init_ledger.php` (creates **new** private copy, all `NOT_RUN`, policy `HOLD`).
2. Keep ledger under `storage/app/private/a7112_uat/` and edit `environment`, `dataset_reference`, `backup_reference` **without secrets**.
3. For each executed case, record `role`, `expected_result`, `observed_result`, `evidence_reference`, and `defect_reference` when FAIL. Mark unexecuted cases `NOT_RUN` or `BLOCKED`, never PASS.
4. `php tools/a784_validate_uat_ledger.php --ledger=storage/app/private/a7112_uat/<filename>.json`
5. Track defects in `docs/A7112_DEFECT_REGISTER_TEMPLATE.csv` copied outside Git if sensitive. Re-test failed scenarios after fixes.
6. Even all PASS only indicates `READY_FOR_EXTERNAL_SIGNOFF_NOT_RELEASE_APPROVED` (subject to policy holds and independent approval).

## Exit gates

- Automated regression source/static + PHPUnit suites pass without unexpected skips or permission weakening.
- All 15 UAT cases executed, except consciously documented legitimate blockers; every FAIL fixed and retested.
- UAT-13, UAT-14 and historical financial/privacy policy holds explicitly resolved/approved by authorized stakeholder.
- Evidence reviewed independently; disposable database reverted/snapshotted and production signoff is a separate manual decision.
