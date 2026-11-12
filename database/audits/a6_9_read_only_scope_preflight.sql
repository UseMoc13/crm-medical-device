-- A6.9 READ ONLY. No customer or employee details returned.
SELECT COALESCE(rp.data_scope, '(NULL / not configured)') AS scope,
       count(*) AS role_view_grants
FROM role_permissions rp
JOIN permissions p ON p.permission_id = rp.permission_id
WHERE p.module = 'crm_page' AND p.action = 'view'
GROUP BY COALESCE(rp.data_scope, '(NULL / not configured)')
ORDER BY scope;

SELECT p.permission_name, COUNT(*) FILTER (WHERE rp.data_scope IS NULL) AS unconfigured_grants,
       COUNT(*) AS total_grants
FROM role_permissions rp
JOIN permissions p ON p.permission_id = rp.permission_id
WHERE p.module = 'crm_page' AND p.action = 'view'
GROUP BY p.permission_name
ORDER BY p.permission_name;

-- Important: source table assignment columns, not a claim they map to all scopes.
SELECT table_name, column_name
FROM information_schema.columns
WHERE table_schema = 'public'
  AND table_name IN ('customers','leads','opportunities','activities',
    'service_tickets','installations','maintenance_schedules')
  AND column_name IN ('user_id','branch_id')
ORDER BY table_name, column_name;
