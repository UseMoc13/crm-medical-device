# A7.6.5.1 — Reporting Authorization QA (staging only)

| Scenario | Reporting base | Reports View | Expected page | Expected CSV |
|---|---|---|---|---|
| Unauthorized user | no | no | 403 | 403 |
| Base only | yes | no | 403 | 403 |
| View only | no | yes | 403 | 403 |
| Report-authorized user | yes | yes | Authorized* | Authorized* |
| Guest | n/a | n/a | Redirect to login | Redirect to login |

*Authorized means access is allowed by the new gate; normal date validation, database preconditions and existing data-scope filters still apply. **Do not infer row-level scope parity**. Test with known dummy finance and non-finance users, and verify exported rows are appropriate for their intended roles.

## Consistency issues reserved for A7.6.5.2

1. Dashboard `CrmScopedKpiQuery` requires explicit per-page data-scope grants, whereas existing `CrmReportingDataScope` downstream filters are opt-in. Source-level formula parity does not guarantee the same accessible population.
2. `Reports` permission is a separate page grant from `Invoices` permission. Management and Finance roles may intentionally have different entitlements. Verify business-approved rules for report-only users before restricting aggregates further.
3. Test the same date range (`invoice_date`, inclusive) and `as-of` day for both views; `Paid` is attributed to **invoice date**, not payment date.
4. Legacy `unpaid` and `Draft` are excluded from issued-invoice sums. Historical statuses are not normalized.
5. Check CSV export with a malicious formula-like customer or invoice value on a staging database before asserting spreadsheet safety. CSV content is unchanged by A7.6.5.1.
