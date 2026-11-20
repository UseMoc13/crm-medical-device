# A7.7.1 — CRM Web Response Cache Privacy (Big-Changes)

## Why

CRM pages, authentication redirects, and invoice CSV downloads contain or lead to private information. This milestone prevents **normal responses from Laravel's web middleware group** from being stored in browser/shared caches, while preserving current login, role permissions and business workflows.

## Files (no installer)

- `bootstrap/app.php` — **ready-replace** version from `Big-Changes` as inspected 2026-11-20. One middleware registration addition. Review your local diff before copying.
- `app/Http/Middleware/PreventCrmResponseCaching.php` — new middleware.
- `tests/Unit/CrmResponseCachePrivacyTest.php` — pure HTTP response tests, no DB.
- `tools/a771_privacy_quick_check.php` — static + PHP syntax checks, no DB.

## Windows PowerShell in VS Code

1. `git branch --show-current` (expect `Big-Changes`)
2. `git diff -- bootstrap/app.php` (inspect local changes; **do not overwrite if already customized**)
3. Make a manual copy of the current `bootstrap/app.php` outside the repository.
4. Copy the contents of the package (`bootstrap/`, `app/`, `tests/`, `tools/`, `docs/`) into the repository root. Overwrite `bootstrap/app.php` only after comparing it.
5. `php tools/a771_privacy_quick_check.php`
6. `php artisan test --filter=CrmResponseCachePrivacyTest`
7. `php artisan optimize:clear`
8. `git diff -- bootstrap/app.php`
9. `git status --short`

PowerShell optional manual header check (requires running `php artisan serve`):

```powershell
$res = Invoke-WebRequest -Uri 'http://127.0.0.1:8000/login' -MaximumRedirection 0 -SkipHttpErrorCheck
$res.Headers['Cache-Control']
```

**Note:** `-SkipHttpErrorCheck` is a PowerShell 7 parameter; Windows PowerShell 5.1 does not support it. In Windows PowerShell 5.1, use:

```powershell
(Invoke-WebRequest -Uri 'http://127.0.0.1:8000/login' -UseBasicParsing).Headers['Cache-Control']
```

## Boundaries

- No database changes, migrations, role changes, or privacy pilot activation.
- Does not add or change CSRF, CORS, CSP, or security permissions.
- Server/static assets served outside Laravel web middleware remain subject to web server cache policy.
- Exception responses generated outside the web middleware chain may need a separate exception response audit; this milestone does not assert every error response is covered.
- Setting no-store for all web responses can disable browser caching for publicly accessible pages delivered through the web group; verify performance on staging.
- `bootstrap/app.php` retains the pre-existing `trustProxies(at: '*')`; evaluate trusted-proxy boundaries separately for deployment.
- Historical A7.5.8 Installation, Invoice, Delivery findings remain blocked on documentary evidence and manual approval.
