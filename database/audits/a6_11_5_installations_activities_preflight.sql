-- A6.11.5 READ ONLY. Review assignments and explicit VIEW scope grants before enabling flags.
SELECT p.permission_name, rp.data_scope, count(*) AS grants
FROM role_permissions rp JOIN permissions p ON p.permission_id = rp.permission_id
WHERE p.permission_name IN ('crm_page.installations.view','crm_page.activities.view')
GROUP BY p.permission_name, rp.data_scope ORDER BY p.permission_name, rp.data_scope;

SELECT 'installations' AS subject, COUNT(*) AS records, COUNT(*) FILTER (WHERE user_id IS NULL) AS unassigned FROM installations
UNION ALL SELECT 'activities', COUNT(*), COUNT(*) FILTER (WHERE user_id IS NULL) FROM activities;
