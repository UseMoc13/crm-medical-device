-- A7.9.6: Human-readable, EXPANSION-ONLY fixture for LOCAL disposable CRM.
-- Rehearsal and commit scripts differ ONLY in their transaction endings.
-- Requires already-imported A7.9.4 dataset + Super Admin, PG18, pgcrypto.
-- Safety: localhost:5432 + exact db name, fail-on-repeat, locking, strict postchecks.
-- No DDL, no deletes, no truncation, no mutation of real/non-fixture records.
-- Does not change authentication secrets, role grants, account emails or user IDs.
\set ON_ERROR_STOP on
BEGIN;
SET LOCAL search_path = public, pg_catalog;
SET LOCAL lock_timeout = '5s';
SET LOCAL statement_timeout = '120s';
DO $guard$
DECLARE current_user_id uuid;
BEGIN
  IF current_database() <> 'crm_medical_device'
     OR inet_server_addr() IS DISTINCT FROM '127.0.0.1'::inet
     OR inet_server_port() IS DISTINCT FROM 5432 THEN
    RAISE EXCEPTION 'STOP: A796 only runs on approved LOCAL crm_medical_device 127.0.0.1:5432';
  END IF;
  IF (SELECT count(*) FROM pg_catalog.pg_tables WHERE schemaname='public') <> 65
     OR (SELECT count(*) FROM public.migrations) <> 32
     OR (SELECT count(*) FROM public.permissions) <> 117 THEN
    RAISE EXCEPTION 'STOP: database is not the expected A793/A794 schema';
  END IF;
  IF NOT EXISTS (SELECT 1 FROM public.branches WHERE branch_code='UAT-B1')
     OR NOT EXISTS (SELECT 1 FROM public.customers WHERE customer_code='UAT-C001')
     OR NOT EXISTS (SELECT 1 FROM public.opportunities WHERE opportunity_code='UAT-O0001')
     OR NOT EXISTS (SELECT 1 FROM public.sales_orders WHERE sales_order_number='UAT-SO0001')
     OR NOT EXISTS (SELECT 1 FROM public.users WHERE email='uat.user01@example.invalid')
     OR NOT EXISTS (
       SELECT 1 FROM public.users u JOIN public.user_roles ur ON ur.user_id=u.user_id
       JOIN public.roles r ON r.role_id=ur.role_id
       WHERE r.is_system=true AND lower(r.role_name)='super admin'
     ) THEN RAISE EXCEPTION 'STOP: original UAT markers or protected administrator missing';
  END IF;
  IF EXISTS (SELECT 1 FROM public.customers WHERE customer_code LIKE 'DEMO26-C%')
     OR EXISTS (SELECT 1 FROM public.users WHERE email LIKE 'demo.staff%%@example.invalid')
     OR EXISTS (SELECT 1 FROM public.opportunities WHERE opportunity_code LIKE 'DEMO26-O%')
     OR EXISTS (SELECT 1 FROM public.roles WHERE role_name='UAT Technician') THEN
    RAISE EXCEPTION 'STOP: A796 expansion appears already present. Do not rerun.';
  END IF;
  IF to_regprocedure('public.gen_salt(text)') IS NULL THEN
    RAISE EXCEPTION 'STOP: pgcrypto gen_salt not available';
  END IF;
  -- UAT-SN005 is intentionally unassigned (customer_id NULL) in A794.
  -- Ticket generation rotates over eligible serials, not all 5 serials.
  IF NOT EXISTS (SELECT 1 FROM public.serial_numbers
                 WHERE serial_number LIKE 'UAT-SN%' AND customer_id IS NOT NULL) THEN
    RAISE EXCEPTION 'STOP: no customer-linked UAT serial numbers for service tickets';
  END IF;
END;
$guard$;
-- Lock only tables this script changes, prevent inconsistent concurrent writes.
LOCK TABLE public.branches, public.roles, public.users, public.user_roles,
 public.workspace_notification_preferences, public.brands, public.product_categories,
 public.products, public.warehouses, public.inventories, public.stock_movements,
 public.customers, public.contacts, public.leads, public.opportunities,
 public.opportunity_items, public.quotations, public.quotation_items,
 public.sales_orders, public.sales_order_items, public.invoices, public.invoice_items,
 public.deliveries, public.service_tickets, public.serial_numbers, public.activities
 IN SHARE ROW EXCLUSIVE MODE;
-- Re-run guard AFTER locks to avoid race with another import.
DO $repeat$
BEGIN
  IF EXISTS(SELECT 1 FROM public.customers WHERE customer_code LIKE 'DEMO26-C%')
      OR EXISTS(SELECT 1 FROM public.opportunities WHERE opportunity_code LIKE 'DEMO26-O%') THEN
    RAISE EXCEPTION 'STOP: expansion already started';
  END IF;
END;
$repeat$;


-- ====================================================================
-- HUMANIZE EXACT A794 DISPLAY PLACEHOLDERS (preserve all UUIDs and emails)
-- ====================================================================

UPDATE public.branches SET
 branch_name = CASE branch_code WHEN 'UAT-B1' THEN 'Kantor Pusat Tangerang' WHEN 'UAT-B2' THEN 'Cabang Jakarta' END,
 address = CASE branch_code WHEN 'UAT-B1' THEN 'Kawasan Bisnis Tangerang, Banten (alamat simulasi)' ELSE 'Jakarta Barat, DKI Jakarta (alamat simulasi)' END,
 updated_at=CURRENT_TIMESTAMP
WHERE (branch_code='UAT-B1' AND branch_name='UAT Branch 1')
   OR (branch_code='UAT-B2' AND branch_name='UAT Branch 2');
