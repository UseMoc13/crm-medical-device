# A7.5.8 — Installation and Billing evidence review

## Purpose

A7.5.7 Delivery remains `TECHNICAL_MATCH_PENDING_HUMAN_PROOF`: do not use its execution mode without original warehouse dispatch documentation and independent approval. A7.5.8 progresses other independent findings without a premature historical data repair.

This package is a **case-scoped, local-only, PostgreSQL transaction READ ONLY** investigation tool. It doesn't enable a pilot, create warranties, update serials, normalize invoice statuses, grant access, or manipulate reporting.

## Grounding in Big-Changes source

- InstallationController requires delivered Delivery, completed checklist and `assigned` serials to perform an authorized **new** transition to `Completed`, changing their status to `installed` atomically. A legacy row already stored as lower-case `completed` has **not** been proven to have gone through that transition.
- `CrmHistoricalRemediationReview` requires all delivered serials installed before proposing even a format-only status candidate. In the provided evidence, serial remains `assigned`.
- InvoiceController accepts only `Draft`, `Sent`, `Paid`, `Cancelled` and explicitly rejects legacy `unpaid` transitions. No state can be inferred solely from invoice dates or from zero audit events.

## Run in VS Code PowerShell (no installer)

Copy `tools/` and `docs/` folders to the Laravel project root. Branch `Big-Changes`, `APP_ENV=local`, Laravel vendor dependencies and PostgreSQL are required.

```powershell
php -l tools/a758_incident_review.php
php tools/a758_case_rules_test.php
php tools/a758_incident_review.php --installation=6360bdab-1075-5d73-b323-03c0853956f6 --invoice=432cae83-4f2e-5a62-b8c8-b964dc6f2f1b
```

Optional wrapper:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File tools/a758_incident_review.ps1 -Installation "6360bdab-1075-5d73-b323-03c0853956f6" -Invoice "432cae83-4f2e-5a62-b8c8-b964dc6f2f1b"
```

## How to interpret

| Decision | Next required human action |
| --- | --- |
| `HOLD_INSTALLATION_LIFECYCLE_REVIEW` | Verify signed device handover, serial chain-of-custody and linked warranties before deciding a separate audited correction |
| `FORMAT_ONLY_CANDIDATE_PENDING_ORIGINAL_HANDOVER_AND_APPROVAL` | Technically eligible only; document and approver still required |
| `HOLD_ORIGINAL_ISSUANCE_AND_PAYMENT_PROOF` | Finance must verify issuance, payment and original state meaning before deciding canonical status |
| `CANONICAL_STATUS_NO_CORRECTION_PROPOSED` | No correction needed based on status alone |

The tool prints no IDs, serial strings, invoice amounts, names, audit details or raw statuses. It checks warranty row count only, which **does not establish warranty validity**. Audit event count zero **does not prove an event never occurred**; legacy data may predate audit instrumentation.

## Release blockers and recommended sequence

1. Retain A7.5.7 on HOLD until true dispatch proof and independent approval are obtained.
2. For Installation: reconcile serial `assigned` versus legacy status `completed` and signed customer handover. Never change `assigned` to `installed` simply to pass a report. Verify warranty implications before correction.
3. For Invoice: original invoice, payment receipts, historical ledger/accounting data and Finance approval determine whether the record was Draft, Sent, Paid, or other; never assume unpaid is Sent.
4. After separately approved remediation, rerun `php artisan crm:integration:regression` and role/reporting gates. A7.6 permission mismatch still requires business approval, not auto-grants.

No write mode is included in A7.5.8. Backup for this package is unnecessary because it adds only new files. The developer should still retain standard Git backups for repository changes.
