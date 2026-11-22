# CRM A7.9.2 — Synthetic dataset candidate (offline only)

## Source and status

Based on A7.9.1 snapshot `33ebc4b1ef1ecff52be3ed9cde67c2e01c69902a7886f21e362270c85ba9b801`, 65 tables, 623 columns, 150 foreign keys. The candidate has 2,668 explicit fixture rows plus seven grants selected from the existing permission catalog = 2,675 candidate rows across 44 tables. It has not been executed in PostgreSQL. Offline validation is not proof of application business rules or live database constraints.

Run from repository root, without starting Laravel:

```powershell
php tools/a792_quick_check.php
```

If snapshot moved, use `--snapshot=PATH`. Keep original snapshot local (do not commit schema metadata on a public repository). `database/uat/a792_fixture_rows.json` is the static synthetic fixture companion; the manifest enumerates every table and identifies deferred/preserved tables. Checksums detect unintended file changes, not adversarial tampering or independent proof that the live schema matches the snapshot.

## Candidate scenarios

- 30 synthetic customers, contacts and qualified leads; 410 opportunities/quotations and 405 confirmed sales orders with one product line each. The first 25 orders share a timestamp; the remaining 380 share another. UAT-Q0406 is approved without an order, Q0407–0408 sent, Q0409–0410 draft. Order 0201 contains harmless CSV formula-shaped text `=1+1` for CSV protection tests.
- Two branches, two warehouses, six products, 12 inventory rows. 12 opening-stock ledger entries and four historical dispatch stock-out entries reconcile to inventory. UAT codes use a distinct namespace; actual sequence objects/default expressions must be inspected before import. No attempt to seed/restart product/customer sequences.
- UAT-D001/D002 delivered with completed installations; D001 has an active warranty, D002 is a warranty-activation candidate. D003 delivered with a scheduled installation; D004 in transit; D005 pending with one available allocated serial; D006 pending without allocation. Status strings use the inspected workflow controller's values (`available`, `assigned`, `installed`); SerialNumber CRUD has a different capitalized status catalogue, which must be reviewed in UI UAT. Historical original customer assignment and transition-specific eligibility still need verification; do not treat inserted history as evidence of a successful HTTP workflow.
- Two service tickets (open/in_progress), one existing ticket part (no stock deduction), two preventive maintenance schedules (completed/scheduled) and one maintenance record.
- Four invoices (Paid/Sent/Draft/Cancelled), with synthetic amounts matching order items; Sent invoice due on 2026-11-19. Dates are fixed to November 2026 for repeatability, and due/scheduled flags will depend on the date of UAT.
- Four announcement status examples, no fake attachment paths. Create actual harmless files through UI in disposable UAT storage for contract downloads, workspace submission, and announcement media. The announcement visibility and reporting privacy HOLDs are not resolved by fixtures.
- Basic workspace fixtures: activity, calendar event, task/assignment, notes with different owners, collaboration, note mention, reporting line and preferences. Team membership, task review, notification delivery, audit events and file upload are deferred to UI or further fixtures. No claim that all workspace workflows are covered.

## RBAC safety and setup

Five new non-system role candidates: Catalog Viewer, Reports Only, Finance Reader, Sales Owner, No Access. Eight synthetic users use `@example.invalid`, one inactive, one multi-role (Reports Only + Finance Reader). The SQL contains bcrypt hashes of an undisclosed random password; no known default password is supplied. A protected Super Admin is not seeded or modified; use the existing approved local bootstrap if needed, then provision credentials privately via the authorized admin UI on the UAT instance. Sales Owner currently has only the module grant: it is not a fully configured sales role. Review exact page/workflow scopes and add approved role assignments via UI before UAT. Seven candidate role grants use existing exact permission names, and the SQL aborts if any are missing or ambiguous. Do not synthesize the permission catalog or bypass protected roles.

## Import safety: not yet approved

`database/uat/a792_rehearsal_only.sql` is a transaction rehearsal, not an installer or commit script. DO NOT execute it in the current CRM database. It checks for an explicitly named disposable database (`crm_medical_device_uat`), requires targeted business tables empty, and finishes with ROLLBACK. Existing roles/users/branches and preferences may exist, but fixture keys must be unused; permission catalog must exist. No auto upsert or destructive reset. Use only after A7.9.3 prepares an isolated database with reviewed schema, sequence state, initial permissions, backup and restored-schema verification. A loopback connection alone is not proof of isolation. A separate database name alone is also not proof of isolation. Stop workers/other writes and use detached test storage; run only on a confirmed local test instance. The next stage will supply exact backup/restore and rehearsal commands; do not execute yet.

The A7.9.1 inventory does not include actual column-default expressions or standalone index definitions (including partial unique indexes), so obtain a PostgreSQL schema-only dump and verify permissions, constraints, index behavior, sequences, and actual schema fingerprint in the dedicated database before loading. Check business rules and file access via real workflows. A7.9.4 validates data after a separately approved committed import. A7.9.5 executes actual UAT and updates the register. The two prior policy HOLDs remain unresolved.

No migration rows, queue jobs, cache, audit history, or fabricated file references are inserted. See `a792_dataset_manifest.json` for all 65 table dispositions.
