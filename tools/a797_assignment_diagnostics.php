<?php
/** Read-only diagnostics; no mutations, migrations or secrets output. */
declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $connection = \Illuminate\Support\Facades\DB::connection();
    $name = $connection->getDatabaseName();
    echo '[DATABASE] ' . $name . PHP_EOL;
    $directory = app(\App\Services\CrmUserAssignmentEligibility::class);
    foreach (['technician', 'sales'] as $purpose) {
        $allowedNames = $directory->rolesFor($purpose);
        $roles = \App\Models\Role::query()->whereIn(
            \Illuminate\Support\Facades\DB::raw('LOWER(TRIM(role_name))'), $allowedNames
        )->orderBy('role_name')->pluck('role_name')->all();
        $candidates = $directory->options($purpose);
        echo '[ROLE] ' . $purpose . ': [' . implode(', ', $roles) . ']' . PHP_EOL;
        echo '[ELIGIBLE] ' . $purpose . ': ' . $candidates->count() . PHP_EOL;
        foreach ($candidates->take(8) as $candidate) {
            echo '  - ' . $candidate->name . PHP_EOL;
        }
        if ($roles === []) echo '[NOTE] Configure a matching role in config/crm_assignment.php.' . PHP_EOL;
    }
    echo '[RESULT] A797_ELIGIBILITY_READ_ONLY_DIAGNOSTICS_DONE' . PHP_EOL;
} catch (\Throwable $e) {
    fwrite(STDERR, '[FAIL] Read-only eligibility check: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
