# A7.6.5.5 — Manual Staging Parity / Authorization Matrix

This protocol is **not** executed by the ZIP. Use a separate PostgreSQL staging environment with synthetic data.

## SQL formulas

The command compares 4 metrics by `invoice_date`, inclusive: `invoiced = Sent + Paid`; `paid = Paid`; `outstanding = Sent`; `overdue = Sent whose due_date < as_of`.

CSV's row projection contains Draft and Cancelled too; its 4 KPI-like sums in this audit intentionally filter to Sent/Paid. A CSV exported under normal Reports permissions can still contain other statuses. The command compares the join shape and metrics **without actually downloading CSV**.

## Required steps for UI validation

1. Seed synthetic staging records only; include Sent, Paid, Draft, Cancelled and legacy `unpaid`, an overdue Sent, a future-due Sent, a row outside the period and a missing optional Sales Order join.
2. Use matching date range and as-of date. Dashboard has no user-facing date filter, so the default dashboard interval should match Reports default (Jan 1 to today); if comparing a historical period, use `CrmScopedKpiQuery::run()` with explicit dates under a test viewer.
3. Super Admin: verify Reports totals and Dashboard Invoice KPI present the same numeric values with downstream Sales scope OFF; verify CSV rows and totals according to the CSV status policy. Do not perform this on production data.
4. Finance (Reports view with **no Invoice scope** per A7.6.5.3): verify Reports is accessible but Dashboard Invoice KPIs are restricted, not global. With CSV flag OFF, its legacy CSV access remains. With flag ON on staging only, CSV returns 403.
5. Management: same checks as Finance; confirm the decision to allow aggregate Reports data is intentional and separately approved.
6. User without Reports permission: both `/reports` and CSV endpoint return 403; direct URL is not a bypass.
7. A user with `crm_page.invoices.view` `data_scope=own/branch/null`: no invoice KPI or strict CSV access; no silent promotion to all.
8. Multi-role users: test actual union of role grants with a separate session; A7.6.5.3 was single-role metadata only.
9. Test Reports customer/product tables, ticket and maintenance sections: invoice-only parity does **not** cover private detail disclosures outside Invoice KPI. Review whether separate permission guards or enforced data scopes are necessary before production policy changes.
10. Keep A7.5 four historical integration flags under manual remediation review. No data repair is part of this work.

## Interpreting results

- `SOURCE_READY`: KPI source tables present; not a permission proof.
- `REVIEW_REQUIRED`: entitlement / row-scope differences still open.
- `UNSCOPED_FORMULA_PARITY`: unscoped SQL formula and LEFT JOIN sums match for the selected staging period **only**.
- `NUMERIC_MISMATCH`: independent queries disagree; investigate source/joins, never auto-fix rows.
- `BLOCKED`: safety prerequisites not met; no invoice data read.

Do not treat successful fixture tests or an unscoped parity result as approval to switch the CSV pilot feature flag ON in production.
