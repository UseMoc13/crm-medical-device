# A7.10.7.3 — Workspace Role Audit HTTP Test Alignment

## Problem

`WorkspaceRoleAuditAccessHttpTest` expected the ordinary `Staff` fixture to receive HTTP 200 for `roles.edit`, then asserted that a sensitive Audit Logs checkbox was absent. Since the CRM route security policy protects all `roles.*` endpoints as **Super-Admin-only**, this request correctly receives HTTP 403. The test was outdated, not the route middleware.

## Scope and safety

This patch changes exactly **one existing automated test method**, leaving production application code, database migrations, role grants, and UI templates unchanged. The corrected test asserts Super Admin can GET `roles.edit` and `roles.show` (200) and Staff cannot GET either endpoint (403), and that Staff does not see the audit checkbox. It does not grant Staff Role administration access.

## Install

Extract this ZIP into the CRM project root **without replacing existing files**; it introduces only `tools/a71073_*.php` and this document.

```powershell
php tools/a71073_source_check.php
php tools/a71073_align_role_audit_test.php --check
php tools/a71073_align_role_audit_test.php --apply
php tools/a71073_align_role_audit_test.php --check
```

Expected results: `A71073_TEST_PATCH_READY`, `A71073_ROLE_AUDIT_TEST_ALIGNED`, and `A71073_TEST_ALREADY_ALIGNED`.

Then run in isolated **SQLite `:memory:` test environment** (not the CRM production/operational database):

```powershell
php artisan test --filter=WorkspaceRoleAuditAccessHttpTest
php artisan test --filter=CrmRouteAuthorizationHttpTest
php artisan test --filter=CrmRoleTemplateCatalogTest
php artisan test --filter=CrmRoleAwareCrudVisibilityTest
```

Run browser Create/Edit Role and verify role audit access is restricted to Super Admin independently. The existence of `workspace_audit.view` has already been repaired in the local DB by A7.10.7.2.

## Rollback

`--apply` prints a backup ID, e.g. `20261126-123456-abc12345`. To restore the exact pre-patch test, **only while its SHA-256 still matches the patched copy**:

```powershell
php tools/a71073_align_role_audit_test.php --rollback=BACKUP_ID
```

If the source differs from either recognized version, the patch refuses rather than guessing.
