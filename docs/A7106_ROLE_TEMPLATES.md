# A7.10.6 — Role Editor CRM Template Refresh

This patch updates the **Role Editor → CRM Template** selection to 22 role-oriented presets matching CRM Medical Device modules. It does not create real roles, edit existing role records, run a database migration, or grant Super Admin permissions.

## Safety and purpose

- **View / Create / Edit / Delete** remain separate (A6 module/page permission catalog + A7.10.5 Blade action visibility).
- All template options are validated against one allowlist on the backend, not trusted from the browser.
- Template workflow permissions are explicitly curated, not inferred from *every* page action. For example, a Billing Clerk does not automatically receive `invoices.status`.
- Existing role IDs and all historical role_permission records remain unchanged during installation. **Only explicitly saving a role in CRM Template mode changes that role's grants.** Existing Role Editor defaults to Custom mode.
- A CRM template is **not** a data scope policy. Template mode preserves previously stored data scopes on retained view permissions, but newly selected view permissions can have NULL / unconfigured scope. These do not become `all`. For data-sensitive roles, **Copy Template to Custom**, set appropriate scopes, and review results before saving. Never silently widen scopes.
- Management/Finance reports remain subject to existing financial privacy policy holds. This patch does not resolve those policies.
- System Super Admin is protected separately; the legacy `admin` preset is renamed “Announcement Manager (legacy Admin)” and does not grant Super Admin.
- No changes to independent Workspace Notes/To-Do/Calendar permissions or assignment eligibility.

## Existing IDs updated

`admin`, `sales`, `warehouse`, `delivery_staff`, `technician`, `finance`, `management`. Old select values remain valid, but the newly chosen preset produces the revised grants only after Save.

## New IDs

`customer_service`, `sales_support`, `sales_manager`, `lead_specialist`, `catalog_manager`, `warehouse_operator`, `inventory_controller`, `logistics_coordinator`, `installation_coordinator`, `service_coordinator`, `warranty_coordinator`, `billing_clerk`, `finance_manager`, `qa_auditor`, `crm_readonly`.

The descriptions are visible in the Role Editor under the template select. The template preview reflects all available page grants. After switching to Custom, individual CRUD/workflow checkboxes and data scopes can be reviewed.

## Installation on Windows PowerShell

Extract ZIP over repo root *after* A7.10.5 and a clean checkpoint (preserve existing files). New files only; source edits are made by the installer:

```powershell
php tools/a7106_source_check.php
php tools/a7106_role_templates_installer.php --check
php tools/a7106_role_templates_installer.php --apply
php tools/a7106_role_templates_installer.php --check
php artisan view:clear
php artisan view:cache
php artisan test --filter=CrmRoleTemplateCatalogTest
php artisan test --filter=CrmRouteAuthorizationHttpTest
```

`--check` compiles modified Roles Blade and PHP-lints the resulting code, in addition to linting the two modified PHP sources. It performs no write or DB operations.

`--apply` backs up the three original sources under `storage/app/private/a7106_role_template_backups/<BACKUP_ID>`, checks for source modifications after validation, and writes the already-validated versions. If a write fails, it attempts to restore files it wrote.

`--rollback=<BACKUP_ID>` restores those original three files **only if each current file still matches the checksum of the installed version**. It does not change DB rows or touch the new `CrmRoleTemplateCatalog` source/test.

## Manual acceptance

1. Open Roles → Create. Choose CRM Template, inspect new choices/descriptions and live preview; do **not** save solely to explore templates.
2. Select Customer Service and verify no Delete anywhere; select Warehouse Operator and verify products are read-only and stock movements permit Create.
3. Select Billing Clerk. Workflow status should NOT be granted; Finance grants invoice status explicitly.
4. Choose a disposable demo role (not system role), Copy Template to Custom and set View scopes; verify output before saving. If an existing custom role is edited, ensure custom choices are not replaced unexpectedly.
5. Use a test user with the role to confirm server-rendered buttons are hidden, and direct unauthorized POST/PATCH/DELETE remains 403. Test on disposable/local data only.
6. Confirm Roles detail, Branches index, Teams index and the main sidebar remain free of Blade ParseError after the A7.10.5 repair.

**LIMIT:** Source tests and Blade compiler checks are not full browser or DB UAT. Two previously known reporting/announcement policy holds remain outstanding.
