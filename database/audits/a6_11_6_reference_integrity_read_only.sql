-- A6.11.6 reference integrity overview (READ ONLY).
-- Foreign keys may already exist, but existence != access authorization.
SELECT 'leads.customer_id' AS reference,
       count(*) FILTER (WHERE l.customer_id IS NOT NULL) AS assigned,
       count(*) FILTER (WHERE l.customer_id IS NOT NULL AND c.customer_id IS NULL) AS missing_target
FROM leads l LEFT JOIN customers c ON c.customer_id = l.customer_id
UNION ALL
SELECT 'opportunities.customer_id',
       count(*) FILTER (WHERE o.customer_id IS NOT NULL),
       count(*) FILTER (WHERE o.customer_id IS NOT NULL AND c.customer_id IS NULL)
FROM opportunities o LEFT JOIN customers c ON c.customer_id = o.customer_id
UNION ALL
SELECT 'opportunities.lead_id',
       count(*) FILTER (WHERE o.lead_id IS NOT NULL),
       count(*) FILTER (WHERE o.lead_id IS NOT NULL AND l.lead_id IS NULL)
FROM opportunities o LEFT JOIN leads l ON l.lead_id = o.lead_id
UNION ALL
SELECT 'quotations.opportunity_id',
       count(*) FILTER (WHERE q.opportunity_id IS NOT NULL),
       count(*) FILTER (WHERE q.opportunity_id IS NOT NULL AND o.opportunity_id IS NULL)
FROM quotations q LEFT JOIN opportunities o ON o.opportunity_id = q.opportunity_id;
