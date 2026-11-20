# A7.6.5.10 — HOLD Triage, Big-Changes

Target: GitHub repository `UseMoc13/crm-medical-device`, branch `Big-Changes`.

## Copy (no installer)

This ZIP has **`tools/` and `docs/` at its root**. Extract the ZIP DIRECTLY into the Laravel root folder, for example `C:\Users\William\Desktop\crm-medical-device\`. It adds two uniquely named tools and one documentation file, without replacing controllers, Blade, `.env`, migrations, roles or existing report scripts.

Before copying, back up any files of the same names. No command in this package changes the database, flags, or permissions. **The triage tool does execute existing READ-ONLY Artisan scanner commands that query database metadata and business records to compute aggregate counts.** The standalone tests do not connect to the database.

## Run from the CRM root

```powershell
git branch --show-current
php tools/a76510_hold_triage_test.php
php tools/a76510_hold_triage.php
```

Expected from the most recently supplied A7.6.5.9 evidence (not a claim of a new live run): `HOLD_REVIEW_REQUIRED`, entitlement mismatch `2`, parity policy reviews `2`, integration flagged `4`, critical `2`.

To output **sanitized JSON only**:

```powershell
php tools/a76510_hold_triage.php --json
```

Nonzero exit codes on HOLD or incomplete evidence are **deliberate**. Do not interpret as a failing PHP unit suite.

## Existing commands (already in Big-Changes)

```powershell
php artisan crm:reporting:role-entitlements
php artisan crm:reporting:parity-baseline
php artisan crm:integration:inspect
php artisan crm:integration:remediation-review
php artisan crm:integration:regression
```

`remediation-review` and `inspect` use sanitized aggregate output by default. `--details` outputs sensitive internal identifiers/raw statuses and must be kept local (`APP_ENV=local`) and reviewed privately, **not pasted into chat**. All commands above are read-only, but may query business data.

## Next actual work

- Decide reporting and CSV rights for Finance and Management with business owners (no implicit Invoice `all` scope).
- Inspect both Delivery findings against original warehouse allocation, stockout movement and dispatch documentation. Do not auto-backfill missing warehouse or create duplicate stock-outs.
- Examine Installation status, linked Delivery and checklist before approving a normalization.
- Examine unknown Billing status against original invoice and payment business meaning; do not guess statuses.
- Validate manual or separately approved corrective changes on a nonproduction clone; rerun integration regression and reporting browser matrix.
- Preserve `CRM_REPORT_AGGREGATE_PRIVACY_PILOT_ENABLED=false` until approvals and staging verification.

This tool does not create policy permissions, change repository code in place, assert production readiness, normalize transaction records, or bypass A7.6.5.9 gate.
