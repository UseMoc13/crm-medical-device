# A7.7.4.3 — Announcement Public Asset Exposure Preflight

## Scope

This is a **read-only, local PostgreSQL inspection**. It counts DB references to announcement thumbnails, media and attachments stored by the current controller on Laravel's `public` disk. It does **not** inspect or move files, guess URLs, alter publication status, or modify permissions.

The announcement index/show controller checks `published` plus effective `published_at` and `expires_at`. A public filesystem path typically does **not** enforce those page visibility rules. Merely using a random file path is not an access-control policy.

## Safe commands — PowerShell in the project root

```powershell
php -l tools/a7742_apply_private_file_patch.php
php tools/a7743_exposure_smoke.php
php artisan test --filter=CrmAnnouncementExposurePolicyTest
php tools/a7743_announcement_exposure.php
```

The last command reads the **configured PostgreSQL database**, so check `.env` before running. The tool requires `APP_ENV=local`, begins a PostgreSQL `READ ONLY` transaction, and prints only aggregate counts.

## Interpretation

- `HOLD_HIDDEN_PUBLIC_DISK_REFERENCES`: draft/scheduled/archived/expired/unpublished asset references exist and a local `public/storage` serving path is detected. **Potential exposure**, not proof of remote access.
- `REVIEW_HIDDEN_REFERENCES_PUBLIC_SERVING_NOT_VERIFIED`: hidden references exist, but the serving path is not detected locally; public disk uploads can still be served through alternate infrastructure.
- `REVIEW_PUBLIC_ASSET_VISIBILITY_POLICY`: current references are only for currently visible announcements; future drafts can still create public-disk files.
- `NO_CURRENT_STORED_ASSET_REFERENCES`: no stored asset references in the queried tables; no guarantee about orphaned physical files.

This stage **does not** silently turn off announcement attachments or introduce a breaking storage migration. Next step is a policy decision: which assets are intended to be public, whether drafts should be previewable by admins, how expiration/revocation should work, and whether existing asset URLs must remain valid. Only after that should a separate backed-up migration to private storage + authorized streaming/download routes be designed.

## A7.7.4.2 cleanup bugfix included

The ZIP also includes a corrected `tools/a7742_apply_private_file_patch.php`. The old script used `exit(0)` inside a PHP `try`/`finally` dry-run; `exit` bypasses `finally` and leaves `.tmp.php` files. The replacement uses `return` so staged files are cleaned. Existing application controllers are unaffected. This does not delete existing leftovers: remove only the exact two filenames shown by `git status` after checking them.
