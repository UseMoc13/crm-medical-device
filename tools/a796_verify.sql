-- A796 POST-IMPORT VALIDATOR, READ-ONLY. Run immediately after commit before new user CRUD.
\set ON_ERROR_STOP on
BEGIN READ ONLY;
SET LOCAL search_path = public, pg_catalog;
DO $guard$
BEGIN
 IF current_database()<>'crm_medical_device' OR inet_server_addr() IS DISTINCT FROM '127.0.0.1'::inet OR inet_server_port() IS DISTINCT FROM 5432 THEN
   RAISE EXCEPTION 'STOP: wrong target';
 END IF;
END;
$guard$;
DO $checks$
DECLARE t text; pred text; expected_count bigint; actual bigint;
BEGIN
  FOR t,pred,expected_count IN
    SELECT x.tab,x.predicate,x.expected FROM (VALUES
 ('branches','branch_code LIKE ''DEMO26-B%''',3),
 ('roles','role_name IN (''UAT Technician'',''UAT Customer Service'')',2),
 ('users','email LIKE ''demo.staff%@example.invalid''',12),
 ('user_roles','user_id IN (SELECT user_id FROM public.users WHERE email LIKE ''demo.staff%@example.invalid'')',12),
 ('workspace_notification_preferences','user_id IN (SELECT user_id FROM public.users WHERE email LIKE ''demo.staff%@example.invalid'')',12),
 ('brands','brand_name LIKE ''%(Simulasi)'' AND brand_id IN (md5(''a796.brand.1'')::uuid,md5(''a796.brand.2'')::uuid)',2),
 ('products','product_code LIKE ''DEMO26-P%''',12),
 ('warehouses','warehouse_code LIKE ''DEMO26-WH%''',2),
 ('inventories','inventory_id IN (SELECT md5(''a796.inv.''||lpad(p::text,2,''0'')||''.''||w)::uuid FROM generate_series(1,12) p CROSS JOIN generate_series(1,2) w)',24),
 ('stock_movements','stock_movement_id IN (SELECT md5(''a796.stock.''||lpad(p::text,2,''0'')||''.''||w)::uuid FROM generate_series(1,12) p CROSS JOIN generate_series(1,2) w)',24),
 ('customers','customer_code LIKE ''DEMO26-C%''',60),
 ('contacts','email LIKE ''demo.contact%@example.invalid''',90),
 ('leads','lead_code LIKE ''DEMO26-L%''',45),
 ('activities','activity_id IN (SELECT md5(''a796.activity.''||lpad(n::text,3,''0''))::uuid FROM generate_series(1,40) g(n))',40),
 ('opportunities','opportunity_code LIKE ''DEMO26-O%''',240),
 ('opportunity_items','opportunity_id IN (SELECT opportunity_id FROM public.opportunities WHERE opportunity_code LIKE ''DEMO26-O%'')',240),
 ('quotations','quotation_number LIKE ''DEMO26-Q%''',200),
 ('quotation_items','quotation_id IN (SELECT quotation_id FROM public.quotations WHERE quotation_number LIKE ''DEMO26-Q%'')',200),
 ('sales_orders','sales_order_number LIKE ''DEMO26-SO%''',180),
 ('sales_order_items','sales_order_id IN (SELECT sales_order_id FROM public.sales_orders WHERE sales_order_number LIKE ''DEMO26-SO%'')',180),
 ('invoices','invoice_number LIKE ''DEMO26-INV%''',36),
 ('invoice_items','invoice_id IN (SELECT invoice_id FROM public.invoices WHERE invoice_number LIKE ''DEMO26-INV%'')',36),
 ('deliveries','delivery_number LIKE ''DEMO26-D%''',20),
 ('service_tickets','ticket_number LIKE ''DEMO26-T%''',12)
    ) AS x(tab,predicate,expected)
  LOOP
    EXECUTE format('SELECT count(*) FROM public.%I WHERE %s', t,pred) INTO actual;
    IF actual<>expected_count THEN
      RAISE EXCEPTION 'A796 validation failed on %, count % expected %',t,actual,expected_count;
    END IF;
  END LOOP;
  IF EXISTS (SELECT 1 FROM public.sales_orders so
    LEFT JOIN public.sales_order_items si ON si.sales_order_id=so.sales_order_id
    WHERE so.sales_order_number LIKE 'DEMO26-SO%'
    GROUP BY so.sales_order_id,so.subtotal,so.total_amount
    HAVING COALESCE(SUM(si.subtotal),0)<>so.subtotal
        OR COALESCE(SUM(si.subtotal),0)<>so.total_amount) THEN
    RAISE EXCEPTION 'A796 sales order totals mismatch';
  END IF;
  IF EXISTS (SELECT 1 FROM public.quotations q
    LEFT JOIN public.quotation_items qi ON qi.quotation_id=q.quotation_id
    WHERE q.quotation_number LIKE 'DEMO26-Q%'
    GROUP BY q.quotation_id,q.subtotal,q.total_amount
    HAVING COALESCE(SUM(qi.subtotal),0)<>q.subtotal
        OR COALESCE(SUM(qi.subtotal),0)<>q.total_amount) THEN
    RAISE EXCEPTION 'A796 quotation totals mismatch';
  END IF;
  IF EXISTS (SELECT 1 FROM public.invoices i
    LEFT JOIN public.invoice_items ii ON ii.invoice_id=i.invoice_id
    WHERE i.invoice_number LIKE 'DEMO26-INV%'
    GROUP BY i.invoice_id,i.subtotal,i.total_amount
    HAVING COALESCE(SUM(ii.subtotal),0)<>i.subtotal
        OR COALESCE(SUM(ii.subtotal),0)<>i.total_amount) THEN
    RAISE EXCEPTION 'A796 invoice totals mismatch';
  END IF;
  IF EXISTS (SELECT 1 FROM public.inventories i
     WHERE i.warehouse_id IN (md5('a796.warehouse.1')::uuid,md5('a796.warehouse.2')::uuid)
       AND i.quantity <> (SELECT COALESCE(SUM(CASE WHEN m.movement_type='opening_stock' THEN m.quantity ELSE -m.quantity END),0)
         FROM public.stock_movements m WHERE m.warehouse_id=i.warehouse_id AND m.product_id=i.product_id)) THEN
    RAISE EXCEPTION 'A796 stock movement and inventory mismatch';
  END IF;
  IF (SELECT count(*) FROM public.user_roles ur JOIN public.users u ON u.user_id=ur.user_id
    WHERE u.email LIKE 'demo.staff%@example.invalid' AND ur.is_primary=true) <> 12 THEN
    RAISE EXCEPTION 'A796 primary role mismatch';
  END IF;
  IF EXISTS(SELECT 1 FROM public.role_permissions rp
    WHERE rp.role_id IN(md5('a796.role.tech')::uuid,md5('a796.role.cs')::uuid)) THEN
    RAISE EXCEPTION 'A796 new roles unexpectedly have permissions';
  END IF;
  IF (SELECT count(*) FROM public.permissions) <> 117 OR (SELECT count(*) FROM public.migrations) <> 32 THEN
    RAISE EXCEPTION 'A796 modified permissions or migration ledger';
  END IF;
  IF (SELECT count(*) FROM public.users u JOIN public.user_roles ur ON ur.user_id=u.user_id
      JOIN public.roles r ON r.role_id=ur.role_id WHERE r.is_system AND lower(r.role_name)='super admin')<1 THEN
    RAISE EXCEPTION 'A796 protected administrator lost';
  END IF;
  RAISE NOTICE 'A796 postchecks passed: 1684 added records plus fixture label improvements';
END;
$checks$;
ROLLBACK;
SELECT 'A796_HUMANIZED_EXPANSION_VALIDATED' AS result;
