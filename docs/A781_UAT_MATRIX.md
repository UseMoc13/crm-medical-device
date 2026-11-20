# CRM A7.8 UAT Matrix — Manual Scenarios (Not executed)

Only execute after approved synthetic dataset on a non-production environment (A7.9). Record the role used, expected result, actual result, screenshot/HTTP status, and defect ID. These are scenarios, **not claims that workflows passed**.

| ID | Modules | Scenario | Expected behavior |
|---|---|---|---|
| UAT-01 | Auth/RBAC | Guest requests protected customer, report, and CSV pages | Redirect/401; private no-store; no confidential data |
| UAT-02 | RBAC | Read-only role sends direct POST/PATCH to protected CRM workflows | 403 and no business mutation |
| UAT-03 | Customer/Lead | Create customer, contact, lead, opportunity with valid references | Related records exist, validation rejects foreign/invalid IDs |
| UAT-04 | Sales | Opportunity -> Quotation -> approve -> Sales Order | Valid transition creates linked order; no duplicate conversion |
| UAT-05 | Catalog/Stock | Product + warehouse + inventory + movement | Quantity and ledger reconcile; underflow/invalid correction denied |
| UAT-06 | Delivery | Confirm -> transit -> delivered, verify items | Only permitted transition, correct HTTP method PATCH and audit/stock postconditions |
| UAT-07 | Installation | Delivery -> install, checklists, complete | Installation references delivery; invalid status changes blocked |
| UAT-08 | Warranty | Activate warranty from eligible installation | Serial numbers/customer relationships valid, no duplicate activation |
| UAT-09 | Service | Service ticket -> notes/parts -> resolve | Scoped permission and audit trail, no unintended stock deduction |
| UAT-10 | Maintenance | Schedule -> record -> complete | Linked history and valid time/status checks |
| UAT-11 | Billing | Sales Order -> invoice -> payment status -> report CSV | Invoice totals align; CSV sanitizes formula, Reports privacy scopes reviewed |
| UAT-12 | Files | Contract/private submission download by allowed/denied actor | 200 for authorized, deny cross-scope, private cache headers |
| UAT-13 | Announcement | Draft/scheduled/published/archived assets | **HOLD** pending visibility/public storage policy; do not assume URL access revoked |
| UAT-14 | Multi-role | Scoped viewer opens dashboard/report/downloads | No cross-user disclosure; explicit privacy pilots remain OFF unless approved |
| UAT-15 | Pagination | 25+ same-timestamp orders and 405 CSV rows | No duplicate/omission; original order stable; limited query count |

**Approval gate:** UAT-13/public attachments and prior financial privacy/historical holds must be resolved or explicitly accepted by stakeholders before any production sign-off. Re-run relevant test suites after schema/data reset in A7.9.
