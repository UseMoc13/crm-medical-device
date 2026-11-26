# A7.10.7.1 — Read-only PostgreSQL endpoint diagnosis

A7.10.7 refuses `workspace_audit.view` repair if `inet_server_addr()` is neither `127.0.0.1` nor `::1`. This package does not bypass that guard and never changes database records.

Extract into Laravel repository root, then run:

```powershell
php tools/a71071_db_endpoint_diagnose.php
```

Share the `APP_ENV`, configured host, actual server address/class, database, and permission result with your developer. This report never displays a password, database URL, or user records. If the server address is non-loopback, verify whether PostgreSQL is running behind Docker/VM/SSH tunnel or on a remote server. Do not run A7.10.7 `--apply` until the actual endpoint is identified and an appropriate safety policy is established.

**Do not** run `migrate:fresh`, reset seeders, or disable A7.10.7 safeguards.
