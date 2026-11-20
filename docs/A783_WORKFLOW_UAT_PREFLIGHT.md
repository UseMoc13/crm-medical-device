# A7.8.3 — Workflow UAT Readiness (No database modifications)

## Input from A7.8.2

- Static **23/23**, smoke **16/16**, PHPUnit **2 tests / 60 assertions** PASS.
- 34 expected tables; 33 have at least one record, 1 empty in Announcements group.
- All seven main cross-module sample chains are present.
- No business state transition was executed or validated by the read-only audit.

## Scope

Inventory all 15 cases from `docs/A781_UAT_MATRIX.md` and check the named HTTP methods against Laravel's loaded route collection. Test files are **supporting automated evidence**, not sign-off for any manual UAT scenario. Critical unimplemented/undecided requirements stay HOLD.

### Commands

```powershell
php tools/a783_quick_check.php
php tools/a783_workflow_smoke.php
php artisan test --filter=CrmUatWorkflowCoverageTest
php tools/a783_workflow_preflight.php --routes
powershell -NoProfile -ExecutionPolicy Bypass -File tools/a783_run_checks.ps1
```

The route preflight requires `APP_ENV=local` and `Big-Changes`. It boots Laravel solely to read named routes and checks existence of test source files; it does not query PostgreSQL, issue business HTTP requests or mutate files.

The PowerShell runner temporarily uses APP_ENV=testing and SQLite :memory: with array session/cache. It runs only selected test classes; some write fixtures to their **own disposable SQLite** connection. It restores environment variables. It deliberately excludes broad `php artisan test`, real PostgreSQL workflow tests, migrations and database reset.

## Interpretation

`WORKFLOW_BASELINE_READY_UAT_NOT_EXECUTED` only means routes and referenced test files are in place. It **never means 15 UAT scenarios passed**, nor does it verify role-specific CRUD or status transitions. `HOLD_PREFLIGHT_GAPS` means a manifest route/test is missing. `HOLD_POLICY` for UAT-13 (Announcement public asset policy) and UAT-14 (Reports Privacy Pilot approval) persists regardless of route PASS.

**Release gate**: require approved disposable staging dataset, stakeholder decisions, execution of all manual tests, and review of legacy financial/historical holds before production deployment. A7.9 is intended to reset a **verified disposable staging database only**, with a backup and explicit user approval; NEVER run it against the current business database or production.
