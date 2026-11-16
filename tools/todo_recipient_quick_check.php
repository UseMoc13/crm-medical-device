<?php
/**
 * A7.4.7 — read-only directory & recipient authorization smoke test.
 * Run: php tools/todo_recipient_quick_check.php
 * No writes, no POST/PATCH/DELETE and no live assignment modifications.
 */
declare(strict_types=1);

use App\Models\User;
use App\Services\CrmTodoRecipientDirectory;
use App\Services\CrmPermissionResolver;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

$root = dirname(__DIR__);
if (!is_file($root.'/vendor/autoload.php') || !is_file($root.'/bootstrap/app.php')) {
    fwrite(STDERR,"Extract this package to the Laravel project root first.\n"); exit(2);
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$pass = 0; $fail = 0; $skip = 0;
$check = static function (string $status, string $name, string $detail = '') use (&$pass, &$fail, &$skip): void {
    if ($status === 'PASS') $pass++; elseif ($status === 'FAIL') $fail++; else $skip++;
    echo "[{$status}] {$name}".($detail !== '' ? " — {$detail}" : '').PHP_EOL;
};
try {
    echo 'A7.4.7 TO-DO Recipient Quick Check | READ-ONLY | '.date('Y-m-d H:i:s').PHP_EOL;
    echo 'Connection: '.DB::getDriverName().' | Database: '.DB::connection()->getDatabaseName().PHP_EOL.PHP_EOL;
    $check(DB::getDriverName()==='pgsql'?'PASS':'FAIL','PostgreSQL connection');
    $schema = true;
    foreach (['users','roles','branches','user_roles','crm_teams','crm_team_members','workspace_tasks','workspace_task_assignments'] as $table) {
        if (!Schema::hasTable($table)) $schema = false;
    }
    $check($schema?'PASS':'FAIL','Recipient/Team/TO-DO schema');
    if (!$schema) exit(1);
    $route = Route::getRoutes()->getByName('todos.recipient-options');
    $middleware = $route?->gatherMiddleware() ?? [];
    $routeOkay = $route && in_array('GET',$route->methods(),true)
        && str_contains($route->getActionName(),'TodoController@recipientOptions')
        && in_array(\App\Http\Middleware\EnsureCrmAuthenticated::class,$middleware,true);
    $check($routeOkay?'PASS':'FAIL','Recipient options route and middleware');
    $resource = Route::getRoutes()->match(\Illuminate\Http\Request::create('/todos/recipient-options','GET'));
    $check($resource->getName()==='todos.recipient-options'?'PASS':'FAIL','Static route precedence','Does not match todos.show');

    $directory = app(CrmTodoRecipientDirectory::class);
    $resolver = app(CrmPermissionResolver::class);
    $actors = User::query()->where('status','active')->get();
    $normal = $actors->first(fn (User $u) => !$resolver->isSuperAdmin($u));
    $admin = $actors->first(fn (User $u) => $resolver->isSuperAdmin($u));
    if ($normal) {
        $result = $directory->search($normal, []);
        $users = $result->getCollection();
        $inScope = $users->every(fn (User $u) =>
            $normal->branch_id === null
                ? (string)$u->getKey() === (string)$normal->getKey()
                : (string)$u->branch_id === (string)$normal->branch_id);
        $check($inScope?'PASS':'FAIL','Non-admin recipient scope','Same branch or self only');
        $options = $directory->filterOptions($normal);
        $check(isset($options['roles'],$options['branches'],$options['teams'])?'PASS':'FAIL','Role/Branch/Team filter directory');
        $private = $directory->selected($normal,['00000000-0000-4000-8000-000000000001']);
        $check($private===[]?'PASS':'FAIL','Non-eligible ID cannot leak through preselection');
        try {
            $directory->assertAssignable($normal,['00000000-0000-4000-8000-000000000001']);
            $check('FAIL','Backend rejects forged assignee','Unexpectedly accepted');
        } catch (ValidationException $exception) {
            $check(isset($exception->errors()['assignee_ids'])?'PASS':'FAIL','Backend rejects forged assignee');
        }
        $same = $directory->selected($normal,[(string)$normal->getKey()]);
        $check(count($same)===1?'PASS':'FAIL','Owner is eligible for own assignment');
    } else $check('SKIP','Non-admin scope and filters','No active non-admin user found');
    if ($admin) {
        $result = $directory->search($admin, []);
        $check($result->total() >= $result->count()?'PASS':'FAIL','Super Admin paginated directory', $result->total().' eligible users');
    } else $check('SKIP','Super Admin directory','No active Super Admin found');
    echo PHP_EOL."Result: {$pass} PASS / {$fail} FAIL / {$skip} SKIP.".PHP_EOL;
    echo "Read-only checks. Browser selection, save, persisted assignments and file upload require manual testing.\n";
    exit($fail ? 1 : 0);
} catch (Throwable $error) {
    fwrite(STDERR,'[ERROR] '.get_class($error).': '.$error->getMessage().PHP_EOL);
    exit(2);
}
