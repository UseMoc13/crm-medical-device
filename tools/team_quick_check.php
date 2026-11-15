<?php
/**
 * A7.4.6 - READ-ONLY Team Management Quick Check.
 * Run: php tools/team_quick_check.php
 * Uses only SELECT queries and pure validation checks. Does not invoke write routes.
 */

declare(strict_types=1);

use App\Http\Middleware\EnsureCrmOrganizationAdmin;
use App\Models\CrmTeam;
use App\Models\Role;
use App\Models\User;
use App\Services\CrmPermissionResolver;
use App\Services\CrmTeamMembershipManager;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

$root = dirname(__DIR__);
if (!is_file($root.'/vendor/autoload.php') || !is_file($root.'/bootstrap/app.php')) {
    fwrite(STDERR, "Run this script from the Laravel project root after extracting the ZIP.\n"); exit(2);
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$pass=0; $fail=0; $skip=0;
$report = static function (string $status, string $name, string $detail='') use (&$pass,&$fail,&$skip): void {
    if ($status==='PASS') $pass++; elseif ($status==='FAIL') $fail++; else $skip++;
    echo "[{$status}] {$name}".($detail!=='' ? " — {$detail}" : '').PHP_EOL;
};
try {
    echo 'A7.4.6 Team Quick Check | READ-ONLY | '.date('Y-m-d H:i:s').PHP_EOL;
    echo 'Connection: '.DB::getDriverName().' | Database: '.DB::connection()->getDatabaseName().PHP_EOL.PHP_EOL;
    if (DB::getDriverName()!=='pgsql') {
        $report('FAIL','Database engine','PostgreSQL expected'); exit(1);
    }
    $schema = Schema::hasTable('crm_teams') && Schema::hasTable('crm_team_members');
    foreach (['team_id','team_code','team_type','status','branch_id','leader_user_id','deleted_at'] as $col) {
        $schema = $schema && Schema::hasColumn('crm_teams',$col);
    }
    foreach (['team_member_id','team_id','user_id','joined_at','left_at'] as $col) {
        $schema = $schema && Schema::hasColumn('crm_team_members',$col);
    }
    $report($schema?'PASS':'FAIL','Team schema','Teams and historical memberships');
    if (!$schema) exit(1);

    $expected = [
        'teams.index'=>'GET','teams.create'=>'GET','teams.store'=>'POST','teams.show'=>'GET',
        'teams.edit'=>'GET','teams.update'=>'PUT','teams.destroy'=>'DELETE',
    ];
    foreach ($expected as $name => $method) {
        $route = Route::getRoutes()->getByName($name);
        $mw = $route?->gatherMiddleware() ?? [];
        $ok = $route && in_array($method,$route->methods(),true)
            && str_contains($route->getActionName(),'CrmTeamController@')
            && in_array(\App\Http\Middleware\EnsureCrmAuthenticated::class,$mw,true)
            && in_array(\App\Http\Middleware\EnsureCrmOrganizationAdmin::class,$mw,true);
        $report($ok?'PASS':'FAIL','Route '.$name,$ok?'Method, controller and middleware OK':'Missing or incorrect');
    }

    $members = DB::table('crm_team_members')->whereNull('left_at')->get();
    $allTeams = CrmTeam::query()->get();
    $memberIdsByTeam = $members->groupBy('team_id');
    $ok = true;
    foreach ($allTeams as $team) {
        $mine = $memberIdsByTeam->get($team->team_id, collect());
        if ($team->leader_user_id && !$mine->contains(fn ($m)=> (string)$m->user_id === (string)$team->leader_user_id)) $ok=false;
        if ($team->team_type === 'permanent') {
            if (!$team->branch_id) $ok=false;
            foreach ($mine as $membership) {
                $memberBranch = DB::table('users')->where('user_id',$membership->user_id)->value('branch_id');
                if ((string)$memberBranch !== (string)$team->branch_id) $ok=false;
            }
        }
    }
    $report($ok?'PASS':'FAIL','Existing membership consistency',$allTeams->count().' teams / '.$members->count().' active members');
    $duplicates = DB::table('crm_team_members')->select('team_id','user_id')
        ->groupBy('team_id','user_id')->havingRaw('COUNT(*) > 1')->exists();
    $report(!$duplicates?'PASS':'FAIL','No duplicate member rows');

    $resolver=app(CrmPermissionResolver::class);
    $users=User::query()->with('roles')->get();
    $super=$users->first(fn ($u)=>strtolower((string)$u->status)==='active' && $resolver->isSuperAdmin($u));
    $normal=$users->first(fn ($u)=>strtolower((string)$u->status)==='active' && !$resolver->isSuperAdmin($u));
    $guard=app(EnsureCrmOrganizationAdmin::class);
    $statusFor = static function (User $actor) use ($guard): int {
        $req=Request::create('/teams','GET');
        $req->setUserResolver(static fn ($which=null)=>$actor);
        try {
            return $guard->handle($req,static fn ($r)=>response('OK',200))->getStatusCode();
        } catch (HttpExceptionInterface $ex) { return $ex->getStatusCode(); }
    };
    if ($super) $report($statusFor($super)===200?'PASS':'FAIL','Super Admin middleware','HTTP '.$statusFor($super));
    else $report('SKIP','Super Admin middleware','No active Super Admin found');
    if ($normal) $report($statusFor($normal)===403?'PASS':'FAIL','Non-admin denied','HTTP '.$statusFor($normal));
    else $report('SKIP','Non-admin denied','No active non-admin found');

    $manager=app(CrmTeamMembershipManager::class);
    try {
        $manager->validateSelection([], '00000000-0000-4000-8000-000000000001', 'project', null, 'active');
        $report('FAIL','Leader must be a member','Unexpectedly accepted');
    } catch (ValidationException $e) {
        $report(isset($e->errors()['leader_user_id'])?'PASS':'FAIL','Leader must be a member','Invalid leader rejected');
    }
    try {
        $manager->validateSelection([], null, 'permanent', null, 'active');
        $report('FAIL','Permanent team needs branch','Unexpectedly accepted');
    } catch (ValidationException $e) {
        $report(isset($e->errors()['branch_id'])?'PASS':'FAIL','Permanent team needs branch','Missing branch rejected');
    }
    try {
        $manager->validateSelection(['00000000-0000-4000-8000-000000000001'],null,'project',null,'archived');
        $report('FAIL','Archived team blocks new members','Unexpectedly accepted');
    } catch (ValidationException $e) {
        $report(isset($e->errors()['member_user_ids'])?'PASS':'FAIL','Archived team blocks new members','Adding member rejected');
    }
    if ($normal) {
        try {
            $manager->validateSelection([(string)$normal->getKey()],null,'project',null,'active');
            $report('PASS','Project membership','Valid active user accepted without branch constraint');
        } catch (ValidationException $e) {
            $report('FAIL','Project membership',implode('; ',array_merge(...array_values($e->errors()))));
        }
    } else $report('SKIP','Project membership','No active non-admin user available');

    echo PHP_EOL."Result: {$pass} PASS / {$fail} FAIL / {$skip} SKIP.".PHP_EOL;
    echo "No records were inserted, updated, or deleted. Successful CRUD persistence/UI still requires browser testing.\n";
    exit($fail?1:0);
} catch (Throwable $e) {
    fwrite(STDERR,'[ERROR] '.get_class($e).': '.$e->getMessage().PHP_EOL);
    exit(2);
}
