# A7.8.1 Integration & UAT Readiness — Scope & Safety

Source basis: CRM `Big-Changes` routes/web.php reviewed November 2026. UAT route manifest references names and methods for actual Laravel route registration. Nested Workspace routes are intentionally outside initial core CRM route list; they retain separate authorization controls.

## Gates

1. **Static and unit**: route/table/relationship manifest consistency; never boot CRM database.
2. **Routes-only**: boot Laravel application, inspect named route metadata; no business HTTP request and no DB query intended.
3. **Catalog read-only (opt-in)**: restricted to `APP_ENV=local`, `DB_CONNECTION=pgsql`, loopback DB host; `SET TRANSACTION READ ONLY`; `pg_catalog.pg_tables`/`pg_constraint`/`pg_class`/`pg_attribute`; transaction rolled back. This reads schema metadata, not business rows.
4. **Human UAT:** wait for A7.9 isolated test dataset and explicit approval. Use separate non-production instance, do not run destructive tests against existing records.

## Known limits

- Presence of named route does not establish correct authorization or business workflow behavior.
- Foreign-key metadata does not establish controller transaction correctness.
- When FK hint is absent, investigate (do not automatically create FK).
- Data counts were intentionally omitted; the previous PostgreSQL estimated rows were 0 even though business references existed.
- Privacy HOLDs: historical/invoice reporting access pilots require explicit review; Announcement attachments on public storage require policy choice and HTTP-access validation. No privilege grants/flag changes in this stage.
- A7.7.7 selected regression passed 90 tests, 442 assertions, 1 Windows symlink test warning; not full release certification.