UPDATE public.warehouses SET warehouse_name=CASE warehouse_code
 WHEN 'UAT-WH1' THEN 'Gudang Pusat Tangerang' WHEN 'UAT-WH2' THEN 'Gudang Jakarta' END,
 address='Alamat gudang simulasi - bukan lokasi operasional nyata', updated_at=CURRENT_TIMESTAMP
WHERE (warehouse_code='UAT-WH1' AND warehouse_name='UAT Warehouse 1')
   OR (warehouse_code='UAT-WH2' AND warehouse_name='UAT Warehouse 2');
UPDATE public.brands SET brand_name=CASE brand_name WHEN 'UAT Brand 1' THEN 'Mediora (Simulasi)' ELSE 'Klinika (Simulasi)' END
WHERE brand_name IN ('UAT Brand 1','UAT Brand 2');
UPDATE public.product_categories SET category_name=CASE category_name
 WHEN 'UAT Monitoring' THEN 'Monitoring dan Terapi' ELSE 'Aksesori Alat Medis' END
WHERE category_name IN ('UAT Monitoring','UAT Accessories');

WITH names AS (
 SELECT n, label FROM unnest(ARRAY['Rizky Pratama - Sales Executive', 'Nadia Putri - Sales Executive', 'Rani Maharani - Catalog Viewer', 'Andi Saputra - Reporting Staff', 'Farhan Hidayat - Finance Staff', 'Sinta Amelia - Operations Staff', 'Ayu Cahyani - Finance Reporting', 'Rudi Wijaya - Inactive Staff']::text[]) WITH ORDINALITY AS t(label,n)
)
UPDATE public.users u SET name=names.label, updated_at=CURRENT_TIMESTAMP
FROM names WHERE u.email='uat.user'||lpad(names.n::text,2,'0')||'@example.invalid'
AND u.name='UAT User '||lpad(names.n::text,2,'0');

WITH labels AS (
 SELECT n, label FROM unnest(ARRAY['Patient Monitor PM-500', 'Infusion Pump IF-20', 'Syringe Pump SP-10', 'ECG Monitor ECG-12', 'SpO2 Sensor Reusable', 'ECG Cable 5-Lead']::text[]) WITH ORDINALITY t(label,n)
)
UPDATE public.products p SET product_name=labels.label, updated_at=CURRENT_TIMESTAMP
FROM labels WHERE p.product_code='UAT-P'||lpad(labels.n::text,3,'0') AND p.product_name LIKE 'UAT %';

WITH names(code,label,cust_type,city) AS (VALUES
 ('UAT-C001','PT Indah Medika Sejahtera','Distributor','Tangerang'),
 ('UAT-C002','RS Harapan Bunda Tangerang','Hospital','Tangerang'),
 ('UAT-C003','Klinik Citra Medika','Clinic','Depok'),
 ('UAT-C004','PT Nusantara Sehat Mandiri','Distributor','Jakarta'),
 ('UAT-C005','RS Cendana Husada','Hospital','Bogor'),
 ('UAT-C006','Klinik Cahaya Sehat','Clinic','Bandung'),
 ('UAT-C007','PT Karya Alkes Prima','Distributor','Bekasi'),
 ('UAT-C008','RS Mentari Kasih','Hospital','Serang'),
 ('UAT-C009','Klinik Sejahtera Mandiri','Clinic','Tangerang Selatan'),
 ('UAT-C010','PT Bina Sarana Medis','Distributor','Semarang'),
 ('UAT-C011','RS Pelita Medika','Hospital','Jakarta'),
 ('UAT-C012','Klinik Bintang Kesehatan','Clinic','Cilegon'),
 ('UAT-C013','PT Medika Sentosa Abadi','Distributor','Bandung'),
 ('UAT-C014','RS Citra Keluarga','Hospital','Depok'),
 ('UAT-C015','Klinik Anggrek Medika','Clinic','Bogor'),
 ('UAT-C016','PT Arunika Diagnostika','Distributor','Surabaya'),
 ('UAT-C017','RS Kencana Husada','Hospital','Bekasi'),
 ('UAT-C018','Klinik Sinar Pratama','Clinic','Tangerang'),
 ('UAT-C019','PT Lintas Medis Nusantara','Distributor','Malang'),
 ('UAT-C020','RS Nusa Sehat','Hospital','Bandung'),
 ('UAT-C021','Klinik Sahabat Sehat','Clinic','Jakarta'),
 ('UAT-C022','PT Tirta Meditech','Distributor','Yogyakarta'),
 ('UAT-C023','RS Keluarga Sentosa','Hospital','Semarang'),
 ('UAT-C024','Klinik Mitra Kencana','Clinic','Serang'),
 ('UAT-C025','PT Sinar Alat Kesehatan','Distributor','Denpasar'),
 ('UAT-C026','RS Aditya Husada','Hospital','Surabaya'),
 ('UAT-C027','Klinik Graha Medika','Clinic','Bekasi'),
 ('UAT-C028','PT Samudra Sarana Medis','Distributor','Medan'),
 ('UAT-C029','RS Taman Sehat','Hospital','Depok'),
 ('UAT-C030','Klinik Langit Biru Medika','Clinic','Tangerang Selatan')
)
UPDATE public.customers c SET customer_name=names.label, customer_type=names.cust_type, city=names.city, province=CASE WHEN names.city IN ('Tangerang','Tangerang Selatan','Serang','Cilegon') THEN 'Banten' WHEN names.city IN ('Depok','Bogor','Bekasi','Bandung') THEN 'Jawa Barat' WHEN names.city='Semarang' THEN 'Jawa Tengah' WHEN names.city IN ('Surabaya','Malang') THEN 'Jawa Timur' WHEN names.city='Yogyakarta' THEN 'DI Yogyakarta' WHEN names.city='Denpasar' THEN 'Bali' WHEN names.city='Medan' THEN 'Sumatera Utara' ELSE 'DKI Jakarta' END, updated_at=CURRENT_TIMESTAMP
FROM names WHERE c.customer_code=names.code AND c.customer_name LIKE 'UAT %';

