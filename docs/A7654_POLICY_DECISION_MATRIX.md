# A7.6.5.4 — CSV Least-Privilege Pilot Decision

This is not a mandate to enable the flag. Finance, Management and Super Admin roles require business approval of which information should be visible and downloadable. Existing Reports page contains financial totals and customer details; restricting CSV alone does not protect every reported customer detail.

| Role | Report page existing | CSV default OFF | CSV pilot ON | KPI Invoice |
|---|---|---|---|---|
| Finance (current metadata) | YES | YES | 403 | restricted |
| Management (current metadata) | YES | YES | 403 | restricted |
| Super Admin | YES | YES | YES | ALL |

If Finance requires CSV, consider explicit Invoice View + `all` only after approval; otherwise design a real filtered export based on verified row-level scopes. Management may need aggregate-only reporting rather than detailed CSV. Do not automatically grant more permissions or normalize historical `unpaid` records.

`crm:reporting:role-entitlements` remains **single role metadata** and cannot prove access of multi-role users. `crm:reporting:parity-baseline` remains `REVIEW_REQUIRED` until real numeric and data-scope parity is verified on PostgreSQL staging.
