# A7.9.7 — One-batch user assignment eligibility

Target: Laravel CRM Medical Device, `Big-Changes` branch after A7.9.5 Branches.

## Scope

- Service Ticket create/edit: only active Technician/Service Engineer job roles.
- Maintenance Schedule create/edit: same technician selection.
- Maintenance Records create/edit: technician `Performed By` selection.
- Installation create/edit: technician/installer selection.
- Lead create/edit and index Sales filter: only active Sales job roles.
- Opportunity create/edit and index Sales filter: same Sales selection.
- Backend rejects wrong-role, inactive, or protected Super Admin accounts on **new assignments**. Existing historical assignments can remain unchanged on edit; removed or changed assignees cannot be newly assigned elsewhere.
- Multi-role via `user_roles` is recognized. `users.role_id` used only if no pivot role exists.
- Page CRUD permissions and data-scope rules are **not** granted or relaxed.
- No SQL, migration, dataset reset, or automatic role assignment is performed.
- Operational roles are explicit case-insensitive exact-name aliases in `config/crm_assignment.php`. Add *role names*, not person names, there. Role identity is separate from page permissions. This patch does not grant Technician permissions to `UAT Technician` or Sales permissions to `UAT Sales Owner`.

## Installation

1. Make a Git checkpoint or backup of local files. Stop Laravel server/queue during file patch.
2. Extract ZIP to project root (merges new files only: `app/Services/CrmUserAssignmentEligibility.php`, `config/crm_assignment.php`, `tests/Unit/CrmUserAssignmentEligibilityConfigTest.php`, `tools/`, `docs/`). No controller or view file is directly overwritten by extraction.
3. `php tools/install_a797_batch.php --check` — all 29 source steps must be `[READY]` or `[INSTALLED]`. A failed check makes **no changes**.
4. `php tools/install_a797_batch.php --apply` — backups go to `storage/app/private/a797_assignment_install_backups/<timestamp>/`.
5. `php tools/a797_quick_check.php`
6. `php artisan optimize:clear`
7. `php artisan test --filter=CrmUserAssignmentEligibilityConfigTest`
8. `php tools/a797_assignment_diagnostics.php` — only SELECT, useful for checking whether demo roles have candidates.
9. Open Service Tickets, Maintenance, Installation, Leads, Opportunities create/edit pages and test with accounts and CSRF tokens.

## UAT regression cases

- Technician: `Dimas Pratama` (role `UAT Technician`) should be selectable after A7.9.6 dataset import; Sales account should **not** be selectable.
- Sales: `Rizky Pratama` (role `UAT Sales Owner`) should be selectable; technician account should **not** be selectable.
- Attempt direct POST/PATCH with a user UUID outside the role category: expect HTTP 302 with field validation error in web flow or 422 for JSON; record must not be changed.
- Select an inactive user: reject a new assignment.
- Assign a user with an additional Technician role: accept if active and not protected Super Admin.
- Re-edit legacy CRM records whose assignee role/status changed: old user stays selected and marked `Previous assignment`; saving without changing that user is allowed, selecting another wrong-role user is rejected.
- Test Data Scope enabled/disabled as configured; the existing role/branch visibility restrictions remain enforced independently.
- Test User CRUD, Roles, Teams, Organization, Branches for no regressions.

## Limits / next work

- `ServiceTicketWorkLogController` currently checks only that a ticket is assigned before writing logs; work log authorship is not recorded. This requires separate explicit database migration and history/backfill decisions, **not** included here.
- The role alias map is a minimal migration-free identity model. Later create a dedicated `job_functions` or `role_assignment_capabilities` model to avoid relying on display role names.
- Current branch/team eligibility is still enforced by existing Data Scope where active; this batch does not introduce new branch rules when Data Scope is disabled.
- No production approval is implied until full HTTP + PostgreSQL UAT passes.
