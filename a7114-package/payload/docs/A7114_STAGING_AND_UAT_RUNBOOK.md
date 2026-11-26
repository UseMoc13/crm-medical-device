# A7.11.4 — Disposable staging gate and browser UAT execution

**Status: prepared, not executed.** Designed after A7.11.3 (320 Laravel tests passing; sidebar source checker 7/7). This package **does not connect to PostgreSQL**, perform migrations/seeds, invoke UAT HTTP requests, or change existing code. The `--init` mode creates an all-false private checklist; it does not authorize anything.

## 1. Non-negotiable preparation

1. Preserve the known-good branch, Git diff and backups. Do not commit `.env`, ZIPs, logs, `storage/app/private` or installer backup folders.
2. Create and configure a **separate PostgreSQL database with explicitly disposable synthetic data** using your normal administrative processes. If it cannot be identified as disposable, stop. Never reset or seed the existing CRM database.
3. Inspect the **actual PostgreSQL cluster, port, host, database name and connection target**, independently from the application `.env`. `127.0.0.1` may be a tunnel to a remote database. Confirm it is not shared/production.
4. Verify a usable backup and restoration procedure, not merely the existence of a `.backup` file. Use no private customer data in UAT.
5. Confirm permitted operator and test role accounts. UAT-13 announcement direct-asset policy and UAT-14 report/privacy policies are HOLD until explicitly decided by an authorized stakeholder.

## 2. Gate commands (no PostgreSQL connection)

Run from CRM project root:

```powershell
php tools/a7114_staging_gate.php --check
php tools/a7114_staging_gate.php --init
# Review and manually fill only after independently verifying each condition:
notepad storage/app/private/a7114_uat/staging_attestation.json
php tools/a7114_staging_gate.php --validate
```

`--check` inspects `.env` only and prints boolean checks, not the database name, credentials or connection string. It requires `APP_ENV=local`, `DB_CONNECTION=pgsql`, explicit loopback host, blank DB_URL, a database name with a distinct `uat/test/testing/sandbox/disposable` segment, and not a PostgreSQL default DB. Names and local hosts alone are **not sufficient evidence**. The `--validate` outcome is **operator-attested readiness only** and does not verify the server connection or backup independently. Do not use it as an authorization token for destructive tools.

If a check is HOLD, **do not force it by relabeling a live database**. Reconfigure a genuine disposable UAT database safely. The application may also use injected environment variables that override `.env`; independently check the **effective** runtime config before any write UAT.

## 3. Recommended execution order

Read `docs/A7112_MANUAL_UAT_PLAYBOOK.md` and the original `docs/A781_UAT_MATRIX.md`. Start with non-mutating UAT-01 (guest) and UAT-02 (read-only role). Then run UAT-03/04 (customer → sale), 05/06 (inventory → delivery), 07/08 (installation → warranty), 09/10 (service/maintenance), 11/12 (billing/files), 15 (pagination); UAT-13/14 stay on HOLD pending decisions.

Per scenario verify permission-denied direct URL/POST/PATCH, valid status sequence, relevant rows before vs after, expected audit entry, correct form method/CSRF, toast/error messages, and no duplicate writes. The scenario checklist CSV is a convenience index, **not evidence**.

## 4. Evidence and validation

Keep screenshots, redacted HTTP status details, identifiers of synthetic records only and notes under a **private** path. Do not expose real names, email addresses, medical data, authentication secrets, raw URLs to private files, or SQL dumps.

```powershell
# Existing A7.11.2 generator; do not replace a ledger that already contains work
php tools/a7112_init_ledger.php
php tools/a784_validate_uat_ledger.php --ledger=storage/app/private/a7112_uat/YOUR_LEDGER.json
# Post-UAT code regression
powershell -NoProfile -ExecutionPolicy Bypass -File tools/a7112_run_regression.ps1 -Run -FullSuite
php tools/a7113_sidebar_check.php
```

Do not mark a case PASS until the actual browser/HTTP scenario has been executed and evidence reviewed. With 15/15 PASS, validator still only indicates readiness for **external sign-off**, not permission to release production.

## 5. Stop conditions

Stop UAT writes if the database identity/backup is uncertain, customer/PHI data is present, permission roles are invalid, a stock transaction is inconsistent, a private URL discloses data, or an audit log is missing on a critical status change. Record `BLOCKED` or `FAIL`, do not silently weaken role enforcement.
