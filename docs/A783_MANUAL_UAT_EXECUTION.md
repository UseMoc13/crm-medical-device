# A7.8.3 — Manual UAT Execution Register (NOT EXECUTED)

Continue using `docs/A781_UAT_MATRIX.md` for the 15 scenarios and `docs/A782_UAT_EVIDENCE_TEMPLATE.md` to record screenshots, role, fixture, expected/actual HTTP response and data state. This file is for planning, not a claimed PASS.

| ID | Primary manual test priority | Starting disposition |
|---|---|---|
| UAT-01 | Guest redirects / 401 and no-store | NOT RUN |
| UAT-02 | Direct unauthorized POST/PATCH 403 with zero writes | NOT RUN |
| UAT-03 | Create customer, contact, lead, opportunity; wrong FK rejected | NOT RUN |
| UAT-04 | Approved quotation -> one Sales Order, repeat conversion rejected | NOT RUN |
| UAT-05 | Stock movement ledger reconciles, invalid correction fails | NOT RUN |
| UAT-06 | Delivery state workflow PATCH with stock/audit postconditions | NOT RUN |
| UAT-07 | Installation checklist and completion match delivery | NOT RUN |
| UAT-08 | Warranty activated only for valid serial/installation | NOT RUN |
| UAT-09 | Service ticket worklogs and parts with scoped permissions | NOT RUN |
| UAT-10 | Maintenance record and status lifecycle | NOT RUN |
| UAT-11 | Invoice and Reports parity + CSV privacy / safety | NOT RUN |
| UAT-12 | Private file allowed vs cross-user denial | NOT RUN |
| UAT-13 | Attachment public URL access for draft/scheduled/expired content | **HOLD: policy unresolved** |
| UAT-14 | Multi-role Dashboard/Reports visibility and pilot approval | **HOLD: policy unresolved** |
| UAT-15 | 25-row pagination and 405-row CSV integration | NOT RUN |

## Data prerequisites

The current A7.8.2 report has all 7 main linked chains. One Announcements table is empty. A previous A7.7.4.3 audit reported `announcement_media` has 0 references; the precise empty table is **not proven by the A7.8.2 aggregate output**. Verify on disposable staging if an example media attachment is required. Do not create new Announcement media on the current database merely to make 34/34 green.

## Manual run guard

1. Confirm staging database is disposable and NOT production; record backup/snapshot identifier.
2. Use synthetic test identities/roles and fixture records only.
3. Record each expected and actual HTTP method/state; reject status transition without postcondition.
4. Stop and mark BLOCKED if roles, visibility policy, fixture, stock ledger, or prior release holds are unresolved.
5. Re-run scope, privacy, finance parity and workflow tests after A7.9 dataset installation.
