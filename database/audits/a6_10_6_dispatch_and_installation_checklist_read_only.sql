-- A6.10.6 read-only verification. No historic attribution backfill.
-- Query A: Recent automatic stock dispatches.
SELECT sm.stock_movement_id,
       sm.reference_id AS delivery_id,
       sm.created_at,
       sm.created_by,
       creator.name AS created_by_name,
       sm.updated_by,
       sm.movement_type,
       sm.quantity,
       sm.notes
FROM stock_movements sm
LEFT JOIN users creator ON creator.user_id = sm.created_by
WHERE sm.reference_type = 'delivery'
  AND sm.movement_type = 'stock_out'
ORDER BY sm.created_at DESC
LIMIT 20;

-- Query B: Recent installation checklist activity.
SELECT ic.checklist_id,
       ic.installation_id,
       ic.item_name,
       ic.is_completed,
       ic.completed_at,
       ic.created_by,
       creator.name AS created_by_name,
       ic.updated_by,
       updater.name AS updated_by_name,
       ic.completed_by,
       completer.name AS completed_by_name
FROM installation_checklists ic
LEFT JOIN users creator ON creator.user_id = ic.created_by
LEFT JOIN users updater ON updater.user_id = ic.updated_by
LEFT JOIN users completer ON completer.user_id = ic.completed_by
ORDER BY ic.completed_at DESC NULLS LAST, ic.installation_id
LIMIT 30;
