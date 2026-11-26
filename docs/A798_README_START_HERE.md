# A7.9.8 — Assignment Integrity & Technician Work Log authorship

Target: CRM Medical Device, `Big-Changes` with A7.9.7 already installed.

## What this batch changes
- Adds nullable `service_ticket_work_logs.created_by_user_id` referencing `users.user_id`, indexed and delete-restricted. Old work logs stay NULL / **Legacy entry — author unavailable**. They are **not backfilled** with the assigned technician (that could be inaccurate).
- All newly submitted work logs store the **authenticated author**. Request data cannot choose a different author.
- Work-log writing restricted to active **currently assigned eligible technician**, or protected Super Admin for attributed administrative interventions. Existing route permissions and row-scope enforcement still apply. No new permissions granted.
- Ticket Show displays the author or legacy marker, and hides the form if the actor is not authorized (server still enforces).
- Edit User (Super Admin only) shows counts of linked operational records and flags current ineligibility. A role/status/branch change **never rewrites old assignments**.
- Read-only cross-module CLI audit identifies existing assignments to users who are now inactive / wrong-role / protected. These findings are **review-only**, not necessarily errors. Maintains original history.

## Safety notes
- **Take a new PostgreSQL backup and Git checkpoint before install.** Stop HTTP and queue writers while deploying and migrating.
- All source patches are all-or-nothing; installer `--check` does not modify existing files. File backups are stored under `storage/app/private/a798_integrity_install_backups/`.
- ZIP includes only *new* PHP/Blade files, manifest, installer, tests. **Existing** controller and view files are changed only when running `--apply`.
- Migration adds a nullable column; never updates/deletes existing records.
- Important: **deploy the migration before reopening the website**. Controller begins querying `created_by_user_id` after patch.
- If installer says `[FAIL]` for a changed local file, do **not** force replacement. Preserve your local changes and inspect that file.
- `migrate:rollback` removes the authorship column and loses newly stored author IDs; do **not** use as a casual rollback. Restore source backup plus DB backup if full rollback is required.

## Steps (PowerShell from project root)
```
php tools/install_a798_integrity.php --check
php tools/install_a798_integrity.php --apply
php tools/a798_source_check.php
php artisan migrate:status
php artisan migrate --path=database/migrations/2026_11_26_150000_add_work_log_author_to_service_ticket_work_logs.php --pretend
php artisan migrate --path=database/migrations/2026_11_26_150000_add_work_log_author_to_service_ticket_work_logs.php --force
php artisan optimize:clear
php artisan test --filter=CrmWorkLogAuthorPolicyTest
php tools/a798_assignment_integrity_audit.php --limit=5
```
Use the targeted migration `--force` only on your intended local CRM DB, after checking `.env`/`php artisan migrate:status` and a backup. `--pretend` alone is not a substitute for backup.

## Manual UAT
1. Open Service Ticket In Progress, assigned to Dimas (Technician). Login as Dimas, create work log, check the author reads **Dimas**.
2. Login as unrelated Sales staff with service permissions, try to forge the POST to work-logs endpoint: expect 403; no row added.
3. Login as Super Admin and create an administrative work log: author must be Super Admin's actual account name, not Dimas.
4. Switch ticket to Closed or Resolved: new entry denied, log history remains.
5. Existing pre-migration log should read **Legacy entry — author unavailable**.
6. Change a user's role/status only after reviewing Edit User impact panel. Run read-only audit; previously assigned records must not be modified.
7. Verify Maintenance, Installation, Leads, Opportunities dropdown filters from A7.9.7 remain unchanged.

## Caveats
- Unit test covers pure access decision logic; **runtime HTTP and PostgreSQL tests are still required**.
- The audit checks *current* role/status eligibility, not whether a historical assignment was valid at the time of creation.
- The audit intentionally does not auto-reassign open tickets or change permission grants.
