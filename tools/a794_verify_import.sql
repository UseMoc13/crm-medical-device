-- Read-only immediately after import, BEFORE bootstrap or application writes.
\set ON_ERROR_STOP on
BEGIN ISOLATION LEVEL REPEATABLE READ READ ONLY;
SET LOCAL lock_timeout = '3s';
SET LOCAL statement_timeout = '60s';
SET LOCAL search_path = public, pg_catalog;
DO $target$
BEGIN
 IF current_database() <> 'crm_medical_device' OR inet_server_addr() IS DISTINCT FROM '127.0.0.1'::inet OR inet_server_port() IS DISTINCT FROM 5432 THEN
  RAISE EXCEPTION 'A794 requires the approved local prototype crm_medical_device at 127.0.0.1:5432';
 END IF;
END;
$target$;
DO $baseline$
DECLARE t text; n bigint;
BEGIN
    IF (SELECT count(*) FROM information_schema.columns WHERE table_schema='public') <> 623 THEN RAISE EXCEPTION 'Baseline mismatch: columns'; END IF;
    IF (SELECT count(*) FROM pg_catalog.pg_constraint c JOIN pg_catalog.pg_namespace n ON n.oid=c.connamespace WHERE n.nspname='public' AND c.contype='f') <> 150 THEN RAISE EXCEPTION 'Baseline mismatch: foreign keys'; END IF;
    IF (SELECT count(*) FROM pg_catalog.pg_constraint c JOIN pg_catalog.pg_namespace n ON n.oid=c.connamespace WHERE n.nspname='public' AND c.contype='p') <> 65 THEN RAISE EXCEPTION 'Baseline mismatch: primary keys'; END IF;
    IF (SELECT count(*) FROM pg_catalog.pg_constraint c JOIN pg_catalog.pg_namespace n ON n.oid=c.connamespace WHERE n.nspname='public' AND c.contype='u') <> 31 THEN RAISE EXCEPTION 'Baseline mismatch: unique constraints'; END IF;
    IF (SELECT count(*) FROM pg_catalog.pg_constraint c JOIN pg_catalog.pg_namespace n ON n.oid=c.connamespace WHERE n.nspname='public' AND c.contype='c') <> 8 THEN RAISE EXCEPTION 'Baseline mismatch: check constraints'; END IF;
    IF (SELECT count(*) FROM pg_catalog.pg_class c JOIN pg_catalog.pg_namespace n ON n.oid=c.relnamespace WHERE n.nspname='public' AND c.relkind='S') <> 5 THEN RAISE EXCEPTION 'Baseline mismatch: sequences'; END IF;
    IF (SELECT count(*) FROM pg_catalog.pg_index i JOIN pg_catalog.pg_class c ON c.oid=i.indexrelid JOIN pg_catalog.pg_namespace n ON n.oid=c.relnamespace WHERE n.nspname='public' AND NOT EXISTS (SELECT 1 FROM pg_catalog.pg_constraint k WHERE k.conindid=i.indexrelid AND k.contype IN ('p','u','x'))) <> 137 THEN RAISE EXCEPTION 'Baseline mismatch: standalone indexes'; END IF;
    IF (SELECT array_agg(tablename::text ORDER BY tablename COLLATE "C") FROM pg_catalog.pg_tables WHERE schemaname='public') IS DISTINCT FROM ARRAY['activities', 'announcement_attachments', 'announcement_media', 'announcements', 'audit_logs', 'branches', 'brands', 'cache', 'cache_locks', 'contacts', 'contracts', 'crm_team_members', 'crm_teams', 'customers', 'deliveries', 'delivery_items', 'failed_jobs', 'installation_checklists', 'installations', 'inventories', 'invoice_items', 'invoices', 'job_batches', 'jobs', 'leads', 'maintenance_records', 'maintenance_schedules', 'migrations', 'notifications', 'opportunities', 'opportunity_items', 'permissions', 'product_categories', 'products', 'products_product_code_sequence', 'quotation_items', 'quotations', 'role_permissions', 'roles', 'sales_order_items', 'sales_orders', 'serial_numbers', 'service_ticket_parts', 'service_ticket_resolution_logs', 'service_ticket_work_logs', 'service_tickets', 'stock_movements', 'user_reporting_lines', 'user_roles', 'users', 'warehouses', 'warranties', 'workspace_audit_events', 'workspace_calendar_day_exclusions', 'workspace_calendar_events', 'workspace_collaboration_entries', 'workspace_collaboration_members', 'workspace_note_mentions', 'workspace_notification_preferences', 'workspace_notifications', 'workspace_personal_notes', 'workspace_task_assignments', 'workspace_task_reviews', 'workspace_task_submission_files', 'workspace_tasks']::text[] THEN RAISE EXCEPTION 'Baseline mismatch: table names'; END IF;
    IF (SELECT array_agg(migration::text ORDER BY migration COLLATE "C") FROM public.migrations) IS DISTINCT FROM ARRAY['0001_01_01_000000_create_users_table', '0001_01_01_000001_create_cache_table', '0001_01_01_000002_create_jobs_table', '2026_10_15_102740_add_correction_fields_to_stock_movements_table', '2026_10_21_061901_create_announcements_and_media_tables', '2026_10_28_000000_add_thumbnail_appearance_to_announcements_table', '2026_11_01_072347_create_products_product_code_sequence', '2026_11_04_230000_create_delivery_items_if_missing', '2026_11_05_000000_add_warehouse_to_delivery_items', '2026_11_05_000001_create_service_ticket_resolution_logs_table', '2026_11_12_000001_create_service_ticket_work_logs_table', '2026_11_12_190000_create_user_roles_table', '2026_11_12_200000_add_rbac_protection_and_scope', '2026_11_12_210000_seed_crm_module_access_permissions', '2026_11_12_220000_seed_crm_page_permissions', '2026_11_12_230000_seed_crm_workflow_action_permissions', '2026_11_13_000000_add_creator_attribution_to_crm_entities', '2026_11_13_010000_create_customer_code_sequence', '2026_11_13_020000_add_installation_checklist_actor_columns', '2026_11_14_000000_create_workspace_personal_tables', '2026_11_15_180000_create_calendar_day_exclusions', '2026_11_15_200000_add_workspace_assignments_and_schedules', '2026_11_15_230000_add_crm_organization_hierarchy', '2026_11_16_000000_create_crm_teams_and_members', '2026_11_16_160000_create_workspace_collaboration_tables', '2026_11_17_120000_add_workspace_task_reviews', '2026_11_17_210000_link_workspace_personal_notes_to_activities', '2026_11_17_220000_create_workspace_note_mentions_and_revision', '2026_11_17_230000_create_workspace_notifications', '2026_11_17_234000_create_workspace_notification_preferences', '2026_11_18_010000_create_workspace_audit_events', '2026_11_18_190000_add_crm_status_notification_preference']::text[] THEN RAISE EXCEPTION 'Baseline mismatch: migration ledger'; END IF;
    IF (SELECT array_agg(permission_name::text ORDER BY permission_name COLLATE "C") FROM public.permissions) IS DISTINCT FROM ARRAY['announcements.manage', 'billing.access', 'catalog.access', 'catalog.manage', 'crm_page.activities.create', 'crm_page.activities.delete', 'crm_page.activities.update', 'crm_page.activities.view', 'crm_page.announcement_management.create', 'crm_page.announcement_management.delete', 'crm_page.announcement_management.update', 'crm_page.announcement_management.view', 'crm_page.brands.create', 'crm_page.brands.delete', 'crm_page.brands.update', 'crm_page.brands.view', 'crm_page.contacts.create', 'crm_page.contacts.delete', 'crm_page.contacts.update', 'crm_page.contacts.view', 'crm_page.contracts.create', 'crm_page.contracts.delete', 'crm_page.contracts.update', 'crm_page.contracts.view', 'crm_page.customers.create', 'crm_page.customers.delete', 'crm_page.customers.update', 'crm_page.customers.view', 'crm_page.deliveries.create', 'crm_page.deliveries.delete', 'crm_page.deliveries.update', 'crm_page.deliveries.view', 'crm_page.installations.create', 'crm_page.installations.delete', 'crm_page.installations.update', 'crm_page.installations.view', 'crm_page.inventories.create', 'crm_page.inventories.delete', 'crm_page.inventories.update', 'crm_page.inventories.view', 'crm_page.invoices.create', 'crm_page.invoices.delete', 'crm_page.invoices.update', 'crm_page.invoices.view', 'crm_page.leads.create', 'crm_page.leads.delete', 'crm_page.leads.update', 'crm_page.leads.view', 'crm_page.maintenance.create', 'crm_page.maintenance.delete', 'crm_page.maintenance.update', 'crm_page.maintenance.view', 'crm_page.opportunities.create', 'crm_page.opportunities.delete', 'crm_page.opportunities.update', 'crm_page.opportunities.view', 'crm_page.product_categories.create', 'crm_page.product_categories.delete', 'crm_page.product_categories.update', 'crm_page.product_categories.view', 'crm_page.products.create', 'crm_page.products.delete', 'crm_page.products.update', 'crm_page.products.view', 'crm_page.quotations.create', 'crm_page.quotations.delete', 'crm_page.quotations.update', 'crm_page.quotations.view', 'crm_page.reports.create', 'crm_page.reports.delete', 'crm_page.reports.update', 'crm_page.reports.view', 'crm_page.sales_orders.create', 'crm_page.sales_orders.delete', 'crm_page.sales_orders.update', 'crm_page.sales_orders.view', 'crm_page.serial_numbers.create', 'crm_page.serial_numbers.delete', 'crm_page.serial_numbers.update', 'crm_page.serial_numbers.view', 'crm_page.service_history.create', 'crm_page.service_history.delete', 'crm_page.service_history.update', 'crm_page.service_history.view', 'crm_page.service_tickets.create', 'crm_page.service_tickets.delete', 'crm_page.service_tickets.update', 'crm_page.service_tickets.view', 'crm_page.stock_movements.create', 'crm_page.stock_movements.delete', 'crm_page.stock_movements.update', 'crm_page.stock_movements.view', 'crm_page.warehouses.create', 'crm_page.warehouses.delete', 'crm_page.warehouses.update', 'crm_page.warehouses.view', 'crm_page.warranties.create', 'crm_page.warranties.delete', 'crm_page.warranties.update', 'crm_page.warranties.view', 'crm_workflow.deliveries.status', 'crm_workflow.installations.activate_warranty', 'crm_workflow.installations.checklist', 'crm_workflow.installations.status', 'crm_workflow.invoices.status', 'crm_workflow.maintenance.status', 'crm_workflow.service_tickets.parts_create', 'crm_workflow.service_tickets.parts_delete', 'crm_workflow.service_tickets.parts_update', 'crm_workflow.service_tickets.status', 'customer_lead.access', 'delivery.access', 'installation.access', 'inventory.access', 'reporting.access', 'sales_crm.access', 'service.access']::text[] THEN RAISE EXCEPTION 'Baseline mismatch: permission catalog'; END IF;
