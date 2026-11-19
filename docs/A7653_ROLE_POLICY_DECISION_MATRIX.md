# A7.6.5.3 — Approval Checklist for Finance / Management

The tool outputs **single-role** hypothetical decisions based on the currently configured permission rows. No permission changes occur. This document is for selecting a business-approved policy before data-scope enforcement.

| Audience | Reports summary | CSV invoice-level detail | Dashboard Invoice KPI | Proposed rule |
|---|---|---|---|---|
| Management (aggregate-only) | Allowed with reports.view + reporting.access | Require separate export entitlement | Only with explicit Invoice grant + supported data scope | Never infer Invoice `all` from Reports grant |
| Finance (invoice operations) | Allowed with Reports grants | Require explicit Invoice viewing entitlement and auditable scope | Only explicit Invoice View + `all` under present A7.6.2 service | Do not weaken downstream Sales/Customer opt-in scopes |
| Sales staff (limited scope) | Denied without Reports grants | Denied without additional authorization | Denied for Invoice KPI until a tested row-level policy exists | Fail closed rather than global fallback |
| Super Admin | System bypass per resolver | System bypass | System bypass | Business invariants and audits still apply |

Existing behavior: Reports page and invoice CSV share one Reports access guard; the opt-in downstream Sales filter may be disabled. Hence this table is *a proposed target*, not a description of already enforced separation. Select a documented policy before modifying Reports controller or CSV output.

**Acceptance tests for a future enforcement patch:** report-only role sees permitted aggregate data but cannot export unauthorized invoice rows; Finance with explicit `all` can compare same-date Dashboard and Reports amounts; users with restricted or null Invoice scope cannot see global numbers; Customer/Opportunity scope ON must not leak out-of-scope invoices; legacy invoice `unpaid` must remain excluded from issued/paid/outstanding/overdue; no business data or grants rewritten.
