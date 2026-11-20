# A7.7.5 PostgreSQL Performance Preflight

No installer. Add files to the Laravel root. Run PHP CLI with `APP_ENV=local` and existing PostgreSQL `.env` connection. **Confirm DB connection targets a database authorized for read-only analysis before running.** No migrations, VACUUM, ANALYZE, EXPLAIN ANALYZE, indexes or data mutations.

## Commands

```powershell
php tools/a775_performance_policy_test.php
php tools/a775_pg_performance.php --limit=20
```

The script reads PostgreSQL's historical, approximate `pg_stat_user_tables` plus `pg_indexes` counts using a `READ ONLY` transaction. It prints **table names and approximate counts**, not customer rows. Stats are cumulative since the server's last statistics reset, influenced by caching, work patterns, and table size; small tables generally benefit from sequential scans. A flagged table is **not evidence** that it needs a new index. After gathering results, choose actual slow application queries and examine representative `EXPLAIN (FORMAT JSON)` in a separate reviewed stage. No index DDL without backups and approval.

This preflight is scoped to table statistics; it does not measure Laravel request latency, SQL response time, locks, disk I/O, autovacuum health or query plans. The command is not a benchmark.
