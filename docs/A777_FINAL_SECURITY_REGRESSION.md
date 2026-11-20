# A7.7.7 — Final Security Regression (read-only app source + isolated tests)

## Verified source basis

Repo: `UseMoc13/crm-medical-device`, branch `Big-Changes`, read on November 21, 2026.
A7.7.6.2 pagination patch is present in source. The user has locally run and passed its tests.

## Issue found: no-store middleware is inner to auth and authorization

Old web middleware order: `EnsureCrmAuthenticated`, `EnforceCrmRouteAuthorization`, `PreventCrmResponseCaching`.
Early authentication redirects and RBAC exception responses can bypass the inner privacy middleware.
The safe nesting is: `PreventCrmResponseCaching` (outer), `EnsureCrmAuthenticated`, `EnforceCrmRouteAuthorization`.
**Middleware execution is nested:** outer privacy middleware still calls the next middleware; authentication remains before RBAC.

The patch updates `bootstrap/app.php` and corrects *two stale static check expectations* in
`tools/a771_privacy_quick_check.php` and `tools/a773_quick_check.php`.
It does not change controllers, route names, permission grants, queries, storage disks or DB data.

## Install — PowerShell in the Laravel project root

1. Ensure branch `Big-Changes` and review `git status --short`.
2. Unzip `CRM_A777_Final_Security_Regression_READY.zip` to project root (adds **new** files only).
3. `php tools/a777_apply_response_boundary_patch.php --dry-run`
4. `php tools/a777_apply_response_boundary_patch.php --apply`
5. `php artisan optimize:clear` (configuration cache should be absent for test isolation).
6. `php tools/a777_quick_check.php`
7. `powershell -NoProfile -ExecutionPolicy Bypass -File tools/a777_run_security_regression.ps1`
8. `git diff -- bootstrap/app.php tools/a771_privacy_quick_check.php tools/a773_quick_check.php`
9. `git status --short`

For preflight only: `powershell -NoProfile -ExecutionPolicy Bypass -File tools/a777_run_security_regression.ps1 -QuickOnly`.

## Test boundaries

- The test wrapper sets `APP_ENV=testing`, `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `DB_URL=`, `CACHE_STORE=array`, and `SESSION_DRIVER=array` for its child processes, then restores environment variables.
- Existing selected Feature tests use fake principals, mocked permission resolvers, or **disposable SQLite memory connections**.
- Only a reviewed, explicit test class allowlist is run. No `php artisan test` with no filters.
- No migrations, schema resets, seeding, business SQL updates, or role permission grants are performed on PostgreSQL CRM.
- Windows symbolic-link test can show **one environment warning** when symlink creation permissions are absent. This is not a passing symlink test.
- `phpunit.xml` and `bootstrap/cache/config.php` are checked before the test matrix.
- This is a security regression for selected routes and policies, NOT a complete pen-test. Uncatalogued routes, actual production cookies/proxies, Announcement public asset policy, and Reports privacy pilot business approval remain outstanding.

## Acceptance

Require quick source checks + old A7.7.1/A7.7.3 compatibility checks + all selected PHPUnit groups to pass. Investigate any failure instead of weakening the test. Browser/UAT and production configuration reviews occur separately at A7.8–A7.10.
