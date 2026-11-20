# A7.7.6.1 — Sales Order CSV Multi-Batch Reliability

## Background

A7.7.5.2 replaced an Eloquent `cursor()` iterator, which cannot eager load the quotation/opportunity/customer chain, with `orderByDesc('sales_order_id')->lazy(200)` in the CSV branch of `SalesOrderController`. The first test covered only eight Sales Orders (one batch). The remaining functional regression risk is incomplete/double-counted records at batch boundaries and query counts increasing linearly with records.

## Coverage

- An independent SQLite `:memory:` connection with four minimal tables and 405 distinct synthetic relation chains.
- Identical `created_at` timestamps, to exercise the secondary `sales_order_id` sort key.
- 405 CSV records, exactly one header, no duplicated or skipped IDs across all three batches (200 + 200 + 5).
- SELECT query budget <=16 for 405 records; >=10 to detect missing eager load, with 4 SELECTs per batch expected for the current design.
- Customer and notes formula payloads remain text-escaped; monetary total remains interpretable as numeric.
- Filtered CSV (`status=pending`) has exactly four records; query budget <=5.
- SQL writes forbidden during measured export, separate from test-fixture inserts.

## Limitations and handling

This is a correctness and N+1 regression test, **not** a latency SLO or production benchmark. The exact number of queries may change when Laravel's eager-loading implementation or authorized business requirements change: investigate failed budgets rather than indiscriminately raising them.

Tests create synthetic SQLite rows only after confirming `APP_ENV=testing` and switching the default connection to the isolated memory connection. They restore the prior default connection in tearDown. Fixture assertions occur only after the connection switch. No migrations or application data writes are performed.

After final reset, insert a separate PostgreSQL test dataset and rerun planner/latency measurements in A7.9. Never import this test's synthetic rows into business databases.

## Next

A7.7.6.2: evaluate pagination limits and expensive dashboard/report paths. A7.7.7: run broader security regression. Preserve A7.5 historical HOLD and A7.6 privacy policy HOLD until decisions are documented.
