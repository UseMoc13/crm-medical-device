# A7.9.4 — Synthetic dataset import (local prototype only)

Preconditions: A7932 fresh baseline verified on the newly recreated `crm_medical_device` database; full backup restored successfully elsewhere; operator confirms this is the disposable local prototype (not a tunnel or shared/production environment). Keep the legacy database and backup. Stop Laravel server, queue workers, scheduler, and other writers. Do not add Super Admin until import verification passes. Never use these scripts against a production DB.

No application changes. Original A792 SQL INSERT payload preserved exactly (2,668 explicit rows plus 7 catalog grants). All 65 tables checked, 63 initially empty; exact 32 migration and 117 permission names checked, and schema object counts checked. Write scripts lock all 65 tables in SHARE ROW EXCLUSIVE mode within one transaction, abort on lock timeout or nonempty table. No delete/truncate/drop/update, no sequence restart, no replacement of accounts or existing grants. A local address/name is not proof of safe target: verify server identity yourself. PG18, psql 18; no database execution performed by package author.

PowerShell (root repository):

```powershell
$pg = 'C:\Program Files\PostgreSQL\18\bin'
$conn = @('-X','-v','ON_ERROR_STOP=1','-h','127.0.0.1','-p','5432','-U','postgres','-d','crm_medical_device')
& "$pg\psql.exe" @conn -f database/uat/a794_rehearsal.sql
if ($LASTEXITCODE -ne 0) { throw 'Rehearsal failed. Do not import.' }
```

Expected: `A794_REHEARSAL_PASSED_ROLLED_BACK`, exit 0. Then optionally rerun `tools/a7932_verify_fresh.sql` (still empty). Only after successful rehearsal and explicit decision to import, execute separately:

```powershell
& "$pg\psql.exe" @conn -f database/uat/a794_commit.sql
if ($LASTEXITCODE -ne 0) { throw 'Import did not report success. Inspect state before retry.' }
& "$pg\psql.exe" @conn -f tools/a794_verify_import.sql
if ($LASTEXITCODE -ne 0) { throw 'Post-import validation failed. Stop before bootstrap.' }
```

Expected: `A794_DATASET_COMMITTED` then `A794_IMPORTED_DATASET_VALIDATED`, both exit 0. Verify immediately before bootstrap, app writes, session/cache/queue operations. The exact count check is intended only for initial import and will intentionally fail after business data changes or bootstrap. If connection is lost near commit, verify state first; never blindly repeat commit. SQL inserts do not exercise HTTP controllers or audit workflows.

After verification: ensure Laravel uses `APP_ENV=local`, correct local DB and `SESSION_DRIVER=file` with no overriding DB_URL, then `php artisan crm:super-admin:bootstrap` (no --env). Use a privately chosen email/password, no preset login. Fixture users contain bcrypt hashes of a discarded random password, so set distinct temporary passwords through authorized admin interface for UAT; user08 remains inactive. Sales Owner has only module access; configure scoped page permissions through UI before owner-scope UAT. Existing policy HOLDs on announcement assets and reporting privacy remain. Use actual harmless test files for file workflows; avoid reusing legacy storage in UI testing. No real file paths or fabricated audit logs inserted.

Fixture counts: 44 populated tables, 2675 rows added, 149 preserved catalogue/ledger rows, total 2824 before bootstrap. SQL checks exact counts, inventory balance, order/quotation/invoice parity, primary roles, all seven specific grants. Data timestamps are fixed for Nov 2026; no claim of current scheduling outcomes. Serial workflow lower-case status and CRUD display differences are pending UI UAT. See A792 dataset design for limitations.
