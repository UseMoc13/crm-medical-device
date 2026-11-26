# A7.9.9 — Integration Review & Controlled Assignment Remediation

Target: CRM Medical Device local prototype after A7.9.7 and A7.9.8 successfully installed. Tested source basis: `Big-Changes` controller/schema and A797/A798 bundle contracts. **No migration, no route changes, and no existing source files replaced.**

## Purpose

A798 audit reported eight older assignments needing review: two Service Tickets, two Maintenance Schedules, one Maintenance Record and three Installations assigned to a Sales user. **Eight review items do not mean eight records are safe to change.** The status of each record determines whether it may be corrected without falsifying completed medical-device operations.

- `a799_integration_gate.php` scans six modules and separately reports operational (potentially correctable) and historical findings. It also checks the A798 Work Log author column. All operations are read-only.
- `a799_assignment_candidates.php` lists eligible active technician UUIDs **from the same branch** as the existing assignee. Read-only.
- `a799_assignment_reassign.php` previews one correction and only modifies one record after explicit `--apply --confirm=A799-ONE-RECORD`, a written reason, identity checks, and an audit log entry. If a check fails, nothing is changed.
- `CrmOperationalAssignmentRemediator` refuses reassignment of old performers (`maintenance_records`), finalized tickets, completed/cancelled schedules and installations, valid existing assignees, missing or ineligible targets, mismatched expected assignee, and cross-branch changes.
- No automatic bulk remediation. History remains intact. Role/permission grants and logged-in staff are unaffected.

## Safety preflight — REQUIRED before write actions

1. **PostgreSQL backup** of `crm_medical_device` using `pg_dump -F c`; confirm the resulting backup file exists and backup exits 0.
2. Commit/checkpoint Git changes; the A799 ZIP contains new files only.
3. Confirm database is the disposable local prototype and `.env` uses `APP_ENV=local`, local PostgreSQL host and database `crm_medical_device`. The tool rejects other environments. This check is defense-in-depth, not proof the database is safe.
4. Stop web/queue workers during corrections to prevent concurrent assignment changes. The tool also uses DB transactions and row locks.
5. **Never reassign historical Maintenance Records** or finalized Tickets/Installations/Schedules: that would incorrectly rewrite who performed a past event. If an immutable row is inaccurate, record a separate correction or review note in the supported workflow, not a fake `user_id` change.

## Install (merge ZIP into repository root)

Extract files to root `C:\Users\William\Desktop\crm-medical-device`, keeping existing application files. **No installer or database migration required.**

```powershell
php tools/a799_source_check.php
php artisan test --filter=CrmOperationalAssignmentPolicyTest
php tools/a799_integration_gate.php --limit=12
```

Output can be `A799_GATE_REVIEW_REQUIRED`: this is expected if the old assignment anomalies still exist. For CI or a strict gate use `--strict`; exit code **3** means actionable findings, not an installer or database failure.

## Find a candidate UUID (read-only)

The A798 report prints the current assignee (`assigned=...`) and its record ID. Use that UUID here:

```powershell
php tools/a799_assignment_candidates.php --from=UUID_OF_CURRENT_ASSIGNEE
```

The tool lists active same-branch Technician candidates and their IDs.

## Preview ONE change (never writes)

```powershell
php tools/a799_assignment_reassign.php --table=service_tickets --record=RECORD_UUID --from=OLD_USER_UUID --to=NEW_TECHNICIAN_UUID
```

Change `--table` to `maintenance_schedules` or `installations` when appropriate. Exact status/branch and role checks apply. If `[RESULT] A799_REASSIGN_DRY_RUN_ONLY` appears, the record is safe to propose for correction; no changes are made yet.

## Apply ONE correction (writes with audit; backup required)

Only after verifying every UUID and taking a backup:

```powershell
php tools/a799_assignment_reassign.php --table=service_tickets --record=RECORD_UUID --from=OLD_USER_UUID --to=NEW_TECHNICIAN_UUID --reason="Correction after assignment role audit A799" --apply --confirm=A799-ONE-RECORD
```

Expected: `A799_ONE_RECORD_REASSIGNED_WITH_AUDIT`. The database transaction changes only that one `user_id` and writes an entry in `audit_logs` containing old/new IDs, workflow status and reason. `audit_logs.user_id` is NULL to avoid impersonating a human actor from a local CLI. It never changes `created_by` or `updated_by`. A repeated command will fail the expected `--from` check.

## Regression and acceptance

```powershell
php tools/a799_integration_gate.php --limit=12
php tools/a798_assignment_integrity_audit.php --limit=12
php artisan test --filter=CrmWorkLogAuthorPolicyTest
php artisan test --filter=CrmUserAssignmentEligibilityConfigTest
php artisan test --filter=CrmOperationalAssignmentPolicyTest
```

Manual browser UAT: Service Ticket `In Progress`, edit an ineligible assignee to eligible Technician, confirm the current Technician can add a Work Log, and the Work Log author still reflects the **actual account**. Maintenance and Installation create/edit only list eligible Technician users; Leads/Opportunities only Sales. Old finalized maintenance and installation historical records retain original IDs and remain marked for review.

## Caveats

- No automated HTTP tests or PostgreSQL tests were executed by the package author. Source is validated with PHP `-l` and pure policy exercises only.
- The gate is **read-only**, but it is not a substitute for reviewing status and factual work evidence.
- This CLI is an operator maintenance tool, not a user-facing privilege grant. Protect server filesystem and database credentials; **do not expose it as a web route**.
- The CLI checks branch equality because it has no authenticated CRM actor/Data Scope. For cross-branch reassignment use authorized CRM UI and its existing scope/role validation, not a CLI bypass.
- If you have a completed installation or a maintenance record performed by someone with the wrong role, review and annotate the real history rather than altering authorship.
