<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Support/CrmPrivateFilePath.php';
use App\Support\CrmPrivateFilePath as Guard;
$pass=0; $fail=0;
function proof(bool $valid,string $label): void { global $pass,$fail; echo ($valid?'[PASS] ':'[FAIL] ').$label."\n"; $valid?$pass++:$fail++; }
$t='11111111-1111-4111-8111-111111111111';
$a='22222222-2222-4222-8222-222222222222';
$f='33333333-3333-4333-8333-333333333333.pdf';
$path='crm-task-submissions/'.$t.'/'.$a.'/'.$f;
proof(Guard::submission($path,$t,$a),'Normal submitted PDF accepted');
proof(!Guard::submission($path,$t,'ffffffff-ffff-4fff-8fff-ffffffffffff'),'Foreign assignment rejected');
proof(!Guard::submission($path,'ffffffff-ffff-4fff-8fff-ffffffffffff',$a),'Foreign task rejected');
proof(!Guard::submission('crm-task-submissions/../../contracts/secret.pdf',$t,$a),'Relative traversal rejected');
proof(!Guard::submission($path.'/evil',$t,$a),'Path suffix rejected');
proof(!Guard::submission($path.'.php',$t,$a),'Executable suffix rejected');
proof(!Guard::submission(str_replace('/','\\',$path),$t,$a),'Backslash traversal rejected');
proof(Guard::contract('contracts/test_AbC12.pdf'),'Normal contract PDF accepted');
proof(!Guard::contract('contracts/subdir/file.pdf'),'Nested contract path rejected');
proof(!Guard::contract('contracts/../../private.pdf'),'Contract traversal rejected');
proof(!Guard::contract('contracts/file.php'),'Non-PDF contract rejected');
proof(!Guard::contract('other/file.pdf'),'Foreign private directory rejected');
$base=sys_get_temp_dir().'/crm-a7742-smoke-'.bin2hex(random_bytes(5));
mkdir($base.'/contracts',0700,true);
mkdir($base.'/other',0700,true);
file_put_contents($base.'/contracts/good.pdf','fixture');
file_put_contents($base.'/other/private.pdf','fixture');
try {
    proof(Guard::resolve($base,'contracts/good.pdf','contracts')===realpath($base.'/contracts/good.pdf'),'Resolves valid in-folder file');
    proof(Guard::resolve($base,'other/private.pdf','contracts')===null,'Out-of-scope file rejected');
    proof(Guard::resolve($base,'contracts/../other/private.pdf','contracts')===null,'Physical traversal rejected');
    proof(Guard::resolve($base,'contracts/missing.pdf','contracts')===null,'Missing file rejected');
    $link=$base.'/contracts/escape.pdf';
    $linked=function_exists('symlink') && @symlink($base.'/other/private.pdf',$link);
    if ($linked) proof(Guard::resolve($base,'contracts/escape.pdf','contracts')===null,'Symlink escape rejected');
    else echo "[SKIP] Symlink escape test (OS permission unavailable)\n";
    if ($linked && is_link($link)) unlink($link);
} finally {
    unlink($base.'/contracts/good.pdf');unlink($base.'/other/private.pdf');
    rmdir($base.'/contracts');rmdir($base.'/other');rmdir($base);
}
echo "A7.7.4.2 smoke: {$pass}/".($pass+$fail)." PASS; {$fail} FAIL. Temporary fixture only.\n";
exit($fail?1:0);
