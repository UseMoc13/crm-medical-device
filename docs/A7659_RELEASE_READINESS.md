# A7.6.5.9 — Reporting Privacy Release Readiness

**CRM Medical Device | November 2026 | standalone read-only tooling**

## Why this stage exists

A7.6.5.8 implements the opt-in Reports Aggregate Privacy Pilot with the flag OFF by default. Its controller and Blade guards are protected by targeted unit/feature tests. However, passing source checks and isolated tests does **not** establish readiness for production: the November 19 log still reports role entitlement mismatches and four cross-module integration findings.

This stage adds an independent **fail-closed review gate** without editing controllers, Blade, routes, `.env`, permissions, business data, or the database. It evaluates a captured transcript from checks already run by the operator; it never executes Artisan, bootstraps Laravel, or accesses PostgreSQL.

## Files

- `install.php`: guarded additive installer with `--dry-run` and opt-in `--replace` (backup first).
- `tools/a7659_release_gate.php`: source-presence and evidence review gate; optional JSON output.
- `tools/a7659_release_gate_test.php`: standalone synthetic unit tests for normal/unsafe/missing-evidence scenarios; writes *temporary test fixtures only* to the system temp directory.
- `docs/A7659_RELEASE_READINESS.md`: this guidance.

## Safe installation (PowerShell)

Extract the **entire package folder** `a7659_release_readiness` into your current CRM Laravel root. The final layout should be `C:\...\crm-medical-device\a7659_release_readiness\install.php`.

From your CRM Laravel root:

```powershell
php a7659_release_readiness/install.php --dry-run
php a7659_release_readiness/install.php
php tools/a7659_release_gate_test.php
```

The installer refuses to install if A7.6.5.8 policy or `tools/a7658_install_state.json` is missing (likely the November 5 archive), or if any target differs from the packaged version. It will not overwrite existing files unless specifically called with `--replace`, which backs up those files. If your current repo is not A7.6.5.8, do not force installation.

## Collect *fresh* evidence from the latest local working copy

The gate expects a text transcript that includes **all** these commands' outputs. Start a PowerShell transcript from the CRM project root; run these commands *manually in the same session*:

```powershell
Start-Transcript -Path "$env:TEMP\crm_a7659_evidence.txt" -Force

php tools/a7658_quick_check.php
php tools/a7658_runtime_preflight.php
php artisan test --filter=CrmReportAggregatePrivacyPolicyTest
php artisan test --filter=CrmReportAggregatePrivacySourceTest
php artisan test --filter=CrmReportAggregatePrivacyIsolationTest
php artisan test --filter=CrmReportPrivacyPilotHttpTest
php artisan test --filter=CrmInvoiceCsvPilotHttpTest
php artisan test --filter=CrmMultiRoleReportsHttpIsolationTest
php artisan test --filter=CrmReportAuthorizationHttpTest
php artisan crm:reporting:role-entitlements
php artisan crm:reporting:parity-baseline
php artisan crm:reporting:baseline
php artisan crm:integration:regression

Stop-Transcript
```

**Important:** The two A7.6.5.8 check commands were reported as no-business-row checks and the listed baseline commands as read-only in the user's last run. Running PHPUnit suites is a separate explicit test operation; verify that your `.env.testing` and test database are isolated from production before executing them. Do not execute these tests against your production database. The release gate itself performs no database queries.

Run the gate with your transcript:

```powershell
php tools/a7659_release_gate.php "--snapshot=$env:TEMP\crm_a7659_evidence.txt"
php tools/a7659_release_gate.php "--snapshot=$env:TEMP\crm_a7659_evidence.txt" --json
```

The transcript may contain environment details or sensitive operational information: review/redact it before sharing. The gate prints summarized statuses only, not raw transcript lines, UUIDs, customer information, or financial amounts.

## Interpretation

