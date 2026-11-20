# A7.5.7 — Controlled Historical Delivery Warehouse Reconciliation

## Current known finding (20 November 2026)

A7.5.6 found one **delivered** delivery with one `delivery_items.warehouse_id = NULL` and one matching-looking `stock_movements` stock-out of quantity 1. In-memory reconciliation would match if that source warehouse were proved. This **does not** authenticate the original warehouse provenance. One `assigned` serial is also attached to a historical Installation recorded as lowercase `completed`. Invoice `unpaid` has no status-change audit event. This package **only** targets the Delivery Item's warehouse column, and never touches Installation or Invoice.

## Installation (NO INSTALLER)

Extract this ZIP into the root of `crm-medical-device` on branch `Big-Changes`. New files are only under `tools/` and `docs/`. The dependency `tools/a756_evidence_rules.php` must already be present from A7.5.6; it is **not overwritten**.

From the VS Code integrated **PowerShell** terminal:

```powershell
php -l tools/a757_delivery_warehouse.php
php -l tools/a757_delivery_rules.php
php tools/a757_rules_test.php
```

**Read-only preview:** replace UUID placeholders with values already shown in the local A7.5.2.1 log.

```powershell
php tools/a757_delivery_warehouse.php --delivery=DELIVERY_UUID --item=DELIVERY_ITEM_UUID --warehouse=WAREHOUSE_UUID
```

Or use the Windows PowerShell wrapper (no policy setting or installer needed):

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File tools/a757_delivery_warehouse.ps1 -Delivery DELIVERY_UUID -Item DELIVERY_ITEM_UUID -Warehouse WAREHOUSE_UUID
```

Expected when the dataset still matches: `Decision: TECHNICAL_MATCH_PENDING_HUMAN_PROOF`. This is **not authorization to modify the database**, just a technical eligibility finding. Neither command needs `--execute`.

## Required human/business evidence before ANY approved write

1. Original delivery note / picking slip / warehouse dispatch record independently confirms the proposed source warehouse **and** the specific serial/product/Delivery Item.
2. Reviewer responsible for warehouse/operations signs off and a **different** authorized person independently verifies the source document. An observed stock-out alone is not proof.
3. Record the document reference, evidence location, reviewer, independent verifier, approval time and exact targeted three UUIDs. Store these **outside Git and outside shared logs**. Do not create fake approval metadata merely to satisfy a tool.
4. Make a PostgreSQL backup and validate that the local connection is a disposable clone or explicitly authorized local environment. The script will refuse `APP_ENV != local`, non-PostgreSQL and a non-loopback DB host **for write mode**. This cannot replace confirming the database identity and approval scope.
5. Run the read-only preview again and confirm the source has not changed. Other teams' inventory/serial records are not part of this correction.

## Optional approved local correction (NOT for current unapproved state)

Only after steps above, create a real JSON file outside the repository and populate it from authentic, signed operational evidence:

```json
{
  "decision": "approve_single_delivery_item_warehouse_correction",
  "delivery_id": "<DELIVERY_UUID>",
  "delivery_item_id": "<DELIVERY_ITEM_UUID>",
  "warehouse_id": "<WAREHOUSE_UUID>",
  "original_dispatch_document_reference": "<ACTUAL_DISPATCH_DOCUMENT_REFERENCE>",
  "original_dispatch_document_verified": true,
  "warehouse_provenance_independently_verified": true,
  "approved_by": "<AUTHORIZED_REVIEWER>",
  "independently_verified_by": "<SECOND_AUTHORIZED_VERIFIER>",
  "approved_at": "<ISO_8601_TIMESTAMP>"
}
```

The tool validates JSON structure, exact ID match, recency and distinct reviewer names; **it cannot verify authentic signatures or the truth of the evidence**. Do not use this feature if original provenance is unconfirmed.

For approved, **local-only** correction:

```powershell
php tools/a757_delivery_warehouse.php --delivery=DELIVERY_UUID --item=DELIVERY_ITEM_UUID --warehouse=WAREHOUSE_UUID --execute --approval-file="C:\secure\real-approval.json" --confirm=APPLY_APPROVED_WAREHOUSE_CORRECTION
```

PostgreSQL serializable transaction, row-level locking and compare-and-swap are used. Only one field `delivery_items.warehouse_id` is updated, and complete stock-out reconciliation is checked again before commit. No inventory increment/decrement, no inserted stock-out, no serial status, no installation status and no invoice status change. A sensitive pre-change snapshot is written to the local system temporary directory (`crm_a757_provenance`); store it securely with approval and PostgreSQL backup. It is **not** a DB audit event and does not independently satisfy a production audit policy. There is no automatic rollback tool: if authorized correction needs reversal, review the snapshot and perform a separately approved guarded correction with DB administrators.

After any approved change:

```powershell
php artisan crm:integration:regression
php artisan crm:integration:inspect
php artisan crm:integration:remediation-review
```

Targeted Delivery flags may disappear, but Installation and Billing will remain pending. Do not change the A7.6.5.8 reporting pilot flag or role permissions as part of this change. Never assume the A7.5.7 preview itself means the rollout gate is READY.

## Safety/compatibility

- PHP 8.2+; Laravel 12; PostgreSQL; Windows 11 PowerShell 5.1 or 7.
- No installer and no changes to `app`, `config`, `resources`, `routes`, `migrations`, `.env` or database during ZIP extraction or preview.
- When running preview, server-side `SET TRANSACTION READ ONLY` is set. PostgreSQL may still take normal read locks; this is not a strict zero-read or zero-lock procedure.
- Source may be newer than the GitHub snapshot; no application source is replaced. If `tools/a756_evidence_rules.php` is absent, copy it from the earlier A7.5.6 package.
- PHP pure tests do not prove integration with your actual database. Windows PowerShell execution cannot be verified in this build environment; use the direct `php` CLI as primary path.
