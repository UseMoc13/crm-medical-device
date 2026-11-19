# A7.6.4 Browser regression (staging / test database)

| Scenario | Expected | Status |
| --- | --- | --- |
| Dashboard loads with authorized user | KPI cards and Sales Overview render with existing permissions | NOT RUN |
| Dashboard loads with restricted role | Unauthorized KPI not shown; recent activity respects access | NOT RUN |
| Dashboard with no announcements | Empty slider state, no exception | NOT RUN |
| Dashboard with published announcements | Pinned/order, thumbnails, slider interactions unchanged | NOT RUN |
| Dashboard GET with due-scheduled announcement | GET does not change its `status`/`published_at` | NOT RUN |
| Explicit `crm:announcements:publish-due` on staging | Due announcements publish; future/draft stay unchanged | NOT RUN |
| Scheduler configured + executed | Due item appears on next Dashboard read | NOT RUN |
| Reports page and Export CSV on Linux/staging | Controller autoloads without case error | NOT RUN |
| Audit KPI baseline | 12/12 sources, 0 source reviews expected | NOT RUN |
| Integration regression | Same historical WATCH findings, no new query errors | NOT RUN |

Do not execute the publication command on production without explicit approval. Source/in-memory tests are not replacements for browser testing.