END;
$baseline$;
DO $dataset$
DECLARE t text; expected bigint; actual bigint;
BEGIN
 FOR t, expected IN SELECT * FROM (VALUES
 ('activities', 2),
 ('announcement_attachments', 0),
 ('announcement_media', 0),
 ('announcements', 4),
 ('audit_logs', 0),
 ('branches', 2),
 ('brands', 2),
 ('cache', 0),
 ('cache_locks', 0),
 ('contacts', 30),
 ('contracts', 1),
 ('crm_team_members', 0),
 ('crm_teams', 0),
 ('customers', 30),
 ('deliveries', 6),
 ('delivery_items', 5),
 ('failed_jobs', 0),
 ('installation_checklists', 6),
 ('installations', 3),
 ('inventories', 12),
 ('invoice_items', 4),
 ('invoices', 4),
 ('job_batches', 0),
 ('jobs', 0),
 ('leads', 30),
 ('maintenance_records', 1),
 ('maintenance_schedules', 2),
 ('migrations', 32),
 ('notifications', 0),
 ('opportunities', 410),
 ('opportunity_items', 410),
 ('permissions', 117),
 ('product_categories', 2),
 ('products', 6),
 ('products_product_code_sequence', 0),
 ('quotation_items', 410),
 ('quotations', 410),
 ('role_permissions', 7),
 ('roles', 5),
 ('sales_order_items', 405),
 ('sales_orders', 405),
 ('serial_numbers', 5),
 ('service_ticket_parts', 1),
 ('service_ticket_resolution_logs', 0),
 ('service_ticket_work_logs', 0),
 ('service_tickets', 2),
 ('stock_movements', 16),
 ('user_reporting_lines', 1),
 ('user_roles', 9),
 ('users', 8),
 ('warehouses', 2),
 ('warranties', 1),
 ('workspace_audit_events', 0),
 ('workspace_calendar_day_exclusions', 0),
 ('workspace_calendar_events', 1),
 ('workspace_collaboration_entries', 1),
 ('workspace_collaboration_members', 1),
 ('workspace_note_mentions', 1),
 ('workspace_notification_preferences', 8),
 ('workspace_notifications', 0),
 ('workspace_personal_notes', 2),
 ('workspace_task_assignments', 1),
 ('workspace_task_reviews', 0),
 ('workspace_task_submission_files', 0),
 ('workspace_tasks', 1)
 ) AS v(table_name, row_count) LOOP
  EXECUTE format('SELECT count(*) FROM public.%I', t) INTO actual;
  IF actual <> expected THEN RAISE EXCEPTION 'A794 count mismatch for %: actual %, expected %', t, actual, expected; END IF;
 END LOOP;
 IF EXISTS (SELECT 1 FROM public.inventories i FULL JOIN (SELECT warehouse_id,product_id,SUM(CASE WHEN movement_type='stock_out' THEN -quantity ELSE quantity END) qty FROM public.stock_movements GROUP BY warehouse_id,product_id) m USING(warehouse_id,product_id) WHERE i.quantity IS DISTINCT FROM m.qty) THEN RAISE EXCEPTION 'Stock reconciliation failed'; END IF;
 IF EXISTS (SELECT 1 FROM public.inventories GROUP BY warehouse_id,product_id HAVING count(*)<>1) THEN RAISE EXCEPTION 'Duplicate stock row'; END IF;
 IF EXISTS (SELECT 1 FROM public.quotations q LEFT JOIN (SELECT quotation_id,SUM(subtotal) subtotal FROM public.quotation_items GROUP BY quotation_id) x USING(quotation_id) WHERE q.subtotal IS DISTINCT FROM x.subtotal OR q.total_amount IS DISTINCT FROM q.subtotal+COALESCE(q.tax,0)-COALESCE(q.discount,0)) THEN RAISE EXCEPTION 'Quotation parity failed'; END IF;
 IF EXISTS (SELECT 1 FROM public.sales_orders s LEFT JOIN (SELECT sales_order_id,SUM(subtotal) subtotal FROM public.sales_order_items GROUP BY sales_order_id) x USING(sales_order_id) JOIN public.quotations q USING(quotation_id) WHERE s.subtotal IS DISTINCT FROM x.subtotal OR s.total_amount IS DISTINCT FROM q.total_amount OR q.status <> 'approved') THEN RAISE EXCEPTION 'Order parity failed'; END IF;
 IF EXISTS (SELECT 1 FROM public.invoices i LEFT JOIN (SELECT invoice_id,SUM(subtotal) subtotal FROM public.invoice_items GROUP BY invoice_id) x USING(invoice_id) JOIN public.sales_orders s USING(sales_order_id) WHERE i.subtotal IS DISTINCT FROM x.subtotal OR i.total_amount IS DISTINCT FROM i.subtotal+COALESCE(i.tax,0)-COALESCE(i.discount,0) OR i.total_amount IS DISTINCT FROM s.total_amount) THEN RAISE EXCEPTION 'Invoice parity failed'; END IF;
 IF EXISTS (SELECT 1 FROM public.roles WHERE is_system) THEN RAISE EXCEPTION 'Unexpected system role'; END IF;
 IF EXISTS (SELECT 1 FROM public.users u LEFT JOIN public.user_roles ur ON u.user_id=ur.user_id AND ur.is_primary WHERE ur.role_id IS DISTINCT FROM u.role_id) THEN RAISE EXCEPTION 'Primary role mismatch'; END IF;
 IF (SELECT count(*) FROM public.role_permissions rp JOIN public.roles r USING(role_id) JOIN public.permissions p USING(permission_id) WHERE r.role_name='UAT Catalog Viewer' AND p.permission_name='catalog.access' AND rp.data_scope IS NULL) <> 1 THEN RAISE EXCEPTION 'Expected grant missing: catalog.access'; END IF;
 IF (SELECT count(*) FROM public.role_permissions rp JOIN public.roles r USING(role_id) JOIN public.permissions p USING(permission_id) WHERE r.role_name='UAT Catalog Viewer' AND p.permission_name='crm_page.products.view' AND rp.data_scope='all') <> 1 THEN RAISE EXCEPTION 'Expected grant missing: crm_page.products.view'; END IF;
 IF (SELECT count(*) FROM public.role_permissions rp JOIN public.roles r USING(role_id) JOIN public.permissions p USING(permission_id) WHERE r.role_name='UAT Reports Only' AND p.permission_name='reporting.access' AND rp.data_scope IS NULL) <> 1 THEN RAISE EXCEPTION 'Expected grant missing: reporting.access'; END IF;
 IF (SELECT count(*) FROM public.role_permissions rp JOIN public.roles r USING(role_id) JOIN public.permissions p USING(permission_id) WHERE r.role_name='UAT Reports Only' AND p.permission_name='crm_page.reports.view' AND rp.data_scope='all') <> 1 THEN RAISE EXCEPTION 'Expected grant missing: crm_page.reports.view'; END IF;
 IF (SELECT count(*) FROM public.role_permissions rp JOIN public.roles r USING(role_id) JOIN public.permissions p USING(permission_id) WHERE r.role_name='UAT Finance Reader' AND p.permission_name='billing.access' AND rp.data_scope IS NULL) <> 1 THEN RAISE EXCEPTION 'Expected grant missing: billing.access'; END IF;
 IF (SELECT count(*) FROM public.role_permissions rp JOIN public.roles r USING(role_id) JOIN public.permissions p USING(permission_id) WHERE r.role_name='UAT Finance Reader' AND p.permission_name='crm_page.invoices.view' AND rp.data_scope='all') <> 1 THEN RAISE EXCEPTION 'Expected grant missing: crm_page.invoices.view'; END IF;
 IF (SELECT count(*) FROM public.role_permissions rp JOIN public.roles r USING(role_id) JOIN public.permissions p USING(permission_id) WHERE r.role_name='UAT Sales Owner' AND p.permission_name='sales_crm.access' AND rp.data_scope IS NULL) <> 1 THEN RAISE EXCEPTION 'Expected grant missing: sales_crm.access'; END IF;
END;
$dataset$;
ROLLBACK;
SELECT 'A794_IMPORTED_DATASET_VALIDATED' AS result;
