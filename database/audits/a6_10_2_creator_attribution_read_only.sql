-- Read-only audit. Run in PostgreSQL after the migration.
SELECT
    table_name,
    COUNT(*) FILTER (WHERE column_name = 'created_by') AS has_created_by,
    COUNT(*) FILTER (WHERE column_name = 'updated_by') AS has_updated_by
FROM information_schema.columns
WHERE table_schema = 'public'
  AND table_name IN (
    'brands','product_categories','products','warehouses','customers','contacts',
    'leads','activities','opportunities','quotations','sales_orders','contracts',
    'deliveries','installations','warranties','service_tickets',
    'maintenance_schedules','invoices','serial_numbers','stock_movements','announcements'
  )
GROUP BY table_name
ORDER BY table_name;