WITH names AS (
 SELECT n, name FROM unnest(ARRAY['Yusuf Amelia', 'Tegar Cahyani', 'Rina Wijaya', 'Sari Rahmawati', 'Dewi Nugraha', 'Bagas Fadli', 'Putri Santoso', 'Fauzan Larasati', 'Raka Kinasih', 'Nabila Mahendra', 'Adi Fauzi', 'Wulan Ramadhan', 'Dian Maulana', 'Yuni Utami', 'Tania Permata', 'Iqbal Siregar', 'Lestari Wibowo', 'Yoga Dewi', 'Rizal Ananda', 'Nina Nugroho', 'Fajar Permadi', 'Indah Hakim', 'Fina Susanti', 'Erika Hartono', 'Lukman Kusuma', 'Anisa Pertiwi', 'Rizka Pratama', 'Citra Putri', 'Agung Maharani', 'Rafi Saputra']::text[]) WITH ORDINALITY t(name,n)
)
UPDATE public.contacts ct SET name=names.name, "position"='Staf Pengadaan', department='Procurement', updated_at=CURRENT_TIMESTAMP
FROM public.customers c, names
WHERE ct.customer_id=c.customer_id AND c.customer_code='UAT-C'||lpad(names.n::text,3,'0')
AND ct.name='UAT Contact '||lpad(names.n::text,3,'0');
UPDATE public.leads l SET company_name=c.customer_name, contact_name=ct.name,
 qualification='Prospek kebutuhan alat kesehatan; data demonstrasi', updated_at=CURRENT_TIMESTAMP
FROM public.customers c LEFT JOIN public.contacts ct ON ct.customer_id=c.customer_id AND ct.is_primary=true
WHERE l.customer_id=c.customer_id AND l.lead_code LIKE 'UAT-L%' AND l.company_name LIKE 'UAT Facility %';
UPDATE public.opportunities o SET name=left('Pengadaan '||COALESCE((
 SELECT p.product_name FROM public.opportunity_items oi JOIN public.products p ON p.product_id=oi.product_id
 WHERE oi.opportunity_id=o.opportunity_id ORDER BY oi.opportunity_item_id LIMIT 1
), 'alat kesehatan')||' - '||c.customer_name,150),
 description='Simulasi penawaran dan pengadaan perangkat medis untuk '||c.customer_name,
 updated_at=CURRENT_TIMESTAMP
FROM public.customers c WHERE o.customer_id=c.customer_id
 AND o.opportunity_code LIKE 'UAT-O%' AND o.name LIKE 'UAT Opportunity %';
UPDATE public.sales_orders so SET notes='Pesanan sintetis terhubung ke '||c.customer_name,
 updated_at=CURRENT_TIMESTAMP
FROM public.quotations q JOIN public.opportunities o ON o.opportunity_id=q.opportunity_id
 JOIN public.customers c ON c.customer_id=o.customer_id
WHERE so.quotation_id=q.quotation_id AND so.sales_order_number LIKE 'UAT-SO%'
 AND so.notes='Synthetic QA order';
UPDATE public.deliveries d SET delivery_address='Alamat simulasi: '||c.customer_name||', '||coalesce(c.city,'Indonesia'),
 updated_at=CURRENT_TIMESTAMP
FROM public.sales_orders so JOIN public.quotations q ON q.quotation_id=so.quotation_id
 JOIN public.opportunities o ON o.opportunity_id=q.opportunity_id JOIN public.customers c ON c.customer_id=o.customer_id
WHERE d.sales_order_id=so.sales_order_id AND d.delivery_number LIKE 'UAT-D%'
 AND d.delivery_address='Synthetic facility';
UPDATE public.service_tickets t SET title=left('Pemeriksaan perangkat - '||c.customer_name,150),
 description='Permintaan pemeriksaan alat kesehatan (data sintetis)'
FROM public.customers c WHERE t.customer_id=c.customer_id AND t.ticket_number LIKE 'UAT-T%'
 AND t.title LIKE 'UAT service ticket %';
UPDATE public.activities a SET subject='Kunjungan dan diskusi kebutuhan alat medis',
 description='Koordinasi dengan tim pengadaan pelanggan (data simulasi)'
WHERE a.subject LIKE 'UAT Activity %';


-- ====================================================================
-- ADD BRANCHES, UAT ROLES, AND NAMED EMPLOYEES (NO NEW GRANTS)
-- ====================================================================

INSERT INTO public.branches(branch_id,branch_code,branch_name,address,phone,status)
SELECT md5('a796.branch.'||n)::uuid,'DEMO26-B'||n,
 (ARRAY['Cabang Bandung','Cabang Semarang','Cabang Depok'])[n],
 'Alamat cabang simulasi - bukan lokasi perusahaan nyata',NULL,'active'
FROM generate_series(1,3) g(n);
INSERT INTO public.roles(role_id,role_name,is_system,description)
VALUES (md5('a796.role.tech')::uuid,'UAT Technician',false,'Peran teknisi sintetis; belum memiliki permission, atur lewat UI'),
 (md5('a796.role.cs')::uuid,'UAT Customer Service',false,'Peran customer service sintetis; belum memiliki permission, atur lewat UI');

