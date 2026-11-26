<?php
/** Read-only eligible technician directory for safe same-branch corrections. */
declare(strict_types=1);
$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (count($argv) !== 2 || !preg_match('/\A--from=([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})\z/iD', $argv[1], $m)) {
    fwrite(STDERR, "Usage: php tools/a799_assignment_candidates.php --from=CURRENT_USER_UUID\n");
    exit(2);
}
try {
    $db = \Illuminate\Support\Facades\DB::connection();
    $from = $db->table('users')->where('user_id', $m[1])->first(['user_id','name','branch_id']);
    if (!$from) throw new RuntimeException('Original user not found.');
    $query = app(\App\Services\CrmUserAssignmentEligibility::class)->eligibleQuery('technician');
    if ($from->branch_id === null) $query->whereNull('users.branch_id');
    else $query->where('users.branch_id', $from->branch_id);
    $users = $query->orderBy('users.name')->get(['users.user_id','users.name','users.branch_id']);
    echo '[DATABASE] '.$db->getDatabaseName().PHP_EOL;
    echo '[FROM] '.$from->name.' ('.$from->user_id.') | branch='.($from->branch_id??'(none)').PHP_EOL;
    foreach ($users as $user) {
        echo '[CANDIDATE] '.$user->name.' | '.$user->user_id.PHP_EOL;
    }
    echo '[RESULT] A799_CANDIDATES_READ_ONLY; count='.$users->count().PHP_EOL;
    if ($users->isEmpty()) echo '[NOTE] No eligible Technician in the same branch. Do not force cross-branch correction via CLI.'.PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, '[FAIL] '.$e->getMessage().PHP_EOL);
    exit(1);
}
