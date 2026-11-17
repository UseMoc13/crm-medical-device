# CRM Medical Device — Final Database Reset & Insertion Plan (DEFERRED)

**User decision:** Revisit every page/module and perform a coordinated PostgreSQL reset and insertion **after all modules are finished**. Do not execute these steps during A7.4.9 or subsequent incremental releases without a specific final-stage request.

## Final-stage checklist (planning only)

1. Freeze source and migrations; back up PostgreSQL database plus private file storage. Validate backup restore on a disposable database.
2. Complete a cross-module schema and foreign-key review, including role permissions, users, organizations, teams, collaboration, audit logs, announcements, and activity links.
3. Prepare a deterministic, rerunnable set of Laravel seeders/factories with synthetic Indonesian CRM demo data (clearly marked as demo), valid UUID foreign keys, and controlled timestamps.
4. Seed reference tables before transactions: roles, permissions, branches, users, product categories, brands, warehouses, teams and memberships.
5. Seed core CRM flow with consistent relationships: customers, contacts, leads, activities, opportunities, quotations, sales orders, contracts, products, inventories, stock movements, serial numbers, deliveries, installations, warranties, service tickets, service maintenance and invoices.
6. Seed Workspace and related workflows with explicit collaboration memberships, assigned TO-DO records, submission evidence metadata (only when corresponding files exist), review decisions, calendar events, personal notes, and announcements. Avoid linking a record whose parent does not exist.
7. Confirm permission boundaries using separate Creator/Assignee/Viewer/Contributor test accounts and documented demo credentials; do not store passwords in version control or expose production contact data.
8. Test a **disposable staging database** first. Only then schedule a controlled final reset of the intended development environment, followed by the seed, basic counts, CRUD smoke tests and UI screenshots.
9. Never truncate tables opportunistically while code changes remain active. Never call `migrate:fresh` against production.

**Implementation status:** Plan only; no seeder code is installed and no database data is modified by this document.