WITH n AS (SELECT num, label FROM unnest(ARRAY['Dimas Pratama - Technician', 'Bima Nugraha - Technician', 'Yusuf Hidayat - Technician', 'Tegar Fadli - Technician', 'Rina Mahendra - Technician', 'Sari Larasati - Technician', 'Dewi Kinasih - Customer Service', 'Bagas Santoso - Customer Service', 'Putri Maharani - Sales Executive', 'Fauzan Akbar - Sales Executive', 'Raka Wijaya - Sales Executive', 'Nabila Ramadhani - Sales Executive']::text[]) WITH ORDINALITY x(label,num))
INSERT INTO public.users(user_id,role_id,branch_id,name,email,password,status)
SELECT md5('a796.user.'||lpad(num::text,2,'0'))::uuid,
 CASE WHEN num<=6 THEN md5('a796.role.tech')::uuid
 WHEN num<=8 THEN md5('a796.role.cs')::uuid
 ELSE (SELECT role_id FROM public.roles WHERE role_name='UAT Sales Owner') END,
 CASE WHEN num%3=0 THEN md5('a796.branch.1')::uuid
 WHEN num%3=1 THEN (SELECT branch_id FROM public.branches WHERE branch_code='UAT-B1')
 ELSE (SELECT branch_id FROM public.branches WHERE branch_code='UAT-B2') END,
 label, 'demo.staff'||lpad(num::text,2,'0')||'@example.invalid',
 public.crypt(gen_random_uuid()::text,public.gen_salt('bf')), 'active'
FROM n;
INSERT INTO public.user_roles(user_role_id,user_id,role_id,is_primary)
SELECT md5('a796.userrole.'||lpad(n::text,2,'0'))::uuid,u.user_id,u.role_id,true
FROM generate_series(1,12) g(n)
JOIN public.users u ON u.user_id=md5('a796.user.'||lpad(n::text,2,'0'))::uuid;
INSERT INTO public.workspace_notification_preferences(user_id,crm_status_enabled)
SELECT md5('a796.user.'||lpad(n::text,2,'0'))::uuid,false
FROM generate_series(1,12) g(n);


-- ====================================================================
-- ADD HUMAN-READABLE PRODUCTS, WAREHOUSES AND MATCHING OPENING STOCK
-- ====================================================================

INSERT INTO public.brands(brand_id,brand_name,description,created_by)
SELECT md5('a796.brand.'||n)::uuid,
 (ARRAY['Medivanta (Simulasi)','Auralis Medical (Simulasi)'])[n],
 'Merek fiktif khusus dataset CRM',
 (SELECT user_id FROM public.users WHERE email='uat.user01@example.invalid')
FROM generate_series(1,2) g(n);

WITH n AS (SELECT num,label FROM unnest(ARRAY['Ventilator ICU VC-700', 'Defibrillator AED-90', 'Ultrasound Portable US-300', 'Patient Monitor PM-700', 'Infusion Pump IF-45', 'Syringe Pump SP-25', 'ECG 12 Lead ECG-120', 'Pulse Oximeter OX-30', 'Suction Pump SC-25', 'Nebulizer Compressor NB-10', 'Temperature Probe TP-5', 'SpO2 Sensor Neonatal NS-12']::text[]) WITH ORDINALITY x(label,num))
INSERT INTO public.products(product_id,category_id,brand_id,product_code,product_name,product_type,specification,unit,price,warranty_period,status,created_by,updated_by)
SELECT md5('a796.product.'||lpad(num::text,2,'0'))::uuid,
 (SELECT category_id FROM public.product_categories WHERE category_name =
   CASE WHEN num>10 THEN 'Aksesori Alat Medis' ELSE 'Monitoring dan Terapi' END LIMIT 1),
 md5('a796.brand.'||(1+(num%2)))::uuid,
 'DEMO26-P'||lpad(num::text,3,'0'),label,
 CASE WHEN num>10 THEN 'Accessory' ELSE 'Medical Device' END,
 'Spesifikasi produk sintetis untuk pengujian katalog; bukan klaim perangkat nyata',
 'Unit', (1500000+num*780000)::numeric(18,2),12,'active',
 (SELECT user_id FROM public.users WHERE email='uat.user01@example.invalid'),
 (SELECT user_id FROM public.users WHERE email='uat.user01@example.invalid')
FROM n;
INSERT INTO public.warehouses(warehouse_id,warehouse_code,warehouse_name,address,status,created_by)
SELECT md5('a796.warehouse.'||n)::uuid,'DEMO26-WH'||n,
 (ARRAY['Gudang Demonstrasi Bandung','Gudang Demonstrasi Depok'])[n],
 'Lokasi stok simulasi (bukan gudang operasional)','Active',
 (SELECT user_id FROM public.users WHERE email='uat.user01@example.invalid')
FROM generate_series(1,2) g(n);
INSERT INTO public.inventories(inventory_id,warehouse_id,product_id,quantity,minimum_stock)
SELECT md5('a796.inv.'||lpad(p::text,2,'0')||'.'||w)::uuid,
 md5('a796.warehouse.'||w)::uuid,
 md5('a796.product.'||lpad(p::text,2,'0'))::uuid,
 12+p+w,5 FROM generate_series(1,12) x(p) CROSS JOIN generate_series(1,2) y(w);
