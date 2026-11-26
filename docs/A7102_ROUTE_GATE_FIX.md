# A7.10.2 hotfix — Laravel 12 route gate middleware false negative

## Diagnosis

`php tools/a710_route_security_gate.php --routes` initially resolves the **Console Kernel**. In Laravel 11/12, `bootstrap/app.php ->withMiddleware()` configures HTTP middleware when the **HTTP Kernel** is resolved. The A7.10 tool inspected `$app['router']->getMiddlewareGroups()['web']` too early and reported three false missing-middleware findings even though the original `bootstrap/app.php` explicitly appends:

- `App\Http\Middleware\PreventCrmResponseCaching`
- `App\Http\Middleware\EnsureCrmAuthenticated`
- `App\Http\Middleware\EnforceCrmRouteAuthorization`

## Scope

Changes **only** `tools/a710_route_security_gate.php` by resolving `Illuminate\Contracts\Http\Kernel` before checking the `web` group. Does **not** change application security middleware, routes, controllers, role grants, DB, session, or historical data. Installs idempotently and saves a timestamped backup under `storage/app/private/a7102_route_gate_backups/`.

## Install

Unzip into the `crm-medical-device` project directory, preserving the project file structure. The archive includes an installer and a readme, not a root replacement for the original route gate.

```powershell
php tools/a7102_apply_fix.php --check
php tools/a7102_apply_fix.php --apply
php tools/a7102_apply_fix.php --check
php tools/a710_source_check.php
powershell -ExecutionPolicy Bypass -File tools/a710_run_checks.ps1 -QuickOnly
```

Expected `A710_ROUTE_SECURITY_CONTRACT_READY` and `A7102_PATCH_ALREADY_INSTALLED`. If middleware actually is absent, the gate still fails. The hotfix fixes preflight initialization, not bypasses the middleware check.

After QuickOnly passes, run the full isolated regression and read-only DB gate:

```powershell
powershell -ExecutionPolicy Bypass -File tools/a710_run_checks.ps1 -WithDatabase
```

This command runs isolated SQLite tests and then local PostgreSQL READ-ONLY checks. No manual UAT or production-release approval is inferred from a PASS. `independent_write_routes=32` remains an explicit separate review item; two unresolved privacy policy HOLD items remain.

## If gate still reports missing guards

Run `php artisan optimize:clear` once, inspect `bootstrap/app.php` without modifying middleware, and send the new full gate output. The guard's absence at that point may be genuine and must not be papered over.
