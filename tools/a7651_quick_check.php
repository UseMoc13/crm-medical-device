<?php
$root=dirname(__DIR__);$p=0;$bad=0;
function a7651_check(bool $condition, string $label): void {
    global $p,$bad; echo ($condition?'[PASS] ':'[FAIL] ').$label.PHP_EOL;
    $condition?$p++:$bad++;
}
$controller=$root.'/app/Http/Controllers/ReportController.php';
$source=is_file($controller)?file_get_contents($controller):'';
a7651_check(is_file($controller),'ReportController canonical filename exists');
a7651_check(str_contains($source,'use App\\Services\\CrmNavigationAccess;'),'Uses shared navigation permission checker');
a7651_check(str_contains($source,"->canView(Auth::guard('web')->user(), 'reports')"),'Reports permission gate');
a7651_check(substr_count($source,'$this->assertReportsView();')===2,'Both GET entrypoints invoke guard');
a7651_check(str_contains($source,'private function assertReportsView(): void'),'Report authorization helper exists');
a7651_check(str_contains($source,"private const ISSUED = ['Sent', 'Paid'];"),'Existing financial status policy retained');
a7651_check(str_contains($source,'CrmReportingDataScope::class'),'Existing row scope adapter retained');
a7651_check(str_contains($source,"'text/csv; charset=UTF-8'"),'Existing CSV type retained');
foreach (['tests/Unit/CrmReportAuthorizationSourceTest.php','tests/Feature/CrmReportAuthorizationHttpTest.php'] as $f) a7651_check(is_file($root.'/'.$f),'Test exists: '.$f);
foreach ([$controller,$root.'/tests/Unit/CrmReportAuthorizationSourceTest.php',$root.'/tests/Feature/CrmReportAuthorizationHttpTest.php',__DIR__.'/install_a7651_report_auth.php',__FILE__] as $f) {
    if (!is_file($f)) {a7651_check(false,'PHP syntax '.$f);continue;}
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($f).' 2>&1',$output,$code);
    a7651_check($code===0,'PHP syntax '.basename($f));unset($output);
}
echo "A7.6.5.1 static: {$p} PASS, {$bad} FAIL.\n";exit($bad?1:0);