INSERT INTO public.stock_movements(stock_movement_id,warehouse_id,product_id,movement_type,quantity,notes,created_by)
SELECT md5('a796.stock.'||lpad(p::text,2,'0')||'.'||w)::uuid,
 md5('a796.warehouse.'||w)::uuid,
 md5('a796.product.'||lpad(p::text,2,'0'))::uuid,
 'opening_stock',12+p+w,'Stok awal produk demo; bukan stok riil',
 (SELECT user_id FROM public.users WHERE email='uat.user01@example.invalid')
FROM generate_series(1,12) x(p) CROSS JOIN generate_series(1,2) y(w);


-- ====================================================================
-- ADD 60 CUSTOMER PROFILES WITH 90 NAMED CONTACTS AND 45 LEADS
-- ====================================================================

WITH catalog(n,label,cust_type,city) AS (VALUES
 (1, 'PT Mentari Alkes Utama Bandung', 'Distributor', 'Bandung'),
 (2, 'RS Widya Sehat Bandung', 'Hospital', 'Bandung'),
 (3, 'Klinik Pratama Harmoni Bandung', 'Clinic', 'Bandung'),
 (4, 'PT Semesta Medis Sejahtera Bandung', 'Distributor', 'Bandung'),
 (5, 'RS Kasih Permata Bandung', 'Hospital', 'Bandung'),
 (6, 'Klinik Keluarga Melati Bandung', 'Clinic', 'Bandung'),
 (7, 'PT Jaya Sarana Diagnostik Bandung', 'Distributor', 'Bandung'),
 (8, 'RS Graha Medika Utama Bandung', 'Hospital', 'Bandung'),
 (9, 'Klinik Cemerlang Sehat Bandung', 'Clinic', 'Bandung'),
 (10, 'PT Aksara Meditek Bandung', 'Distributor', 'Bandung'),
 (11, 'RS Surya Kencana Bandung', 'Hospital', 'Bandung'),
 (12, 'Klinik Madani Medika Bandung', 'Clinic', 'Bandung'),
 (13, 'PT Cakrawala Alkes Bandung', 'Distributor', 'Bandung'),
 (14, 'RS Bhakti Bunda Bandung', 'Hospital', 'Bandung'),
 (15, 'Klinik Bina Husada Bandung', 'Clinic', 'Bandung'),
 (16, 'PT Pilar Solusi Medis Bandung', 'Distributor', 'Bandung'),
 (17, 'RS Cahaya Medika Bandung', 'Hospital', 'Bandung'),
 (18, 'Klinik Damai Sentosa Bandung', 'Clinic', 'Bandung'),
 (19, 'PT Bahari Medika Utama Bandung', 'Distributor', 'Bandung'),
 (20, 'RS Cipta Kasih Bandung', 'Hospital', 'Bandung'),
 (21, 'Klinik Permata Hijau Bandung', 'Clinic', 'Bandung'),
 (22, 'PT Gemilang Instrumen Medis Bandung', 'Distributor', 'Bandung'),
 (23, 'RS Buana Sehat Bandung', 'Hospital', 'Bandung'),
 (24, 'Klinik Citra Keluarga Bandung', 'Clinic', 'Bandung'),
 (25, 'PT Daya Medis Prima Bandung', 'Distributor', 'Bandung'),
 (26, 'RS Sentosa Kencana Bandung', 'Hospital', 'Bandung'),
 (27, 'Klinik Nusa Prima Bandung', 'Clinic', 'Bandung'),
 (28, 'PT Pratama Meditech Abadi Bandung', 'Distributor', 'Bandung'),
 (29, 'RS Bintang Kencana Bandung', 'Hospital', 'Bandung'),
 (30, 'Klinik Arunika Sehat Bandung', 'Clinic', 'Bandung'),
 (31, 'PT Mentari Alkes Utama Surabaya', 'Distributor', 'Surabaya'),
 (32, 'RS Widya Sehat Surabaya', 'Hospital', 'Surabaya'),
 (33, 'Klinik Pratama Harmoni Surabaya', 'Clinic', 'Surabaya'),
 (34, 'PT Semesta Medis Sejahtera Surabaya', 'Distributor', 'Surabaya'),
 (35, 'RS Kasih Permata Surabaya', 'Hospital', 'Surabaya'),
 (36, 'Klinik Keluarga Melati Surabaya', 'Clinic', 'Surabaya'),
 (37, 'PT Jaya Sarana Diagnostik Surabaya', 'Distributor', 'Surabaya'),
 (38, 'RS Graha Medika Utama Surabaya', 'Hospital', 'Surabaya'),
 (39, 'Klinik Cemerlang Sehat Surabaya', 'Clinic', 'Surabaya'),
 (40, 'PT Aksara Meditek Surabaya', 'Distributor', 'Surabaya'),
 (41, 'RS Surya Kencana Surabaya', 'Hospital', 'Surabaya'),
 (42, 'Klinik Madani Medika Surabaya', 'Clinic', 'Surabaya'),
 (43, 'PT Cakrawala Alkes Surabaya', 'Distributor', 'Surabaya'),
 (44, 'RS Bhakti Bunda Surabaya', 'Hospital', 'Surabaya'),
 (45, 'Klinik Bina Husada Surabaya', 'Clinic', 'Surabaya'),
 (46, 'PT Pilar Solusi Medis Surabaya', 'Distributor', 'Surabaya'),
 (47, 'RS Cahaya Medika Surabaya', 'Hospital', 'Surabaya'),
 (48, 'Klinik Damai Sentosa Surabaya', 'Clinic', 'Surabaya'),
 (49, 'PT Bahari Medika Utama Surabaya', 'Distributor', 'Surabaya'),
 (50, 'RS Cipta Kasih Surabaya', 'Hospital', 'Surabaya'),
 (51, 'Klinik Permata Hijau Surabaya', 'Clinic', 'Surabaya'),
 (52, 'PT Gemilang Instrumen Medis Surabaya', 'Distributor', 'Surabaya'),
 (53, 'RS Buana Sehat Surabaya', 'Hospital', 'Surabaya'),
 (54, 'Klinik Citra Keluarga Surabaya', 'Clinic', 'Surabaya'),
 (55, 'PT Daya Medis Prima Surabaya', 'Distributor', 'Surabaya'),
 (56, 'RS Sentosa Kencana Surabaya', 'Hospital', 'Surabaya'),
 (57, 'Klinik Nusa Prima Surabaya', 'Clinic', 'Surabaya'),
 (58, 'PT Pratama Meditech Abadi Surabaya', 'Distributor', 'Surabaya'),
 (59, 'RS Bintang Kencana Surabaya', 'Hospital', 'Surabaya'),
 (60, 'Klinik Arunika Sehat Surabaya', 'Clinic', 'Surabaya')
)
INSERT INTO public.customers(customer_id,user_id,customer_code,customer_name,customer_type,email,city,province,status,address,created_by,updated_by)
SELECT md5('a796.customer.'||lpad(n::text,3,'0'))::uuid,
 CASE WHEN n%4=0 THEN md5('a796.user.09')::uuid ELSE
  (SELECT user_id FROM public.users WHERE email=CASE WHEN n%2=0 THEN 'uat.user02@example.invalid' ELSE 'uat.user01@example.invalid' END) END,
 'DEMO26-C'||lpad(n::text,3,'0'),label,cust_type,
 'demo.customer'||lpad(n::text,3,'0')||'@example.invalid',city,
 CASE WHEN city='Bandung' THEN 'Jawa Barat' ELSE 'Jawa Timur' END,'active',
 'Alamat pelanggan fiktif, '||city,
 (SELECT user_id FROM public.users WHERE email='uat.user01@example.invalid'),
 (SELECT user_id FROM public.users WHERE email='uat.user01@example.invalid')
