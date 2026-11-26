<?php
/** Copy official A7.8.4 template to a new private ledger, without modifying existing evidence. */
declare(strict_types=1);
$root = dirname(__DIR__);
$source = $root.'/docs/uat/a784_uat_execution_template.json';
$folder = $root.'/storage/app/private/a7112_uat';
if (!is_file($source)) { fwrite(STDERR, "[BLOCK] Original A7.8.4 ledger template missing.\n"); exit(2); }
if (!is_dir($folder) && !mkdir($folder,0700,true) && !is_dir($folder)) { fwrite(STDERR,"[BLOCK] Cannot create private folder\n");exit(2); }
$path = $folder.'/uat_'.gmdate('Ymd_His').'_'.bin2hex(random_bytes(3)).'.json';
$data = (string)file_get_contents($source);
$decoded = json_decode($data,true);
if (!is_array($decoded) || count($decoded['scenarios']??[])!==15 || ($decoded['policies']['announcement_public_assets']['status']??'')!=='HOLD' || ($decoded['policies']['reports_privacy_pilot']['status']??'')!=='HOLD') {
 fwrite(STDERR,"[BLOCK] Template contract changed.\n");exit(2);
}
if (file_put_contents($path,$data,LOCK_EX)===false) { fwrite(STDERR,"[BLOCK] Ledger cannot be saved.\n");exit(2); }
@chmod($path,0600);
echo '[CREATED] '.$path.PHP_EOL;
echo '[NOTE] All 15 are NOT_RUN. Two policies HOLD. Edit only after actual execution and save evidence privately.'.PHP_EOL;
