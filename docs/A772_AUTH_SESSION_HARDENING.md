# A7.7.2 — CRM Login Throttling & Session Regression

**Branch:** `Big-Changes` (Laravel 12, PHP 8.2+, PostgreSQL in the application, SQLite :memory: for isolated tests).

## Purpose

Current `CrmLoginController` already checks account status, rotates session IDs on successful login, invalidates sessions on sign-out and limits failed logins to 5 per normalized email + client IP for 60 seconds.

This stage retains those controls and adds a **second limiter independent of IP**: 20 failed attempts for the same normalized email over 300 seconds. A distributed attempt changing IP will therefore not reset the email-specific ceiling. The second ceiling may temporarily throttle an otherwise legitimate user after enough failed guesses. The IP-based limiter alone is not a reliable security boundary if reverse-proxy headers are misconfigured.

## Ready-to-replace files

1. `app/Http/Controllers/Auth/CrmLoginController.php` — replaces the existing login controller.
2. `app/Services/CrmLoginThrottlePolicy.php` — new pure helper to create hashed limiter keys.
3. `tests/Unit/CrmLoginThrottlePolicyTest.php` — independent policy tests.
4. `tests/Unit/CrmLoginThrottleSourceTest.php` — checks dual hit/clear and session protections.
5. `tests/Feature/CrmLoginThrottleHttpIsolationTest.php` — HTTP tests using a new isolated SQLite `:memory:` connection; these never connect to or seed the CRM database.
6. `tools/a772_auth_quick_check.php`, `tools/a772_policy_smoke.php` and `tools/a772_run_checks.ps1` — static and test runners.

**No migrations, seeders, SQL execution, role changes, session config edits, or automatic deployments.** No installer.

## VS Code PowerShell commands

From project root:

```powershell
git branch --show-current
git diff -- app/Http/Controllers/Auth/CrmLoginController.php
Copy-Item app/Http/Controllers/Auth/CrmLoginController.php "$env:TEMP\crm-login-before-A772.php"
```

If the controller already contains local uncommitted changes, review and merge manually before replacing. Copy the archive contents over the Laravel root. Then:

```powershell
php tools/a772_auth_quick_check.php
php tools/a772_policy_smoke.php
php artisan test --filter=CrmLoginThrottle
powershell -NoProfile -ExecutionPolicy Bypass -File tools/a772_run_checks.ps1
php artisan optimize:clear
git diff -- app/Http/Controllers/Auth/CrmLoginController.php
git status --short
```

The PowerShell runner repeats the static and Laravel tests; it does not alter app configuration or user records. The HTTP tests create a separate SQLite memory-only connection (not `RefreshDatabase` against the CRM). Pure unit tests require no DB at all.

## Expected behavior

- Invalid password increments both counters and returns the existing generic error.
- Valid login after fewer than the limits clears both counters and regenerates the session.
- An inactive account cannot stay authenticated; both counters still increment.
- Reaching either threshold blocks an attempt *before* credentials are checked, even if another IP address is used.
- Logout still invalidates the session and regenerates CSRF token.

## Security caveats / production review

- **Trusted proxy:** `bootstrap/app.php` currently calls `$middleware->trustProxies(at: '*')`. Review the actual Railway/load-balancer topology and trusted proxy headers before production. Do not blindly replace `*` with an unverified address or depend only on spoofable client IPs for rate limits.
- **HTTPS cookies:** confirm on production that `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true` and `SESSION_SAME_SITE=lax` (or another reviewed policy). Never publish the `.env` file or secrets.
- **Cache backing:** The rate limiter needs a shared cache in multi-instance production (e.g. Redis/database cache as configured). An isolated in-memory cache on each worker is not a distributed limit.
- This stage does not guarantee account lockout resistance, proxy correctness, CSRF/browser security, or production SSO. Those require deployment review and browser verification.
- **Avoid live brute-force testing:** Run the included tests (which use array cache and ephemeral SQLite), not repeated password attempts against production.

## Exit criteria

1. Static checks pass.
2. Tests for policy, source and isolated HTTP flows pass (no skipped HTTP tests due to missing SQLite driver).
3. Login/log-out smoke test in local browser completed.
4. Production proxy/cookie decisions are documented before final release.

## Final testing database later (A7.9)

Do **not reset or seed now**. When modules and the security regression are stable, create a fresh backup and test the restore on a disposable database. Prepare PostgreSQL seed SQL based on the final schema, with role and user fixtures, customers/leads, products/warehouses/serials/inventory, sales pipeline, deliveries/installation/warranty, service/maintenance, invoices and reporting parity; seed explicit known statuses, stock-out/inventory consistency and deterministic scenarios for both valid and invalid workflows. No live credentials or customer data in seed SQL.
