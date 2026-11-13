-- A6.11 Customer-only scope preflight (READ ONLY).
-- 1. Review explicit View grants (NULL means not configured, NOT all).
SELECT r.role_name, rp.data_scope, COUNT(*) AS grants
FROM role_permissions rp
JOIN roles r ON r.role_id = rp.role_id
JOIN permissions p ON p.permission_id = rp.permission_id
WHERE p.permission_name = 'crm_page.customers.view'
GROUP BY r.role_name, rp.data_scope
ORDER BY r.role_name, rp.data_scope;

-- 2. Review Customers missing owner and orphaned owner references.
SELECT
    COUNT(*) AS total_customers,
    COUNT(*) FILTER (WHERE user_id IS NULL) AS unassigned_customers,
    COUNT(*) FILTER (WHERE user_id IS NOT NULL) AS assigned_customers
FROM customers;

-- 3. List users missing a branch (branch scope for these users denies access).
SELECT user_id, name, branch_id
FROM users
WHERE branch_id IS NULL
ORDER BY name;