FROM catalog;

WITH n AS (SELECT g.n,
 (ARRAY['Rizky', 'Nadia', 'Rani', 'Andi', 'Farhan', 'Sinta', 'Ayu', 'Rudi', 'Dimas', 'Bima', 'Yusuf', 'Tegar', 'Rina', 'Sari', 'Dewi', 'Bagas', 'Putri', 'Fauzan', 'Raka', 'Nabila', 'Adi', 'Wulan', 'Dian', 'Yuni', 'Tania', 'Iqbal', 'Lestari', 'Yoga', 'Rizal', 'Nina', 'Fajar', 'Indah', 'Fina', 'Erika', 'Lukman', 'Anisa', 'Rizka', 'Citra', 'Agung', 'Rafi', 'Ratna', 'Niko', 'Fikri', 'Ria', 'Aldo', 'Salsa', 'Mira', 'Dito', 'Gita', 'Bunga', 'Wahyu', 'Yusuf']::text[])[(1+((g.n-1)%52))] || ' ' ||
 (ARRAY['Pratama', 'Putri', 'Maharani', 'Saputra', 'Hidayat', 'Amelia', 'Cahyani', 'Wijaya', 'Rahmawati', 'Nugraha', 'Fadli', 'Santoso', 'Larasati', 'Kinasih', 'Mahendra', 'Fauzi', 'Ramadhan', 'Maulana', 'Utami', 'Permata', 'Siregar', 'Wibowo', 'Dewi', 'Ananda', 'Nugroho', 'Permadi', 'Hakim', 'Susanti', 'Hartono', 'Kusuma', 'Pertiwi']::text[])[(1+(((g.n-1)*7)%31))] AS fullname
 FROM generate_series(1,90) g(n))
INSERT INTO public.contacts(contact_id,customer_id,name,"position",department,email,contact_type,is_primary,created_by)
SELECT md5('a796.contact.'||lpad(n::text,3,'0'))::uuid,
 md5('a796.customer.'||lpad((CASE WHEN n<=60 THEN n ELSE n-60 END)::text,3,'0'))::uuid,
 fullname,
 CASE WHEN n<=60 THEN 'Kepala Pengadaan' ELSE 'Staf Elektromedis' END,
 CASE WHEN n<=60 THEN 'Procurement' ELSE 'Biomedical Engineering' END,
 'demo.contact'||lpad(n::text,3,'0')||'@example.invalid',
 CASE WHEN n<=60 THEN 'Primary' ELSE 'Technical' END,
 n<=60,(SELECT user_id FROM public.users WHERE email='uat.user01@example.invalid')
FROM n;
INSERT INTO public.leads(lead_id,user_id,customer_id,lead_code,company_name,contact_name,email,source,status,qualification,created_by)
SELECT md5('a796.lead.'||lpad(n::text,3,'0'))::uuid,c.user_id,c.customer_id,
 'DEMO26-L'||lpad(n::text,3,'0'),c.customer_name,ct.name,
 'demo.lead'||lpad(n::text,3,'0')||'@example.invalid',
 CASE WHEN n%3=0 THEN 'Referral' WHEN n%3=1 THEN 'Website' ELSE 'Exhibition' END,
 CASE WHEN n%4=0 THEN 'New' ELSE 'Qualified' END,
 'Prospek pengadaan alat kesehatan untuk pengujian sistem CRM',
 c.user_id
