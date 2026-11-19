# A7.6.5.7 — Policy decision and browser QA

Before enabling the pilot, obtain a written decision from the CRM owner and Finance/Management for: (a) whether aggregate global invoiced/paid/overdue numbers may be shown to Reports-only roles, (b) whether pipeline/service/warranty global summaries may be shared, (c) whether Finance and Management need *named* top customers/products, and (d) who may export detailed invoice CSV. The default OFF installation must not be mistaken for an approved access policy.

## Browser matrix (staging with synthetic data)

| Viewer | Detail flag | Expected Top Customers/Products | CSV UI | Direct `/reports/export/invoices` |
|---|---|---|---|---|
| Finance, Reports-only | OFF | Current legacy list | Current legacy | Legacy access if CSV flag OFF |
| Management, Reports-only | OFF | Current legacy list | Current legacy | Legacy access if CSV flag OFF |
| Finance, Reports-only | ON | Restricted text; **no named query executed** | Restricted, no link | 403 |
| Management, Reports-only | ON | Restricted text; **no named query executed** | Restricted, no link | 403 |
| Reports + Invoice View + `all` | ON | Named top lists | Link available | Allowed, normal CSV conditions |
| Reports + Invoice `own` only | ON | Restricted text | Restricted | 403 |
| Reports + Invoice null scope | ON | Restricted text | Restricted | 403 |
| System Super Admin | ON | Named lists | Link available | Allowed |
| No Reports grant | either | 403 page | Not applicable | 403 |

Use same date range, synthetic records and separate browser sessions. Confirm HTML source has no customer/product names for denied viewers, not merely hidden CSS. Inspect query logs in staging (no invoice/customer data printed) if needed to verify Top lists are not queried when denied. Check `CRM_REPORT_CSV_INVOICE_ALL_SCOPE_ENABLED` independently; CSV-only ON does **not** redact named HTML sections. Confirm no new writes in GET requests.

Retained reviews: Reports and Dashboard Invoice entitlement mismatch; optional downstream row-scope vs explicit KPI scope; four A7.5 historical data anomalies. Never auto-correct historical statuses or stock.
