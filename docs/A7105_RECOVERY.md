# A7.10.5 — Safe Role-Aware UI Rebuild (A7103 / A7104 recovery)

## Why the previous fix stopped

The A7103 installer inserted 579 Blade guards into 128 files. The defect is broader than missing whitespace: crossing/nested insertion offsets can create incorrectly ordered Blade `@if`/`@endif` directives. A7104 repaired closing-marker adjacency but intentionally refused to modify any file when `branches/index.blade.php` still failed compiled PHP syntax validation. That fail-closed behavior was correct.

`php artisan view:cache` reporting success does **not** guarantee all generated compiled PHP passes `php -l` or all routes render successfully.

## Safe repair strategy

1. Use the **original source backup** from A7103: `storage/app/private/a7103_crud_ui_backups/20261126-050227-d28dcbfc`.
2. Check SHA-256 of the original backup and every current A7103 patched view. **Do not overwrite views modified since A7103.** If an SHA mismatch occurs, STOP; preserve current changes and investigate.
3. Reconstruct guards from the clean source, using ordered opening/closing boundary events rather than repeated text insertions with offset ambiguity. Whitespace around Blade directives is explicit. GET filter forms are not unnecessarily guarded. Independent Workspace features are not reclassified as CRM permissions.
4. Compile **all 128 proposed Blade views** via the Laravel compiler and run `php -l` on each compiled PHP string, before any source writes. Any failure aborts the whole operation.
5. Snapshot currently patched views, then apply the validated rebuilt versions. No database, role, grant, controller or route changes.

## Commands — PowerShell in CRM root

Extract the ZIP, then:

```powershell
php tools/a7105_source_check.php
php tools/a7105_transform_smoke.php
php tools/a7105_rebuild_role_ui.php --check
```

**Only if** the last command returns `A7105_SAFE_REBUILD_READY`, and the manifest identifies the expected 128 files, run:

```powershell
php tools/a7105_rebuild_role_ui.php --apply
php tools/a7105_rebuild_role_ui.php --check
php artisan view:clear
php artisan view:cache
```

Successful `--apply` prints `A7105_ROLE_UI_REBUILT_AND_VALIDATED`, plus a snapshot path under `storage/app/private/a7105_rebuild_backups/`.

Revisit **Roles > Edit > Role Details**, and then **Branches**, **Teams**, **Products**, **Customers**, **Delivery**, and **Service Tickets** with (a) Super Admin and (b) a synthetic role without Delete grants. Verify New/Edit/Delete visibility and direct unauthorized actions still return HTTP 403. Visibility controls do not replace backend authorization.

## Emergency alternative — restore UI immediately (no A7105)

The original A7103 backup is checksum-verified and can undo all 128 guarded templates:

```powershell
php tools/a7103_crud_ui_installer.php --rollback=20261126-050227-d28dcbfc
php artisan view:clear
```

This disables **only the newly added A7103 role-aware UI hiding**, while retaining earlier server-side RBAC, roles, customer data and PostgreSQL. This is an **alternative**, not a prerequisite to A7105. Do **not** run it before `a7105_rebuild_role_ui.php --check`, because A7105 expects the current A7103-patched files.

## Undo a successful A7105 rebuild

```powershell
php tools/a7105_rebuild_role_ui.php --restore-original
php artisan view:clear
```

This restores the clean original pre-A7103 views, not the broken generated guards. It refuses to overwrite any file modified after the A7105 rebuild. Avoid rerunning `a7103_crud_ui_installer.php --apply` or A7104 `--apply`; those prior patchers may reintroduce the problem.

## Important

- This tool is source-only and does not touch PostgreSQL or stored permissions.
- This tool is intentionally pinned to the **specific A7103 backup ID** in the user's log. If that backup is missing or any hash differs, it **stops** rather than guessing which source was safe.
- Synthetic browser UAT is still needed. Existing unit tests only verify permission logic, not every UI state.
