# A7.6.2 — KPI access and definition contract

| Metric family | Permission | Row scope | Query meaning |
| --- | --- | --- | --- |
| Customers | customer_lead.access + customers.view | own / branch / all | Count visible current customers |
| Leads | customer_lead.access + leads.view | own / branch / all | Status not NULL nor closed/converted/lost, trimmed case-insensitively |
| Opportunities | sales_crm.access + opportunities.view | own / branch / all | Total visible opportunities, not active-only |
| Service Tickets | service.access + service_tickets.view | assigned / branch / all | Total current visible tickets |
| Installations | installation.access + installations.view | assigned / branch / all | Exact `In Progress` |
| Deliveries | delivery.access + deliveries.view | all only | Exact `in_transit` |
| Inventory | inventory.access + inventories.view | all only | `quantity < minimum_stock`, NULL minimum excluded |
| Invoice values | billing.access + invoices.view | all only | Invoice_date period: `Sent + Paid`, `Paid`, `Sent`, `Sent` overdue as of today |
| Warranties | service.access + warranties.view | all only | `active` expiring in 30 days as of today |

Other scopes (including `team`) fail closed; missing scope does not imply `all`. `branch` with no branch_id does not grant rows. Modules without an audited `user_id` owner/assignee **never guess** owner relationships. This is intentionally stricter than existing page visibility and preserves the ability to add proper downstream scope joins in a later stage.

`CrmScopedKpiQuery::run($from, $to, $asOf)` requires an authenticated web user. Defaults to Jan 1 through today. Output contains `status=visible` and `value` only for authorized KPIs, else `restricted` or `unavailable_schema` without a value. Financial values use decimal strings, not floats. The service makes no writes. It is NOT exposed via a route or CLI containing business numbers.

**Unresolved source reviews:** dashboard global counts, Activity preview, Sales Overview placeholder, `Reportcontroller.php` Linux casing, and announcement scheduled-publish side effect. A7.6.3 must address these before treating Dashboard as role-safe. Five A7.6.1 warnings should remain visible in the baseline until fixed.
