# A7.8 — Manual UAT evidence template (not executed)

**Precondition:** disposable staging database with approved A7.9 synthetic dataset and verified test roles; rollback or DB snapshot available. Do not run destructive scenarios against existing business data.

| UAT ID (see A781_UAT_MATRIX.md) | Role | Fixture reference | Expected HTTP/state | Actual HTTP/state | Evidence screenshot/log | PASS/FAIL/BLOCKED | Defect |
|---|---|---|---|---|---|---|---|
| UAT-01 | Guest | none | redirect or JSON 401, no-store | Pending | Pending | NOT RUN | — |
| UAT-02 | Read-only | synthetic | 403, no mutation | Pending | Pending | NOT RUN | — |
| UAT-04 | Sales | synthetic | approved quotation creates one linked Sales Order | Pending | Pending | NOT RUN | — |
| UAT-06 | Delivery | synthetic | PATCH status workflow and stock/audit postconditions | Pending | Pending | NOT RUN | — |
| UAT-08 | Authorized | synthetic | warranty activation links valid serial | Pending | Pending | NOT RUN | — |
| UAT-11 | Finance | synthetic | invoice totals and CSV scope agree | Pending | Pending | NOT RUN | — |
| UAT-13 | Admin/public | synthetic | direct file URL policy approved and enforced | Pending | Pending | HOLD | — |

Use **A781_UAT_MATRIX.md** for all 15 scenarios. Never mark an unexecuted scenario as PASS based only on schema, route, or data-presence checks.
