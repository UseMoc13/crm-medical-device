-- A6.10.4 read-only verification: Query Builder attribution.
-- Query each only after the corresponding action has been tested.
SELECT 'sales_orders' AS source, sales_order_id::text AS id, created_by::text, updated_by::text FROM sales_orders ORDER BY created_at DESC LIMIT 5;
SELECT 'serial_numbers' AS source, serial_number_id::text AS id, created_by::text, updated_by::text FROM serial_numbers ORDER BY created_at DESC LIMIT 5;
SELECT 'warranties' AS source, warranty_id::text AS id, created_by::text, updated_by::text FROM warranties ORDER BY created_at DESC LIMIT 5;
SELECT 'stock_movements' AS source, stock_movement_id::text AS id, created_by::text, updated_by::text FROM stock_movements ORDER BY created_at DESC LIMIT 5;
SELECT 'quotations' AS source, quotation_id::text AS id, created_by::text, updated_by::text FROM quotations ORDER BY updated_at DESC LIMIT 5;
