# A7.6.3 — Dashboard browser regression checklist

**Environment:** staging / test accounts only. No need to edit financial records. Record observed vs expected and test account role for each case.

| ID | User condition | Expected | Result |
| --- | --- | --- | --- |
| D01 | Super Admin with access to all relevant modules | Four KPI cards and Sales Overview display authorized counts/amounts | NOT RUN |
| D02 | Staff with Customers View + own scope | Customer KPI reflects only own customers | NOT RUN |
| D03 | Staff without Leads View | Active Leads displays `—`, not global total or `0` | NOT RUN |
| D04 | Staff with Billing View but without `all` scope | No invoice amount in Sales Overview | NOT RUN |
| D05 | Finance user with Billing View + all scope | Sent/Paid/Outstanding/Overdue use invoice-date period, never count legacy `unpaid` | NOT RUN |
| D06 | User with Activity View, owns some Activity rows | Only owned activity text appears | NOT RUN |
| D07 | User with Activity View, explicitly collaborated on another Activity | Collaborated Activity visible, unrelated Activity hidden | NOT RUN |
| D08 | User without Activity View | No recent Activity free text shown | NOT RUN |
| D09 | Guest (if Dashboard route accessible) | No personalized KPI or Activity data, authentication required | NOT RUN |
| D10 | Dashboard announcements | Slider images, controls, navigation unchanged | NOT RUN |
| D11 | Very large invoiced values | Decimal display correct with no scientific notation | NOT RUN |
| D12 | Mobile narrow screen | Sales Overview cards wrap into one column | NOT RUN |

**Known separate reviews:** `Reportcontroller.php` capitalisation on Linux and scheduled publication inside `dashboard/index.blade.php` remain outside this patch. The existing view still calls `Announcement::publishDueScheduled()` if the fallback is used; a future patch should move publication to an explicit scheduler/command after testing.

**Scope:** This release only adjusts Dashboard presentation. It is not a substitute for HTTP multi-user testing, and the A7.5.4.1 historical integration audit remains `WATCH` until separate reconciliation.
