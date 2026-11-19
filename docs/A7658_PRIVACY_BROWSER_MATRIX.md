# A7.6.5.8 — Aggregate Privacy Browser Acceptance Matrix

## Required environments

- Use test/staging identities only; never expose invoice amounts, customer names, personal data, or complete CSV content in shared logs.
- Review the actual assigned `user_roles` of each test account separately; the earlier multi-role preview is hypothetical and not evidence of real memberships.
- Verify both cases of the `CRM_REPORT_AGGREGATE_PRIVACY_PILOT_ENABLED` flag and the existing A7.6.5.4/A7.6.5.7 flags independently.
- For PostgreSQL numeric-parity checks, use the gated A7.6.5.5 staging command with the required strict staging criteria; do not relabel production as staging.

## Test cases

| Test | Expected when aggregate pilot ON |
|---|---|
| Reports-only user (Management-style) | GET `/reports` allowed, four aggregate groups show “restricted” and do not query business tables; CSV direct URL 403 |
| Finance with reports access but missing Invoice `all` | Financial KPI, chart, invoice status, Top Customers/Products hidden and not queried; CSV 403 |
| Invoice View `own` or `branch` | Financial group restricted; no fallback to global invoice total |
| Invoice View `all` plus Reports View | Financial group available; named detail still respects A7.6.5.7 detail pilot |
| Service tickets `all`, Maintenance missing | Service **and** Maintenance groups restricted, since they share one combined overview section |
| Service tickets `all`, Maintenance `all` | Service/Maintenance permitted; other unrelated groups remain restricted unless separately granted |
| Opportunities View `all` | Pipeline group visible; without explicit `all`, restricted |
| Warranties View `all` | Warranty group visible; without explicit `all`, restricted |
| Super Admin (actual `is_system=true`, correct system name) | All groups visible; do not assume any lookalike role bypasses permission checks |
| Multi-role union | Each grant evaluated via resolver; no implicit `all` if no role supplies it; test actual accounts manually |
| Pilot OFF | Current A7.6.5.7 behavior and existing flags preserved |
| Direct CSV URL with pilot ON and insufficient scope | 403, even if link is manually typed or earlier CSV flags remain OFF |

## Verification requirements

- HTTP status and rendered content, not just sidebar visibility.
- Inspect query logging locally with synthetic data: **zero** business queries for denied groups; permitted groups use existing scoped Reports query adapter.
- Confirm 4 financial totals use same selected date period and no legacy `unpaid` inclusion.
- Verify modal/toast/sidebar/UI unaffected outside Reports.
- Verify dashboard role isolation, announcement scheduler, and regression gates remain unchanged.
- Historical delivery, installation, billing anomalies remain WATCH until manually reviewed.

## Decision still needed

Management/Finance policy must explicitly decide whether they require: (a) no aggregate report numbers, (b) approved organization-wide aggregates without named customer/product details, or (c) full Invoice/other module access. This pilot intentionally **does not introduce a new aggregate-only permission**. It makes current module View + `all` a conservative opt-in requirement. Do not switch it on in production without selecting a policy and verifying every report group.