FROM generate_series(1,45) g(n)
JOIN public.customers c ON c.customer_id=md5('a796.customer.'||lpad(n::text,3,'0'))::uuid
JOIN public.contacts ct ON ct.contact_id=md5('a796.contact.'||lpad(n::text,3,'0'))::uuid;


-- ====================================================================
-- ADD 240 READABLE OPPORTUNITIES, 200 QUOTATIONS, 180 ORDERS
-- ====================================================================

INSERT INTO public.opportunities(opportunity_id,customer_id,lead_id,user_id,opportunity_code,name,description,stage,estimated_value,expected_close_date,status,created_by,updated_by)
SELECT md5('a796.opp.'||lpad(n::text,4,'0'))::uuid,
 c.customer_id,
 CASE WHEN ((n-1)%60)+1<=45 THEN md5('a796.lead.'||lpad((((n-1)%60)+1)::text,3,'0'))::uuid ELSE NULL END,
 c.user_id,
 'DEMO26-O'||lpad(n::text,4,'0'),
 left('Pengadaan '||p.product_name||' - '||c.customer_name,150),
 'Simulasi siklus penjualan dan kebutuhan alat medis di '||c.customer_name,
 CASE WHEN n<=180 THEN 'Closed Won' WHEN n%3=0 THEN 'Negotiation' WHEN n%3=1 THEN 'Qualification' ELSE 'Proposal' END,
 p.price * (1+(n%3)),
 CURRENT_DATE + ((n%75)+5),
 CASE WHEN n<=180 THEN 'Won' ELSE 'Open' END,
 c.user_id,c.user_id
FROM generate_series(1,240) g(n)
JOIN public.customers c ON c.customer_id=md5('a796.customer.'||lpad((((n-1)%60)+1)::text,3,'0'))::uuid
JOIN public.products p ON p.product_id=md5('a796.product.'||lpad((((n-1)%12)+1)::text,2,'0'))::uuid;
INSERT INTO public.opportunity_items(opportunity_item_id,opportunity_id,product_id,quantity,estimated_price,notes)
SELECT md5('a796.oppitem.'||lpad(n::text,4,'0'))::uuid,
 md5('a796.opp.'||lpad(n::text,4,'0'))::uuid,
 md5('a796.product.'||lpad((((n-1)%12)+1)::text,2,'0'))::uuid,
 1+(n%3),p.price,'Unit perangkat untuk penawaran demo'
FROM generate_series(1,240) g(n)
JOIN public.products p ON p.product_id=md5('a796.product.'||lpad((((n-1)%12)+1)::text,2,'0'))::uuid;
INSERT INTO public.quotations(quotation_id,opportunity_id,quotation_number,quotation_date,valid_until,subtotal,discount,tax,total_amount,discount_type,tax_type,status,notes,created_by,updated_by)
SELECT md5('a796.quote.'||lpad(n::text,4,'0'))::uuid,
 o.opportunity_id,'DEMO26-Q'||lpad(n::text,4,'0'),
 CURRENT_DATE-((n%28)+1),CURRENT_DATE+30,
 o.estimated_value,0,0,o.estimated_value,'amount','amount',
 CASE WHEN n<=180 THEN 'approved' WHEN n%2=0 THEN 'sent' ELSE 'draft' END,
 'Penawaran simulasi: '||left(o.name,200),o.user_id,o.user_id
FROM generate_series(1,200) g(n)
JOIN public.opportunities o ON o.opportunity_id=md5('a796.opp.'||lpad(n::text,4,'0'))::uuid;
INSERT INTO public.quotation_items(quotation_item_id,quotation_id,product_id,quantity,unit_price,discount,subtotal)
SELECT md5('a796.quoteitem.'||lpad(n::text,4,'0'))::uuid,
 md5('a796.quote.'||lpad(n::text,4,'0'))::uuid,
 oi.product_id,oi.quantity,oi.estimated_price,0,oi.quantity*oi.estimated_price
FROM generate_series(1,200) g(n)
JOIN public.opportunity_items oi ON oi.opportunity_id=md5('a796.opp.'||lpad(n::text,4,'0'))::uuid;
INSERT INTO public.sales_orders(sales_order_id,quotation_id,sales_order_number,order_date,subtotal,tax,total_amount,status,notes,created_by,updated_by)
SELECT md5('a796.so.'||lpad(n::text,4,'0'))::uuid,q.quotation_id,
 'DEMO26-SO'||lpad(n::text,4,'0'),CURRENT_DATE-((n%20)+1),
 q.subtotal,q.tax,q.total_amount,'confirmed',
 'Pesanan simulasi untuk '||left(o.name,200),o.user_id,o.user_id
FROM generate_series(1,180) g(n)
JOIN public.quotations q ON q.quotation_id=md5('a796.quote.'||lpad(n::text,4,'0'))::uuid
JOIN public.opportunities o ON o.opportunity_id=q.opportunity_id;
INSERT INTO public.sales_order_items(sales_order_item_id,sales_order_id,product_id,quantity,unit_price,discount,subtotal)
SELECT md5('a796.soitem.'||lpad(n::text,4,'0'))::uuid,
 md5('a796.so.'||lpad(n::text,4,'0'))::uuid,
 qi.product_id,qi.quantity,qi.unit_price,0,qi.subtotal
FROM generate_series(1,180) g(n)
JOIN public.quotation_items qi ON qi.quotation_id=md5('a796.quote.'||lpad(n::text,4,'0'))::uuid;


-- ====================================================================
-- ADD INVOICES, PENDING DELIVERIES, TECHNICAL TICKETS AND ACTIVITIES
-- ====================================================================

