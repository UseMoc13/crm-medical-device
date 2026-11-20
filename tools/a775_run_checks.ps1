$ErrorActionPreference = 'Stop'
php -l tools/a775_pg_performance.php
if ($LASTEXITCODE -ne 0) { throw 'PHP syntax failed.' }
php -l tools/a775_performance_policy.php
if ($LASTEXITCODE -ne 0) { throw 'PHP syntax failed.' }
php tools/a775_performance_policy_test.php
if ($LASTEXITCODE -ne 0) { throw 'Policy tests failed.' }
php tools/a775_pg_performance.php --limit=20
if ($LASTEXITCODE -ne 0) { throw 'Read-only preflight failed.' }
