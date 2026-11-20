# A7.6.5.10 — Reports Privacy & Integration HOLD Review Matrix

## Evidence baseline (20 Nov 2026, user-supplied CLI log)

| Gate | Current reading | Meaning |
| --- | --- | --- |
| A7.6.5.8 static | 50/50 PASS | Structural/source checks pass |
| A7.6.5.8 runtime | 11/11 PASS | Bootstrap/guard baseline passes |
| A7.6.5.9 PHPUnit suites | 7/7 PASS | Targeted tests pass |
| A7.6.5.3 single-role entitlements | REVIEW_REQUIRED, 2 mismatches | Finance/Management Reports rights not equivalent to Invoice KPI rights |
| A7.6.5.2 financial parity | REVIEW_REQUIRED, 2 policy reviews | Same KPI formula != identical allowed row scope |
| A7.5.4.1 integration regression | WATCH, 4 flagged (2 critical) | Historical data findings require investigation |

The above is **not** a current DB scan by this package; the CLI tool obtains fresh results from existing audit commands when run locally.

## Reporting: stakeholder decisions (do not implement unapproved grants)

| Actor | Business decision needed | Safe choices to evaluate |
| --- | --- | --- |
| Finance | Full financial aggregate visibility? Invoice CSV? customer/product names? | restrict; authorize full Invoice view with explicit `all` after approval; or develop scoped reporting with verified row-level filters |
| Management | Needs global dashboard aggregates but no granular invoice data? | restrict; design a dedicated aggregate-only permission and privacy-safe queries; or explicit existing module grants if approved |
| Other/synthetic multi-role users | Does union of roles accidentally expose CSV/details? | test real assignable combinations on isolated staging; never infer from role labels |

**Technical constraint:** The A7.6.5.8 aggregate gate currently requires explicit module View + `all` scope to reveal the associated group when enabled. A new aggregate-only feature would be a separate, reviewed implementation, not an automatic change to permissions.

## Integration: investigate before correction

| Finding rule | Severity in source | Verification |
| --- | --- | --- |
| `delivery.dispatched_item_missing_warehouse` | critical | Confirm origin warehouse from dispatch and serial allocation evidence; unknown warehouse must not be guessed |
| `delivery.stockout_quantity_mismatch` | critical | Compare per-delivery warehouse/product allocated quantity vs. stock-out movement; consider whether NULL warehouse contributes to mismatch |
| `installation.unknown_status` | review | Check case/whitespace versus different business meaning; confirm Delivery, checklist and serial state |
| `billing.unknown_status` | review | Determine historical invoice semantics from transaction/payment evidence; do not guess Draft, Sent or Paid |

**Warning:** Two Delivery flags may reference the same Delivery. Four flagged rules do not prove there are four distinct corrupt business records. No repair, balancing entry, or stock edit is authorized by these findings.

## Review protocol

1. Verify source and test environment, and take a database backup before any eventual remediation.
2. Run `php tools/a76510_hold_triage.php` for sanitized rule IDs and counts.
3. Run `php artisan crm:integration:remediation-review` for read-only remediation recommendations.
4. In a private local environment, examine underlying documents/IDs using existing local-only `--details` commands if authorized; never share raw output.
5. Obtain Finance/Management decisions in writing. Record author, date and approved scope outside this report.
6. Only then implement separately reviewed code/data changes, test with a clone and rerun A7.6.5.9 and UI acceptance matrix.
7. Preserve pilot OFF until reviewed. A clean scanner is not a substitute for browser authorization tests and PostgreSQL parity validation.

Do not interpret `WATCH` as a crash; it is an expected audit status while historical flags remain.
