<?php
/** Self-test for exact-match role-hidden status-form patch. No project files modified. */
declare(strict_types=1);
require_once __DIR__.'/a7108_navigation_fix.php';
$cases=[
    'resources/views/deliveries/show.blade.php'=>"    const next = document.getElementById('deliveryNextStatus');\n"."    document.getElementById('deliveryCancel').addEventListener('click', close);"."            </div>1\n        </form>",
    'resources/views/installations/show.blade.php'=>"    const message = document.getElementById('installationModalMessage');\n    document.querySelectorAll('[data-installation-next]')"."    document.addEventListener('keydown', event => { if(event.key === 'Escape') close(); });\n    document.querySelectorAll('.checklist-toggle')",
    'resources/views/maintenance/_workflow.blade.php'=>"    const confirm = document.getElementById('mtStatusConfirm');\n    const close",
];
foreach($cases as $path=>$source){
    $first=a7108_nav_rewrite($path,$source);
    if($first['state']!=='old'||$first['after']===$source)throw new RuntimeException('Failed to patch old fixture: '.$path);
    $second=a7108_nav_rewrite($path,$first['after']);
    if($second['state']!=='new'||$second['after']!==$first['after'])throw new RuntimeException('Not idempotent: '.$path);
    try{a7108_nav_rewrite($path, str_replace('getElementById','getBogusElementById',$source));}
    catch(RuntimeException $e){echo '[PASS] Rejects changed anchors '.$path.PHP_EOL;continue;}
    throw new RuntimeException('Changed anchor not rejected: '.$path);
}
echo "[PASS] 3 targeted rewrites are deterministic and idempotent\n[RESULT] A7108_NAV_PATCH_SELF_TEST_PASS; no project files modified\n";
