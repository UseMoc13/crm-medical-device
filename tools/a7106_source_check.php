<?php
/** Read-only A7.10.6 package and template consistency check; no Laravel bootstrap. */
declare(strict_types=1);
$root = dirname(__DIR__);
require_once $root.'/app/Services/CrmRoleAccessCatalog.php';
require_once $root.'/app/Services/CrmWorkflowAccess.php';
require_once $root.'/app/Services/CrmRoleTemplateCatalog.php';
use App\Services\CrmRoleAccessCatalog as Pages;
use App\Services\CrmWorkflowAccess as Flow;
use App\Services\CrmRoleTemplateCatalog as Templates;
$checks = 0;
try {
    foreach ([
        'app/Services/CrmRoleTemplateCatalog.php',
        'tools/a7106_role_templates_installer.php',
        'tests/Unit/CrmRoleTemplateCatalogTest.php',
        'docs/A7106_ROLE_TEMPLATES.md',
    ] as $path) {
        if (!is_file($root.'/'.$path)) throw new RuntimeException('Missing: '.$path);
        if (str_ends_with($path,'.php')) {
            exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($root.'/'.$path).' 2>&1', $out, $rc);
            if ($rc !== 0) throw new RuntimeException('PHP lint failed: '.$path.' '.implode(' ', $out));
            $checks++;
        }
        echo '[PASS] '.$path.PHP_EOL;
    }
    foreach (Templates::keys() as $key) {
        $pages = Templates::pages($key);
        if (!$pages || !isset(Templates::labels()[$key]) || !isset(Templates::descriptions()[$key])) throw new RuntimeException('Incomplete template: '.$key);
        foreach ($pages as $slug=>$actions) {
            if (!isset(Pages::PAGES[$slug]) || !in_array('view',$actions,true) || array_diff($actions, Pages::ACTIONS)) throw new RuntimeException('Invalid page/action: '.$key.'/'.$slug);
            $checks++;
        }
        foreach (Templates::workflows($key) as $workflow) {
            if (!isset(Flow::ACTIONS[$workflow]) || !in_array($workflow, Flow::permittedByPages($pages), true)) throw new RuntimeException('Invalid workflow: '.$key.'/'.$workflow);
            $checks++;
        }
    }
    echo '[RESULT] A7106_SOURCE_READY; templates='.count(Templates::keys()).' checks='.$checks.' (Laravel/DB not executed)'.PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, '[FAIL] '.$error->getMessage().PHP_EOL);
    exit(1);
}
