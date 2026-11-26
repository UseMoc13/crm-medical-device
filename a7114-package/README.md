# A7.11.4 — Staging & UAT Safety Gate

Additive-only package. Does not change existing PHP application code, RBAC, routes, Blade, or databases.

From project root:

```powershell
New-Item -ItemType Directory -Path .\a7114-package -Force | Out-Null
Expand-Archive "$HOME\Downloads\A7114_Staging_UAT_Gate.zip" .\a7114-package -Force
php .\a7114-package\a7114_installer.php --check
php .\a7114-package\a7114_installer.php --apply
php .\a7114-package\a7114_installer.php --check
php tools/a7114_staging_gate.php --check
```

The gate is expected to return HOLD if `.env` still targets the existing non-disposable CRM database. This is correct and protective. See `docs/A7114_STAGING_AND_UAT_RUNBOOK.md`. `--init` creates an attestation with every flag false. Never set flags true without manual verification.
