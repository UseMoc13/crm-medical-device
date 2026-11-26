# A7.10.8 — Manual UAT matrix for page changes, exit flows, CRUD, permissions

**Status: NOT YET EXECUTED on the user's computer.** This document is a test plan, not an assertion that all screens are error-free.

Set up **synthetic disposable records**, four browser sessions (Super Admin; CRM view-only; CRM CRUD without Delete; workflow-specific); keep developer console visible. For every module test **Index → Show → Edit → Cancel/Back → Index**, then **Create → Save → Show** and any module-specific safe transitions. Verify *both* the visibility of buttons and the HTTP authorization by attempting the direct URL with an unauthorized test account. A user with no grant should see 403, not 500. Never change operational customer/payment/stock data for UAT.

## Core navigation matrix

| # | Area / route family | Paths / behaviors to check | Permission & state edge cases | Result |
|---|---|---|---|---|
| 01 | Dashboard `/` | sidebar → Dashboard → other module → back | scoped KPIs; no private data leak | ☐ |
| 02 | Roles `roles.*` | index/create/edit/show; template → save → back | Super Admin 200, ordinary user 403 | ☐ |
| 03 | Users `users.*` | list/create/edit/show; return links | admin only; user assignment | ☐ |
| 04 | Branches `branches.*` | list/create/edit/show/delete; filters/reset | admin only; branch dependencies | ☐ |
| 05 | Organization `organization.*` | hierarchy; update role parent / manager | admin only; no cycles | ☐ |
| 06 | Teams `teams.*` | list/create/edit/show; member assignments | admin only; team changes | ☐ |
| 07 | Customers `customers.*` | index/search/create/show/edit/delete/back | view-only + no-delete controls | ☐ |
| 08 | Contacts `contacts.*` | index/create/show/edit/delete/back | customer association | ☐ |
| 09 | Leads `leads.*` | index/create/show/edit/delete/back | ownership/scope | ☐ |
| 10 | Activities `activities.*` | list/create/edit/show; shared/collaboration | invitation/member ACL | ☐ |
| 11 | Opportunities `opportunities.*` | index/create/edit/show; item create/edit/delete | nested item forms + parent routes | ☐ |
| 12 | Quotations `quotations.*` | list/new/edit/show; change status | workflow / POST status methods | ☐ |
| 13 | Sales Orders `sales-orders.*` | list/show/download/export; source quotation | GET download and permission | ☐ |
| 14 | Contracts `contracts.*` | list/new/edit/show/download | file access; back | ☐ |
| 15 | Products `products.*` | list/new/edit/show/delete | catalog `manage` and CRUD | ☐ |
| 16 | Product Categories `product-categories.*` | list/new/edit/show/delete | catalog CRUD guards | ☐ |
| 17 | Brands `brands.*` | list/new/edit/show/delete | catalog CRUD guards | ☐ |
| 18 | Warehouses `warehouses.*` | list/new/edit/show/delete | inventory dependencies | ☐ |
| 19 | Inventories `inventories.index` | stock listing/filter/navigation | read-only stock state | ☐ |
| 20 | Stock Movements `stock-movements.*` | list/new/show; correction create/store | **do not test legacy edit as supported** | ☐ |
| 21 | Serial Numbers `serial-numbers.*` | index/create/show/edit/delete | inventory tracing | ☐ |
| 22 | Deliveries `deliveries.*` | list/new/edit/show; allocate/remove units; status confirm/back | view-only dialog; PATCH, stock postconditions | ☐ |
| 23 | Installations `installations.*` | list/new/edit/show; start/complete/cancel; checklist/save | status permission independent of checklist; PATCH | ☐ |
| 24 | Warranties `warranties.*` | list/new/edit/show/delete; activation | device/customer integrity | ☐ |
| 25 | Service Tickets `service-tickets.*` | list/new/edit/show; parts CRUD/status/work log | workflow permissions; status + PATCH | ☐ |
| 26 | Maintenance `maintenance.*` | list/new/edit/show; record create/edit/delete; status dialog | view-only missing form; PATCH status | ☐ |
| 27 | Service History `service-history.*` | list/detail/back | read only; device history | ☐ |
| 28 | Invoices `invoices.*` | list/new/edit/show; status; print | view-only, Billing Clerk no status | ☐ |
| 29 | Reports `reports.*` | dashboard/export | aggregate privacy, CSV authorization | ☐ |
| 30 | Announcements `announcements.*` | list/detail | visibility and scheduled posts | ☐ |
| 31 | Announcement Management `announcement-management.*` | new/edit/show/delete/toggle-pin | write role, permissions | ☐ |
| 32 | Notes `notes.*` | new/edit/show/list/back, mentions | visibility/personal privacy | ☐ |
| 33 | To-Do `todos.*` | new/edit/show/review/back | owner + assigned user access | ☐ |
| 34 | Calendar `calendar.*` | list/create/edit/show/back | timeline/assigned task consistency | ☐ |
| 35 | Workspace Notifications | inbox/prefs/deep link/back | ownership, unread behavior | ☐ |
| 36 | Workspace Audit | read-only event list | audit permission; unauthorized user 403 | ☐ |
| 37 | Auth `login/logout` | logout → login; back button after logout | no private cached pages, CSRF & session | ☐ |
| 38 | Generic navigation | browser back/forward, click sidebar and cancel links repeatedly | broken URL, 404/405/500, JS console | ☐ |
| 39 | Forms & validation | required fields; invalid inputs; duplicate codes | validation errors retain input, no unintended mutation | ☐ |
| 40 | Async/workflow dialogs | open/close/confirm/Escape; no permission | modal DOM missing safely; no uncaught TypeError | ☐ |

## Per-action acceptance

- **Read role**: no New/Edit/Delete action rendered; direct mutation POST/PATCH/DELETE returns 403, never successful write.
- **CRUD without Delete**: View/New/Edit work; Delete hidden and direct DELETE blocked.
- **Workflow without special grant**: main page readable (if allowed), workflow button hidden, direct PATCH blocked.
- **Super Admin**: role/user/branch pages work, but protected system role cannot be edited/deleted.
- **Navigation**: Cancel/Back always reaches an authorized destination; no unsupported HTTP method error or invalid route parameter.
- **Status change**: method is PATCH for Delivery, Installation, Service Ticket, Maintenance, Invoice; avoid a browser operation if it mutates real operational data.
- **Workflow JS**: when status form is hidden, no uncaught `TypeError` in browser console; Installation checklist continues working if its separate permission exists.
- **Role save**: 22 templates remain selectable; saving does not auto-grant Audit Logs or scope `all`.
- **Sign-out**: authenticated pages not visible using browser Back after logout.

## Failure record template

```
UAT ID: 22 — Deliveries / View-only
Role (non-identifying): View-only
Page and action: deliveries.show → click Back
Expected: safe navigation to accessible list
Actual: <HTTP status or browser error>
Console error: <redacted, no cookies/tokens>
Laravel exception: <timestamp and relevant lines only>
Affected record: synthetic fixture, no real customer identifiers
```

**Release hold:** This matrix is a guide and remains open until its cases are actually exercised and logged. Passing source compilation and previous isolated RBAC test suites does not guarantee every page works at runtime.
