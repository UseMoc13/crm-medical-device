# A7.6.5.2 — Financial KPI parity review

| Item | Dashboard A7.6.2 | Reports controller | Conclusion |
|---|---|---|---|
| Invoiced | Sent + Paid by invoice_date | Sent + Paid by invoice_date | Formula aligned by source contract |
| Paid | Paid by invoice_date | Paid by invoice_date | Formula aligned by source contract; **not** payment-date cash |
| Outstanding | Sent by invoice_date | Sent by invoice_date | Formula aligned by source contract |
| Overdue | Sent and due_date before as-of | Sent and due_date before today | Aligned only at same as-of date |
| Period | default year-to-date; service accepts from/to | user-selectable inclusive from/to | Compare identical periods only |
| Invoice authorization | Billing + Invoice View + explicit invoice data scope | Reporting + Reports View | **Different role entitlement** |
| Invoice visibility | Current KPI requires invoice scope `all` | Invoice report queries use opt-in downstream sales filters | **Not proven equivalent** |
| CSV export | Not applicable | same Reports permission + downstream filter | Requires role and row-level review |

## Recommended policy decision before any permission-changing patch

Decide whether a report-only Management user is authorized to see all financial aggregates and customer names, and whether Finance with `own` or `branch` scope should see no totals, partial totals, or global totals. **Do not infer an `all` grant from `reports.view`.** If global reporting is intended, grant it explicitly through a reviewed capability with corresponding export authorization. If scoped reporting is intended, implement an auditable invoice/report row policy shared by KPI, report, and CSV queries. Avoid auto-normalization of `unpaid` and changes to historical invoices.

## Test boundaries

This audit reads only PHP source and configuration switches. It neither evaluates SQL on production data nor proves end-to-end browser or security behavior. Two tests use synthetic PHP data and a read-only command. Source checks are intentionally conservative; unsupported source changes return SOURCE_DRIFT. Run role-based end-to-end comparisons on staging later.
