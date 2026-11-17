# A7.4.9 — Review/Approval/Revision Test Matrix

No manual database reset. Use throwaway test users and records in the live CRM as appropriate; full page/module evaluation and synthetic data seeding are postponed to the final project phase.

| ID | Actor | Action | Expected |
|---|---|---|---|
| 01 | Creator | Create Work TO-DO, 1 mandatory Assignee | Creation succeeds |
| 02 | Assignee | Upload required evidence | Saved on private local disk |
| 03 | Assignee | Submit assignment | `completed`, review `pending_review` |
| 04 | Creator | Complete entire task while review pending | Blocked with status validation |
| 05 | Viewer | POST approval directly | 403, no audit row |
| 06 | Contributor | POST revision request | 403, no audit row |
| 07 | Assignee | POST approval directly | 403, no audit row |
| 08 | Creator | Request revision with note shorter than 10 chars | Validation error, no state change |
| 09 | Creator | Request revision with meaningful note | Returns to `assigned`, review `revision_requested` |
| 10 | Assignee | Check previously uploaded files | Still accessible to this assignee; not lost |
| 11 | Assignee | Resubmit assignment | Returns to `completed` / `pending_review` |
| 12 | Creator | Approve submission | Review `approved`; reviewer and timestamp recorded |
| 13 | Creator | Attempt duplicate approval | 422, no duplicate history |
| 14 | Creator | Complete entire task after mandatory approval | Succeeds |
| 15 | Creator | Attempt review after task completed/cancelled | 422, no state change |
| 16 | Guest/uninvited | POST direct reviewer URL | Not permitted |
| 17 | Creator | Review assignment UUID belonging to other task | 404 |
| 18 | Viewer/Contributor | Download other assignee's evidence | 404, no file leak |
| 19 | Existing task | Has previously completed assignment | Remains `legacy_accepted` and closable |
| 20 | Creator | Remove assignment with review history | Validation blocks destructive audit loss |
| 21 | Creator | Existing Activity and Collaboration pages | Unchanged layout, toast, permissions |
| 22 | Assignee | Mobile To-Do detail | Responsive review card follows existing styling |

`php artisan test --filter=WorkspaceTaskReviewHttpTest` + `php artisan test --filter=WorkspaceCollaborationHttpTest` provide automated isolated checks. For security, browser work may be deferred until final evaluation, as requested.
