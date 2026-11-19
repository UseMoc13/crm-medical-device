# A7.6.5.6 — Multi-role & Reports privacy matrix

## Boundary

This phase is a **hypothetical role-grant audit**, not a real-user audit. It never reads `users`, `user_roles`, invoices, customers or amounts. Running the audit does **not** confer or revoke access. The synthetic HTTP regression uses a separate SQLite `:memory:` connection only during PHPUnit, with `APP_ENV=testing` asserted.

## Browser QA to perform on isolated staging (manual)

| Actor | Reports page | Invoice CSV (pilot OFF) | CSV (pilot ON) | Dashboard Invoice KPI |
| --- | --- | --- | --- | --- |
| Reports-only role | Reports visible | Current legacy behavior | 403 expected | Restricted |
| Reports + Invoice View, NULL/own scope | Reports visible | Current legacy behavior | 403 expected | Restricted |
| Reports + Invoice View, scope `all` | Reports visible | Current legacy behavior | Eligible; still subject to Reports row scope | Eligible; subject to same dates |
| Role without Reports base/View | 403 | 403 | 403 | Independent of Reports grants |
| Genuine system Super Admin | Allowed | Allowed | Allowed | Allowed |
| Role named “Super Admin” with `is_system=false` | No bypass | No bypass | No bypass | No bypass |
| Multi-role combination | Verify union with temporary staging account | Verify | Verify | Verify |

Do not create or alter real role grants just to pass this matrix. Use synthetic staging actors and records, with explicit stakeholder approval for any permission changes.

## Privacy surfaces requiring a decision

- Reports top-customers contains customer labels and amounts, even if user has no Customers View grant.
- Reports top-products contains product names and amounts, even if user has no Products View grant.
- Invoice CSV includes invoice number, customer name, sales-order number, dates and money fields.
- Current `CrmReportingDataScope` is opt-in; disabled flags may allow global reporting aggregates and labels when Reports View is granted.
- The CSV strict pilot (OFF by default) enforces Invoice View with scope `all` but does not by itself create per-row customer/warehouse segmentation.
- Enabling any opt-in row scope may alter Reports data population, so numeric parity must be re-verified.

## Independent audit interpretation

`REVIEW_REQUIRED` means the hypothetical combination has Reports access and known privacy surfaces; it is not a test failure. `NO_REPORTS_ACCESS` means no Reports gate passed. `SOURCE_DRIFT` or `SCHEMA_INCOMPLETE` means the audit cannot be trusted until source/schema are checked. `ROLE_NOT_FOUND` means a supplied role name does not match metadata. The audit intentionally does not reveal actual account-role memberships.

## Existing open items

1. Decide Finance versus Management grants and whether customer/product labels should be visible.
2. Decide when and where to enable CSV pilot and read scopes.
3. Use staging with PostgreSQL for actual numeric comparisons, only after stakeholder agreement.
4. Preserve all four existing cross-module historical audit flags; no automatic corrections.
