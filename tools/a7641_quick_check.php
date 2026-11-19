<?php
/** A7.6.4.1 static verification only. */
declare(strict_types=1);
$root=dirname(__DIR__);
$checks=[];
$check=static function(string $label,bool $pass)use(&$checks):void{
    $checks[]=$pass; echo '['.($pass?'PASS':'FAIL').'] '.$label.PHP_EOL;
};
$paths=['tools/a764_runtime_preflight.php','tools/install_a7641_preflight_fix.php',
    'tools/a7641_quick_check.php','tools/a7641_payload/tools/a764_runtime_preflight.php'];
foreach($paths as $path) $check('File: '.$path,is_file($root.'/'.$path));
$src=(string)@file_get_contents($root.'/tools/a764_runtime_preflight.php');
$check('No unsupported Artisan::has',!str_contains($src,'Artisan::has('));
foreach(['crm:announcements:publish-due','workspace:reminders:sync','crm:reporting:baseline'] as $name){
    $check('Command lookup via Artisan::all: '.$name,
        str_contains($src,"array_key_exists('{$name}', \\Illuminate\\Support\\Facades\\Artisan::all())"));
}
$check('Dashboard service check preserved',str_contains($src,'Read-only announcement service resolves'));
$check('Dashboard read model check preserved',str_contains($src,'Dashboard read model retained'));
$check('Report controller check preserved',str_contains($src,'Report controller autoloads'));
$check('Read-only provenance retained',str_contains($src,'No business queries.'));
$check('No mutation methods in preflight',preg_match('/(?:DB::|Schema::)(?:insert|update|delete|truncate|drop|create)\s*\(/i',$src)!==1);
foreach($paths as $path){
    $result=[];$status=1;exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($root.'/'.$path).' 2>&1',$result,$status);
    $check('PHP syntax: '.$path,$status===0);
}
$pass=count(array_filter($checks));$total=count($checks);
echo "A7.6.4.1 static: {$pass}/{$total} PASS, ".($total-$pass)." FAIL.".PHP_EOL;
exit($pass===$total?0:1);