| Status | Meaning | Next action |
| --- | --- | --- |
| `SOURCE_OUTDATED` (exit 4) | Current source does not contain the required A7.6.5.8 files. | Switch to the newer local working copy. Do not apply controller patches from the older archive. |
| `EVIDENCE_INCOMPLETE` (exit 3) | At least one required check, test, or field is absent. | Re-run and capture complete output. |
| `HOLD_REVIEW_REQUIRED` (exit 2) | Explicit risks, failing tests, missing permissions or integration flags persist. | Review and resolve each issue; keep pilot OFF. |
| `READY_FOR_MANUAL_REVIEW` (exit 0) | Required evidence appears consistent and no blockers detected. | Obtain explicit authorization and do staging end-to-end verification. This is **not** an automatic production approval. |

The gate requires (1) A7.6.5.8 install manifest present and static 50+ checks all passing, runtime 11+ checks all passing, and both evidence lines confirming default/local OFF; (2) seven targeted suite outputs PASS; (3) a clear entitlements review with zero Invoice KPI and CSV mismatch; (4) no policy reviews in parity checks; (5) `SOURCE_READY` with zero missing/query errors/reviews in KPI baseline; and (6) clear integration audit with no flagged findings, criticals or query errors.

## Current log findings (November 19, 2026)

- **Verified:** A7.6.5.8 static 50/50 PASS, runtime 11/11 PASS, seven targeted suites 20 tests PASS. Aggregate pilot OFF.
- **Financial policy review:** Finance and Management each have Reports/CSV capability without matching Invoice KPI scope. Entitlement mismatch count: 2. This does **not** justify automatically granting them Invoice access.
- **Parity:** two policy reviews remain because Reports permissions and downstream Invoice scope differ.
- **Integration:** `WATCH`, 4 flagged checks, 2 critical and 2 review: `delivery.dispatched_item_missing_warehouse`, `delivery.stockout_quantity_mismatch`, `installation.unknown_status`, `billing.unknown_status`.
- **Source artifact mismatch:** the attached repository ZIP is dated around November 5 and does not contain A7.6.5.8. Do not treat a passed log from November 19 as proof that the earlier ZIP contains the newer implementation.

The correct release-gate outcome with the **latest** source and this evidence is `HOLD_REVIEW_REQUIRED`. The same log against the **old archive** yields `SOURCE_OUTDATED`, taking precedence over the reviews.

## Review before enabling the pilot

**Privacy policy:** Confirm with the CRM owner which roles can see Invoice KPI, finance details, customer/product breakdowns and direct CSV. For each report group (Financial, Operations, Pipeline, Warranty), validate the effective permission and explicit `all` scope. Operations must consider both Service Tickets and Maintenance; partial/unknown scopes must fail closed. Do not infer a permission from display rights to the Reports page alone.

**Isolation:** On a non-production copy, verify Reports-only roles issue zero business queries when the aggregate flag is ON; denied blocks say access is restricted rather than showing `0` as actual data; direct CSV endpoints are blocked; multi-role scenarios do not bypass scope rules; legitimate full-scope access still works; and with the flag OFF behavior remains backward compatible.

**Integration:** Investigate the four flagged cross-module statuses/stocks *read-only first*. Do not rename status values, rewrite movements, update quantities, or backfill warehouse IDs without an authorized data correction plan and audit trail.

**Rollout:** Keep the aggregate flag OFF until the role policy review, integration findings, data privacy testing, and staging sign-off are completed. This gate does not change flags or permissions and cannot substitute for end-to-end UI testing.

## Big-Changes branch compatibility (verified from GitHub)

The branch `Big-Changes` includes `CrmReportAggregatePrivacyPilot`, all seven required targeted test files, `tools/a7658_install_state.json`, and PHPUnit configuration using SQLite `:memory:`. `main` is not the recommended target. Fresh evidence need not reproduce the original A7.6.5.8 installer command; its manifest is checked directly.

For a one-command PowerShell collector use `tools/a7659_collect_evidence.ps1`. By default tests are excluded; add `-RunTests -ConfirmIsolatedTestDatabase` **only after reviewing the local PHPunit environment**. Never provide the transcript externally before checking for sensitive information. The collector does not execute migrations or change grants. The gate recognizes `A7659_EXIT_CODE` from this collector and prevents READY if an audit exits nonzero.
