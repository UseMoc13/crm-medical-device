-- A6.11.4 READ ONLY: inspect explicit View scopes before enabling pilots.
SELECT r.role_name, p.permission_name, rp.data_scope, COUNT(*) AS grants
FROM role_permissions rp
JOIN roles r ON r.role_id = rp.role_id
JOIN permissions p ON p.permission_id = rp.permission_id
WHERE p.permission_name IN ('crm_page.service_tickets.view', 'crm_page.maintenance.view')
GROUP BY r.role_name, p.permission_name, rp.data_scope
ORDER BY r.role_name, p.permission_name, rp.data_scope;

-- Data assignment distribution (NULL assignees can disappear in Assigned/Branch scopes).
SELECT 'service_tickets' AS module, COUNT(*) AS total_records,
       COUNT(*) FILTER (WHERE user_id IS NULL) AS unassigned_records
FROM service_tickets
UNION ALL
SELECT 'maintenance_schedules', COUNT(*),
       COUNT(*) FILTER (WHERE user_id IS NULL)
FROM maintenance_schedules;
