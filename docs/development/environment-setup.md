# CRM Medical Device - Environment Setup (two developers)

## Principles
- GitHub tracks source code, `.env.example`, and this guide.
- Each developer has a private `.env` with their own database credentials and APP_KEY.
- Existing `.env` is **never** overwritten by a `git pull` or by this A6.12 package.
- `.env.example` uses PostgreSQL and disables seven scoped pilots by default.
- `.env.example` is a template, not a live configuration switch. Changing it does not alter either developer's already existing `.env`.

## First install
Run from the project root in PowerShell:

```powershell
composer install
npm install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
# Edit .env for local PostgreSQL connection; create the database separately.
php artisan key:generate    # ONLY for a new .env whose APP_KEY is empty
php artisan optimize:clear
php artisan migrate:status
```

If DB and migration history match expectations, apply only missing migrations with `php artisan migrate`. Do not use `migrate:fresh` against shared or working data. `npm run dev` or `npm run build` is needed for frontend assets depending on workflow.

## Existing install after a teammate commits A6.12
```powershell
git pull origin main
# Inspect .env.example and copy new CRM_DATA_SCOPE_* keys into your own .env if needed.
php artisan optimize:clear
php artisan crm:rbac:audit-data-scope --db
```

## Scope settings (local development only)

```dotenv
CRM_DATA_SCOPE_CUSTOMERS_ENABLED=false
CRM_DATA_SCOPE_LEADS_ENABLED=false
CRM_DATA_SCOPE_OPPORTUNITIES_ENABLED=false
CRM_DATA_SCOPE_SERVICE_TICKETS_ENABLED=false
CRM_DATA_SCOPE_MAINTENANCE_ENABLED=false
CRM_DATA_SCOPE_INSTALLATIONS_ENABLED=false
CRM_DATA_SCOPE_ACTIVITIES_ENABLED=false
```

Never assume a NULL `role_permissions.data_scope` means `all`. At the last audit, eleven grants for enabled View subjects still had NULL scope. Configure appropriate grants in the Roles UI first. The Activities flag stays OFF without an integrated ActivityController, and Installations stays OFF until end-to-end tests.

## Tests and database safety
`phpunit.xml` in the supplied repository snapshot sets `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` and `SESSION_DRIVER=array`. Shell overrides can be affected by PHPUnit configuration, so verify the **effective** connection before any data-changing tests. The A6.11.7.3 runtime authorization test is postponed. Use a separate populated test DB; do not point it to the main local CRM database or production.

## Railway / other deployment
Use platform environment Variables, not `.env.example`. Check active flags, database URLs and migration state separately for each deployment. Keep scope pilots disabled in production until runtime authorization is tested.

## Git and sensitive files
- Commit `.env.example` and docs; never commit `.env`, secret keys, or real DB credentials.
- `.gitignore` should include `.env` and private environment variants.
- After changes to `.env`, run `php artisan optimize:clear`.
- If local `APP_KEY` already exists, keep it; running `key:generate` again may invalidate encrypted state.
