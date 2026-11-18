# A7.5.4.2 — Browser & Transaction QA Matrix

**Use a non-production/staging environment with disposable records and two authorized test accounts.** Do not test status transitions on the CRM's real business records. Do not reset, truncate or reseed the existing CRM database. The A7.5.4.2 installer and `a7542_http_preflight.php` are read-only with respect to the database.

## Before starting

1. Save a backup of the staging database, verify the staging `.env`/database connection, and open the application in two separate sessions (two browsers or a private window).
2. Prepare **Account A** (makes the status change) and **Account B** (directly associated creator or assignee). Give only the permissions necessary for the module being tested. B must have **View** permission AND record-level access. B must be `active`.
3. In Account B, open **Notifications > Notification Settings** and enable **CRM status updates**. Leave the same option disabled for Account A or another unrelated Account C to verify opt-out.
4. Prepare NEW test Delivery, Installation and Invoice records with valid relationships; do not reuse the four flagged historical records. For Delivery dispatch, prepare an actual serial, warehouse inventory, allocation and Sales Order in staging. Do not manufacture a stock-out movement by hand.
5. Run `php artisan crm:integration:regression` and record the baseline status and counts. The current known baseline is WATCH, 33 OK / 4 findings, but future counts may change as records are added.

## Browser matrix (mark PASS/FAIL/NOT RUN)

| ID | Scenario | Expected result | Result |
|---|---|---|---|
| B01 | Account B enabled CRM status updates; Account A performs permitted **Delivery** `pending -> in_transit` on a clean test delivery | Exactly one applicable status transition; inventory decremented once and stock-out recorded once; audit event `delivery.status_changed`; eligible B receives a generic notification | NOT RUN |
| B02 | Account A marks the same Delivery `in_transit -> delivered` | Guard checks warehouse + product + quantity; completed status and date saved; audit event; no second stock-out | NOT RUN |
| B03 | Attempt `delivered -> cancelled` or repeat finalized transition | Rejected; no new stock-out, status event or notification | NOT RUN |
| B04 | Using a **separate disposable test record**, attempt to complete an `in_transit` Delivery with a missing warehouse allocation or bad stock-out match | Rejected before `delivered`; no extra stock movement or audit event | NOT RUN |
| B05 | Account A performs `Scheduled -> In Progress` on a new Installation, where B is creator or assigned technician | Status and audit event recorded; B receives notification only when allowed; no customer data in notification | NOT RUN |
| B06 | Attempt `In Progress -> Completed` while installation checklist remains incomplete | Rejected; no `Completed` event or notification | NOT RUN |
| B07 | Complete installation legitimately (Delivery `delivered`, checklist complete, serials correct) | Completion accepted; serial state updated by existing workflow; audit + notification if B is eligible | NOT RUN |
| B08 | Account A updates Invoice `Draft -> Sent` on staging | Status changes; `invoice.status_changed` audit; B receives generic notification if allowed/opted in | NOT RUN |
| B09 | Attempt an invalid Invoice transition (such as `Paid -> Sent`) | Rejected; no audit event or notification | NOT RUN |
| B10 | Test Invoice edit/delete after status becomes non-editable | Request denied based on locked current row; no item changes or deletion | NOT RUN |
| B11 | Account C without View permission opens a saved CRM notification URL/target directly | Access denied; notification does not confer permission | NOT RUN |
| B12 | Revoke B's record/module access after a notification is created, then press **Open** | Target is not opened; user returns to inbox with a non-sensitive error | NOT RUN |
| B13 | B disables CRM status updates and Account A performs another legitimate change on a **new** record | No new CRM status notification to B; existing notifications remain | NOT RUN |
| B14 | An unrelated active user C, who is neither creator nor direct assignee, has module View permission | C does not receive a CRM status notification for an unrelated record | NOT RUN |
| B15 | Re-submit a rejected status request or invalid method/CSRF token | No status change and no new audit/notification; Laravel authorization/method rules enforced | NOT RUN |
| B16 | Refresh bell poll and Notifications inbox with accounts A and B | Poll/unread counts are per-user; no target UUID, customer name, invoice amount or free-text notes are exposed in notification preview | NOT RUN |

## Read-only evidence collection (optional, staging only)

Record a test record UUID locally (do **not** paste it in a shared report). The following queries are read-only; replace UUIDs only in the local terminal. Do not run SQL `UPDATE`/`DELETE` against a CRM test or production record to make a scenario pass.

```powershell
php artisan crm:integration:regression
php artisan crm:integration:audit
```

When a fresh test transaction should have produced an audit entry, inspect via your authorized **Audit Logs** page with `subject_type` and the record ID, rather than bypassing its access controls.

To review delivery stock-outs, use the existing Delivery Details page, which presents the allocated serials and movements. Compare warehouse and product pairs and ensure exactly one stock-out per dispatch event.

## Gating criteria

- A7.5.4.1 gate remains **WATCH** if ONLY known historical findings persist, with zero query errors, zero scanner disagreement and zero missing prerequisites. Changes to the counts require review.
- A7.5.4.2 automated **isolated memory** test must pass: audit and inbox writes commit together and roll back together; a recipient with CRM notifications disabled receives nothing.
- Any **FAIL** in B01–B16 blocks sign-off. **NOT RUN** is not PASS.
- One skipped legacy `CrmCrossModuleStatusAuditTransactionTest` is still an **unverified** migrated-PostgreSQL rollback scenario. Do not call this verified until rerun on a deliberately isolated, migrated PostgreSQL test database.
- The historical Delivery (null warehouse), Installation (`completed` but serial prerequisites missing) and Invoice (`unpaid`) records must not be edited by these QA checks.

## Suggested test report template

```
Stage: A7.5.4.2
Environment: staging / local test (NOT production)
Git branch / commit: <branch> / <short hash>
Static quick check: PASS / FAIL
Read-only preflight: PASS / FAIL
Isolated rollback test: PASS / FAIL / SKIPPED
Regression gate: READY / WATCH / INCOMPLETE / BLOCKED
Browser matrix: N PASS / N FAIL / N NOT RUN
Outstanding skip (migrated PostgreSQL audit): YES / NO
Known historical findings: 4 (or current count)
Notes: describe only generic failures; remove UUIDs, personal data and secrets
```
