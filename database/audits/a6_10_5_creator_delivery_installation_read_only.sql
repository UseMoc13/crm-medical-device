-- A6.10.5 READ ONLY — Run only after migration A6.10.2.
-- Check users on the latest Delivery/Installation/Stock Movement rows.
-- Older rows may legitimately be NULL, and raw-DB writes may bypass Eloquent events.

SELECT 'deliveries' AS source,
       d.delivery_number AS reference,
       d.created_at,
       d.created_by,
       c.name AS creator_name,
       d.updated_by,
       u.name AS updater_name
FROM deliveries d
LEFT JOIN users c ON c.user_id = d.created_by
LEFT JOIN users u ON u.user_id = d.updated_by
ORDER BY d.created_at DESC
LIMIT 10;

SELECT 'installations' AS source,
       i.installation_id::text AS reference,
       i.created_at,
       i.created_by,
       c.name AS creator_name,
       i.updated_by,
       u.name AS updater_name
FROM installations i
LEFT JOIN users c ON c.user_id = i.created_by
LEFT JOIN users u ON u.user_id = i.updated_by
ORDER BY i.created_at DESC
LIMIT 10;

SELECT 'stock_movements' AS source,
       s.stock_movement_id::text AS reference,
       s.created_at,
       s.created_by,
       c.name AS creator_name,
       s.updated_by,
       u.name AS updater_name
FROM stock_movements s
LEFT JOIN users c ON c.user_id = s.created_by
LEFT JOIN users u ON u.user_id = s.updated_by
ORDER BY s.created_at DESC
LIMIT 10;
