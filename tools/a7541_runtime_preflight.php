<?php
/** Read-only Laravel command / schema preflight. */
$root=dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
$n=0;$bad=0;
$check=static function(string $label,bool $ok)use(&$n,&$bad){$n++;if(!$ok)$bad++;echo($ok?'[PASS] ':'[FAIL] ').$label.PHP_EOL;};
$commands=Artisan::all();
$check('Regression command registered',array_key_exists('crm:integration:regression',$commands));
foreach(['crm:integration:audit','crm:integration:inspect','crm:integration:remediation-review'] as $cmd)
 $check('Existing command '.$cmd,array_key_exists($cmd,$commands));
$check('Regression gate resolves',app(\App\Services\CrmIntegrationRegressionGate::class) instanceof \App\Services\CrmIntegrationRegressionGate);
foreach(\App\Services\CrmIntegrationRegressionGate::REQUIRED_TABLES as $table)
 $check('Table '.$table,Schema::hasTable($table));
$check('CRM opt-in preference column',Schema::hasTable('workspace_notification_preferences')&&Schema::hasColumn('workspace_notification_preferences','crm_status_enabled'));
foreach(\App\Services\CrmIntegrationRegressionGate::REQUIRED_ROUTES as $route)
 $check('Route '.$route,Route::has($route));
foreach(\App\Services\CrmIntegrationRegressionGate::REQUIRED_EVENTS as $event)
 $check('Audit event '.$event,in_array($event,\App\Services\WorkspaceAuditTrail::EVENTS,true));
echo "\nA7.5.4.1 runtime: ".($n-$bad)."/$n PASS, $bad FAIL. Read-only.\n";
exit($bad?1:0);
