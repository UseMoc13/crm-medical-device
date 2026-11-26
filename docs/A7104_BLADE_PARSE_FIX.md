# A7.10.4 — Emergency fix for A7103 Blade ParseError

## Observed issue

After installing A7.10.3 (128 Blade files, 579 auto-generated guards), `/roles/{uuid}` raises:

`ParseError: unexpected end of file, expecting "elseif" or "else" or "endif"`, at `resources/views/roles/show.blade.php`.

**Root cause (reproduced with Laravel 12.69.2 BladeCompiler):** injected guard ending is adjacent to an existing Blade directive:

```blade
@unless($isProtected){{-- A7103:roles.edit --}}@if(\App\Support\CrmViewAction::allows('roles.edit'))
    <a href="...">Edit Role</a>
@endif{{-- /A7103 --}}@endunless
```

`@endunless` is not compiled in this arrangement. The generated PHP retains literal `@endunless` and leaves `if (! ($isProtected)):` open. A newline **after** the injected `{{-- /A7103 --}}` fixes it.

This is a Blade formatting/compiler issue, **not** evidence that the role record or PostgreSQL data is damaged. No database changes are needed.

## Installation

Extract this zip into CRM root without overwriting app controllers or Blade templates. Verify the following files exist:
- `tools/a7104_repair_guard_syntax.php`
- `tools/a7104_compiler_smoke.php`
- `tools/a7104_harden_old_installer.php`

Then in PowerShell:

```powershell
php tools/a7104_compiler_smoke.php
php tools/a7104_repair_guard_syntax.php --check
```

**Only if checks are PASS** (including `A7104_COMPATIBLE`):

```powershell
php tools/a7104_repair_guard_syntax.php --apply
php tools/a7104_repair_guard_syntax.php --check
php artisan view:clear
php artisan view:cache
```

To prevent the original A7103 installer from generating this issue again, separately harden **only the installer code**, after the Blade repair succeeds:

```powershell
php tools/a7104_harden_old_installer.php --check
php tools/a7104_harden_old_installer.php --apply
```

The hardener saves an automatic backup of the old installer under `storage/app/private/a7104_installer_safety_backups/`. It corrects the insertion newline and requires Blade+PHP syntax validation before any future A7103 `--apply` writes. This is preventive maintenance, **not** a reason to rerun the A7103 installer now.

`--check` compiles every view with A7103 markers and invokes `php -l` on generated PHP **after** applying proposed whitespace fixes **in memory**. If a view still fails, the tool refuses to change any file. `--apply` backs up every changed Blade first.

Expected result includes `[RESULT] A7104_BLADE_SYNTAX_REPAIRED`; the follow-up `--check` should show `files_to_repair=0`, with every affected view compiled and syntax-linted. The `view:cache` Laravel command validates the rest of the project views and warms compiled view cache.

Try `Roles > Edit Role > Back to Role` again; then test `Products`, `Customers`, `Service Tickets`, `Deliveries` with a limited-permission role and Super Admin. Ensure hidden CRUD actions remain forbidden by direct HTTP URL with 403. The existing three A7103 PHPUnit suites test permission logic but **do not substitute for Blade compilation or browser UAT**.

## Safety and rollback

No database changes, no migrations, no role permission modifications. `--apply` produces:

`[BACKUP_ID] YYYYMMDD-HHMMSS-xxxxxxxx`

To undo only the syntax repair, use that ID:

```powershell
php tools/a7104_repair_guard_syntax.php --rollback=YOUR_A7104_BACKUP_ID
php artisan view:clear
```

**If any other A7103 pages remain broken, revert the ORIGINAL A7103 128-file patch instead:**

```powershell
php tools/a7103_crud_ui_installer.php --rollback=20261126-050227-d28dcbfc
php artisan optimize:clear
```

This restores the templates that existed before A7103 (thus temporarily removing role-aware UI hiding), but does not roll back other CRM features or any PostgreSQL data. Original rollback refuses to overwrite files modified after A7103. If a checksum conflict occurs, STOP: do not force overwrite; preserve current changes and investigate each file individually. If you already applied A7104 and wish to revert the original A7103 batch, rollback A7104 first to restore the original hashes.

**Do not rerun the original `a7103_crud_ui_installer.php --apply` merely to complete its five residual candidates.** Before hardening, it can reintroduce unsafe Blade adjacency. The optional A7104 installer hardening adds a preflight but does not change which action links the old scanner considers eligible. Manual browser validation remains required.
