# A7.5.6 Historical Provenance Preflight — `Big-Changes`

This is a **read-only** tool. No installers, file replacements, migrations, permission changes, stock movements, invoice status updates, or automatic repairs. Use it only with a database you are authorized to inspect. Confirm that the local `.env` points at your intended database; `APP_ENV=local` does not itself guarantee a non-production database connection.

## Copy files

Copy the contents of `tools/` into the project's `tools/` folder and this document to `docs/`. It is compatible with PHP 8.2, Laravel and Windows PowerShell (including the VS Code integrated terminal). No existing controller needs replacement.

## Commands (PowerShell in VS Code)

```powershell
php tools/a756_evidence_test.php
php -l tools/a756_historical_evidence.php
php tools/a756_historical_evidence.php
# Optional equivalent wrapper:
powershell -NoProfile -ExecutionPolicy Bypass -File tools/a756_historical_evidence.ps1
```

## How to read output

- `MATCHES_IF_PROVENANCE_APPROVED`: **in-memory simulated** replacement of the missing warehouse would reconcile allocated quantities and stock-out quantities by warehouse/product. This is not independent proof that the warehouse really dispatched the item.
- `CANDIDATE_NEEDS_ORIGINAL_DISPATCH_PROOF_AND_APPROVAL`: product/ordered quantity and warehouse master metadata also look consistent, but actual source documents and approval are still mandatory.
- `HOLD_...`: insufficient evidence or a conflicting condition. Do not normalize historical data.
- `INSTALLATION HOLD_PRECONDITIONS`: a case-only status difference is not enough. A real `Completed` installation workflow also needs a delivered shipment, completed checklist, and all delivery serials `installed`.
- `BILLING HOLD_BILLING_MEANING`: `unpaid` is outside the current `Draft`, `Sent`, `Paid`, `Cancelled` vocabulary. It is not safe to infer the correct status without actual invoice/payment evidence.

The script does not print UUIDs, raw serial numbers, names, notes, or amounts. It is not a security attestation or an approved remediation. All findings remain open until deliberately reviewed, corrected (if appropriate), and re-audited.

## Manual decision record (keep locally; do not include sensitive values in Git)

| Finding | What to verify | Approved action |
|---|---|---|
| Delivery allocation `warehouse_id` missing | Original warehouse dispatch evidence, matching serial/product/quantity, existing stock-out and audit trail | Record approved provenance, then review a separate narrow data correction. Never duplicate stock-out. |
| Delivery quantity mismatch | Re-evaluate after delivery provenance is independently confirmed | Do **not** change stock quantities just to make the audit pass. |
| Installation `completed` | Original handover, checklist, device statuses, associated warranty/service history | Decide whether operational workflow repair precedes any formatting correction. |
| Invoice `unpaid` | Original invoice issue status and actual payment/collection history | Owner assigns canonical status based on evidence; never infer automatically. |

After approval, implement a separate **one-time, audited, preconditioned** correction script with a specific row ID, expected old values, exact approved new values, and a dry-run/rollback strategy. That correction is intentionally **not** in this ZIP.
