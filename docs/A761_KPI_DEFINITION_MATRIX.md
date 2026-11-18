# A7.6.1 — KPI definition, permission and source readiness

This is a source assessment, **not** a calculation of real CRM totals and **not** a certification of row-level access.

| KPI | Source | Period | Canonical rule | View permission |
| --- | --- | --- | --- | --- |
| Total Customers | customers | Snapshot | Count viewer-visible customer rows | customers.view |
| Active Leads | leads | Snapshot | Exclude closed/converted/lost; define case/NULL policy | leads.view |
| Opportunities | opportunities | Snapshot | Count all viewer-visible opportunities; do not call it active | opportunities.view |
| Service Tickets | service_tickets | Snapshot | Count viewer-visible tickets | service_tickets.view |
| In Transit | deliveries | Snapshot | Only `in_transit` | deliveries.view |
| Installations in Progress | installations | Snapshot | Only `In Progress`, legacy casing flagged | installations.view |
| Below Minimum Stock | inventories | Snapshot | quantity < minimum_stock by product + warehouse | inventories.view |
| Invoiced | invoices | Invoice date period | Sent + Paid | invoices.view |
| Paid | invoices | Invoice date period | Paid only; **not by payment date** | invoices.view |
| Outstanding | invoices | Invoice date period | Sent only | invoices.view |
| Overdue | invoices | Invoice date period + today | Sent with due_date < today | invoices.view |
| Warranty expiring | warranties | As-of-today snapshot | Active expiring within 30 days | warranties.view |

## Source review findings to resolve before displaying expanded KPIs

1. The existing DashboardController has global `countTable()` reads that do not apply the role/row scopes used elsewhere. KPI queries must only run and render when the current user has view permission; use existing scoped services where applicable.
2. Dashboard recent activities are fetched from the `activities` table without owner/collaborator filtering and the view shows title and description. Protect both data acquisition and rendering; do not merely hide the card.
3. Dashboard Sales Overview is currently a placeholder, while Reports already implements financial, monthly, product, customer, service, maintenance, pipeline, and warranty blocks. Reuse the financial definition and scoped query service rather than duplicating SQL or assuming all users can see financial data.
4. Dashboard view has a fallback that invokes `Announcement::publishDueScheduled()` during page rendering. Review this write side effect before calling dashboard GET paths read-only. Keep scheduled publication in its intended schedule/command path.
5. Check the exact Git filename: `Reportcontroller.php` currently differs from its `ReportController` class. Linux file systems can reject mismatched-case PSR-4 autoload. Do not rename automatically; validate it in a separate controlled change.
6. Reports' `Sent + Paid` invoice definition intentionally **excludes** historical `unpaid`. Do not silently classify the legacy status as Paid, Sent or Draft.
7. Report month labels for Paid use **invoice_date**, because the current model lacks a payment date. Label this clearly to prevent treating the graph as cash-flow data.

## Gate sequence

A7.6.1: source prerequisites and design review → A7.6.2: permission-scoped KPI query service → A7.6.3: dashboard UI and report parity → A7.6.4: HTTP privacy tests and performance.

Manual browser regression and PostgreSQL audit rollback verification from A7.5 remain independent release requirements. Do not use this command to clear those gates.