INSERT INTO public.invoices(invoice_id,sales_order_id,invoice_number,invoice_date,due_date,subtotal,tax,discount,total_amount,status,created_by,updated_by)
SELECT md5('a796.invoice.'||lpad(n::text,3,'0'))::uuid,so.sales_order_id,
 'DEMO26-INV'||lpad(n::text,3,'0'),CURRENT_DATE-(n%24),CURRENT_DATE+30,
 so.subtotal,0,0,so.total_amount,
 CASE WHEN n%4=0 THEN 'Paid' WHEN n%4=1 THEN 'Sent' ELSE 'Draft' END,
 so.created_by,so.updated_by
FROM generate_series(1,36) g(n)
JOIN public.sales_orders so ON so.sales_order_id=md5('a796.so.'||lpad(n::text,4,'0'))::uuid;
INSERT INTO public.invoice_items(invoice_item_id,invoice_id,product_id,quantity,unit_price,discount,subtotal)
SELECT md5('a796.invoiceitem.'||lpad(n::text,3,'0'))::uuid,
 md5('a796.invoice.'||lpad(n::text,3,'0'))::uuid,si.product_id,si.quantity,si.unit_price,0,si.subtotal
FROM generate_series(1,36) g(n)
JOIN public.sales_order_items si ON si.sales_order_id=md5('a796.so.'||lpad(n::text,4,'0'))::uuid;
-- Deliveries intentionally have no dispatch items and no stock changes.
INSERT INTO public.deliveries(delivery_id,sales_order_id,delivery_number,delivery_date,delivery_address,status,notes,created_by,updated_by)
SELECT md5('a796.delivery.'||lpad(n::text,3,'0'))::uuid,
 so.sales_order_id,'DEMO26-D'||lpad(n::text,3,'0'),CURRENT_DATE+(n%20)+1,
 'Alamat pelanggan simulasi: '||c.customer_name||', '||c.city,
 'pending','Belum dikirim; stok tidak dipotong otomatis oleh dataset',so.created_by,so.updated_by
FROM generate_series(1,20) g(n)
JOIN public.sales_orders so ON so.sales_order_id=md5('a796.so.'||lpad(n::text,4,'0'))::uuid
JOIN public.quotations q ON q.quotation_id=so.quotation_id
JOIN public.opportunities o ON o.opportunity_id=q.opportunity_id
JOIN public.customers c ON c.customer_id=o.customer_id;
-- Tickets use existing A794 serials linked to customers (4 of 5 by default).
-- Rotate over the actual eligible count; do not silently lose tickets when a serial is unassigned.
INSERT INTO public.service_tickets(service_ticket_id,customer_id,serial_number_id,user_id,ticket_number,title,description,priority,status,opened_at,created_by)
SELECT md5('a796.ticket.'||lpad(n::text,3,'0'))::uuid,s.customer_id,s.serial_number_id,
 md5('a796.user.'||lpad((((n-1)%6)+1)::text,2,'0'))::uuid,
 'DEMO26-T'||lpad(n::text,3,'0'),
 left((ARRAY['Sensor monitor tidak terbaca','Perangkat perlu kalibrasi','Pemeriksaan alarm mesin','Pengecekan konektor alat'])[1+((n-1)%4)]
 ||' - '||c.customer_name,150),
 'Laporan gangguan alat fiktif; tidak terkait pasien nyata',
 CASE WHEN n%3=0 THEN 'high' WHEN n%3=1 THEN 'low' ELSE 'medium' END,
 'open',CURRENT_TIMESTAMP-((n%8)||' days')::interval,
 md5('a796.user.'||lpad((((n-1)%6)+1)::text,2,'0'))::uuid
FROM generate_series(1,12) g(n)
JOIN (SELECT serial_number_id,customer_id,
             row_number() OVER (ORDER BY serial_number) AS seq,
             count(*) OVER () AS eligible_serial_count
      FROM public.serial_numbers
      WHERE serial_number LIKE 'UAT-SN%' AND customer_id IS NOT NULL) s
 ON s.seq=1+((n-1)%s.eligible_serial_count)
JOIN public.customers c ON c.customer_id=s.customer_id;
-- Activity items are linked to new accounts and same new customer/lead/opportunity chain.
INSERT INTO public.activities(activity_id,user_id,customer_id,lead_id,opportunity_id,activity_type,subject,description,activity_date,created_by)
SELECT md5('a796.activity.'||lpad(n::text,3,'0'))::uuid,o.user_id,o.customer_id,o.lead_id,o.opportunity_id,
 CASE WHEN n%3=0 THEN 'Meeting' WHEN n%3=1 THEN 'Call' ELSE 'Follow Up' END,
 left((ARRAY['Diskusi kebutuhan alat kesehatan','Demo produk dengan tim pengadaan','Follow up proposal harga'])[1+((n-1)%3)]
 ||' - '||c.customer_name,150),
 'Aktivitas penjualan sintetis untuk kebutuhan UAT', CURRENT_TIMESTAMP-((n%15)||' days')::interval,o.user_id
FROM generate_series(1,40) g(n)
JOIN public.opportunities o ON o.opportunity_id=md5('a796.opp.'||lpad(n::text,4,'0'))::uuid
JOIN public.customers c ON c.customer_id=o.customer_id;


-- ====================================================================
-- ASSERT INSERT COUNTS, RELATIONAL PARITY, AUTHORIZATION SAFETY, AND STOCK BALANCE
-- ====================================================================

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

COMMIT;
SELECT 'A796_HUMANIZED_EXPANSION_COMMITTED' AS result;
