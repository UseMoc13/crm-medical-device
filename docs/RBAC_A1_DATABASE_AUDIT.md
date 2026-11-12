# CRM Medical Device — RBAC Phase A1: Database Compatibility Audit

Source: `dump-crm_medical_device-202611121810.sql`, PostgreSQL 18.6; schema-only `pg_dump`, no `COPY`/data rows in the provided file.

## Verified schema facts

- `users.user_id` UUID PK; `users.role_id` UUID **NOT NULL** referencing `roles.role_id`; `users.branch_id` UUID nullable.
- `roles.role_id` UUID PK; `roles.role_name` unique, no `role_code` or `is_system` column yet.
- `permissions.permission_id` UUID PK; `permission_name` unique; `module` and `action` exist.
- `role_permissions` uses UUID PK and role_id/permission_id FKs. **No data_scope column** currently.
- `notifications` exists with `notification_id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`; no `read_at`/event key yet.
- `audit_logs` exists with `audit_log_id`, `user_id`, `action`, `table_name`, `record_id`, `old_value`, `new_value`, `created_at`.
- `announcements` exists with status/scheduling/publishing fields and creator/updater FKs; no audience/recipient tables.
- `jobs`, `failed_jobs`, and `job_batches` exist; operational queue configuration and scheduler execution not verified.
- `service_ticket_resolution_logs` and `service_ticket_work_logs` present. `maintenance_schedules` and `maintenance_records` present.

## A1 deployment strategy

**Expand first, never break old code:** `user_roles` is created and populated from `users.role_id`; the existing `users.role_id` remains **NOT NULL** and is not modified by this patch.

New pivot columns:
- `user_role_id uuid PK DEFAULT gen_random_uuid()`
- `user_id uuid NOT NULL FK users` (Laravel `$table->uuid()` defaults non-null)
- `role_id uuid NOT NULL FK roles`
- `is_primary boolean NOT NULL DEFAULT false`
- `assigned_by uuid NULL FK users` (on deletion, null)
- `created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP`

Constraints: unique `(user_id, role_id)` and PostgreSQL partial unique `(user_id) WHERE is_primary = true`. This ensures **at most** one primary per user. **At least one primary** must be enforced in transactional application logic in a later patch. It does not exist yet.

`ON DELETE CASCADE` on user pivot permits user deletion while preserving existing business FKs; `roles` deletion with assignments is restricted.

Backfill selects all current `users.role_id`; because it is non-null, every existing user receives one primary assignment. This is only an initial snapshot, NOT ongoing dual-write synchronization. Until Phase A2/A3, continue editing users/roles through existing code only with caution: changes to `users.role_id` after this migration **will not automatically update** `user_roles`. Run the consistency query below before the next deployment, and reconcile any differences.

## Verification SQL (run read-only in DBeaver/pgAdmin)

```sql
-- Existing users vs new primary assignments (should be equal after initial backfill)
SELECT
  (SELECT COUNT(*) FROM public.users) AS users_total,
  (SELECT COUNT(*) FROM public.user_roles WHERE is_primary) AS primary_assignments_total;

-- Should return 0 rows immediately after A1 migration
SELECT u.user_id, u.role_id AS legacy_role_id,
       ur.role_id AS pivot_primary_role_id
FROM public.users u
LEFT JOIN public.user_roles ur
  ON ur.user_id = u.user_id AND ur.is_primary
WHERE ur.user_role_id IS NULL OR ur.role_id IS DISTINCT FROM u.role_id;

-- Should return 0 rows: no user has multiple primary roles
SELECT user_id, COUNT(*) AS primary_count
FROM public.user_roles WHERE is_primary
GROUP BY user_id HAVING COUNT(*) > 1;

-- Check installed migration
SELECT migration, batch FROM public.migrations
WHERE migration LIKE '%create_user_roles_table%';
```

## Limitations / next phase

1. **Do not remove `users.role_id`** until all code paths are migrated. A future version must dual-write or make the pivot authoritative and fully update User CRUD/model/controllers.
2. The existence of role names/permissions/users in the actual production database **cannot be verified** from this schema-only dump. Do not seed guessed Super Admin accounts or passwords.
3. Scope enforcement needs a per-grant design. Add `data_scope` to `role_permissions` only after permission-scoping policy review, with a safe default; this patch does not grant any new permissions.
4. Dynamic sidebar, auth middleware, protected Super Admin, announcement audience snapshots, queue jobs, and notifications UI are **not enabled** by A1.
5. A full backup is required before migration. Run this on development database first. Never execute the dump on the live DB; it is for audit only.
6. Because the original app did not track role assignments in a pivot, a role change performed in old UI after A1 needs explicit backfill/reconciliation before enabling RBAC.
7. Rollback removes `user_roles`. If additional roles have been assigned after A1, rollback destroys those new assignments: back them up first.

## Recommended next development slice (A2)

- Update User and Role models to expose `roles()` plus `primaryRole()` with preserved legacy `role()` behavior during transition.
- Build a single transactional `UserRoleAssignmentService` that performs validation, primary selection, change logging, locks and synchronization with `users.role_id`.
- Protect system roles, prevent last Super Admin removal and block Admin self-escalation.
- Implement request-level permission checks only after authentication, role migration and permission tests are verified.
