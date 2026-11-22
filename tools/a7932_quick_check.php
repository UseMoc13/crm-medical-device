<?php
// Offline check: no Laravel boot, no DB.
$root = dirname(__DIR__);
$m = json_decode(file_get_contents($root.'/database/schema/crm-baseline-manifest.json'), true, 512, JSON_THROW_ON_ERROR);
$s = file_get_contents($root.'/database/schema/pgsql-schema.sql');
$ok = true;
function ck(bool $condition, string $label): void {
    global $ok;
    echo ($condition?'[PASS] ':'[FAIL] ').$label.PHP_EOL;
    $ok = $ok && $condition;
}
ck(hash('sha256', $s) === $m['baseline_sha256'], 'Baseline SHA-256');
preg_match_all('/^CREATE TABLE public\.(\w+)/m', $s, $matches);
$tables = $matches[1]; sort($tables);
ck($tables === $m['tables'], '65 exact table names');
foreach (['foreign_keys'=>'/ADD CONSTRAINT .* FOREIGN KEY/', 'primary_keys'=>'/ADD CONSTRAINT .* PRIMARY KEY/', 'unique_constraints'=>'/ADD CONSTRAINT .* UNIQUE/', 'check_constraints'=>'/\bCHECK \(/', 'standalone_indexes'=>'/^CREATE (?:UNIQUE )?INDEX/m', 'sequences'=>'/^CREATE SEQUENCE/m'] as $key=>$pattern) {
    ck(preg_match_all($pattern,$s) === $m['counts'][$key], $key.' = '.$m['counts'][$key]);
}
preg_match_all('/^CREATE TABLE public\.\w+ \(\n(.*?)^\);/ms',$s,$defs);
$cols=0;foreach($defs[1] as $def){$cols+=preg_match_all('/^    (?!CONSTRAINT )\S+/m',$def);}
ck($cols === 623,'623 columns');
$files = array_map(fn($p)=>basename($p,'.php'),glob($root.'/database/migrations/*.php'));
$missing = array_diff($m['migrations'],$files);
$extra = array_diff($files,$m['migrations']);
ck(!$missing && !$extra,'Exactly 32 historical migrations present (candidate checkpoint)');
foreach ($missing as $f) echo 'Missing migration: '.$f.PHP_EOL;
foreach ($extra as $f) echo 'New migration requires baseline review: '.$f.PHP_EOL;
preg_match_all("/^    \\('([^']+)', 1\\)/m",$s,$ledger);
ck($ledger[1] === $m['migrations'],'Exact historical migration ledger');
preg_match_all("/^    \\('([^']+)', '[^']+', '[^']+', '[^']+'\\)/m",$s,$permissions);
$names = $permissions[1];sort($names);
ck($names === $m['permissions'],'117 code-defined permission names');
require_once $root.'/app/Services/CrmRoleAccessCatalog.php';
require_once $root.'/app/Services/CrmWorkflowAccess.php';
// Supply a minimal stub only for offline class loading without bootstrapping Laravel.
class CrmOfflineSeederStub {}
if (!class_exists('Illuminate\\Database\\Seeder')) { class_alias(CrmOfflineSeederStub::class, 'Illuminate\\Database\\Seeder'); }
require_once $root.'/database/seeders/CrmPermissionCatalogSeeder.php';
ck(array_keys(\Database\Seeders\CrmPermissionCatalogSeeder::rows()) === $m['permissions'],'Live application catalogue matches baseline');
ck(str_starts_with($s,"\\set ON_ERROR_STOP on\n") && str_contains($s,'CRM baseline requires an empty public schema') && str_ends_with(trim($s),'COMMIT;'),'Transactional empty-schema guard and ON_ERROR_STOP');
preg_match_all('/^INSERT INTO public\.(\w+)/m',$s,$inserts);
ck($inserts[1] === ['migrations','permissions'] && !preg_match('/^\s*(?:DROP|TRUNCATE|DELETE FROM|COPY)\b/im',$s),'Only ledger/catalog inserts; no destructive DDL or business rows');
echo $ok?"[RESULT] BASELINE_OFFLINE_READY_NOT_EXECUTED\n":"[RESULT] HOLD_BASELINE_CHECK_FAILED\n";
exit($ok?0:1);
