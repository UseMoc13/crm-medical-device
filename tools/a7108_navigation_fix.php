<?php
/**
 * A7.10.8.1: targeted fix for role-hidden workflow dialog DOM handlers.
 * Uses exact-source anchors; supports --check, --apply, --rollback=<backup-id>.
 * Does not touch database, routes, controllers, saved permissions or any other Blade.
 */
declare(strict_types=1);

function a7108_nav_rewrite(string $relative, string $s): array
{
    $rules = match($relative){
        'resources/views/deliveries/show.blade.php'=>[
            "    const next = document.getElementById('deliveryNextStatus');\n"=>"    const next = document.getElementById('deliveryNextStatus');\n    // A7108: the status form is omitted for read-only roles.\n    if (!modal || !title || !message || !next) return;\n",
            "    document.getElementById('deliveryCancel').addEventListener('click', close);"=>"    document.getElementById('deliveryCancel')?.addEventListener('click', close);",
            "            </div>1\n        </form>"=>"            </div>\n        </form>",
        ],
        'resources/views/installations/show.blade.php'=>[
            "    const message = document.getElementById('installationModalMessage');\n    document.querySelectorAll('[data-installation-next]')"=>"    const message = document.getElementById('installationModalMessage');\n    // A7108: keep checklist handlers active even when status controls are hidden.\n    if (modal && value && message && document.getElementById('installationModalCancel')) {\n    document.querySelectorAll('[data-installation-next]')",
            "    document.addEventListener('keydown', event => { if(event.key === 'Escape') close(); });\n    document.querySelectorAll('.checklist-toggle')"=>"    document.addEventListener('keydown', event => { if(event.key === 'Escape') close(); });\n    }\n    document.querySelectorAll('.checklist-toggle')",
        ],
        'resources/views/maintenance/_workflow.blade.php'=>[
            "    const confirm = document.getElementById('mtStatusConfirm');\n    const close"=>"    const confirm = document.getElementById('mtStatusConfirm');\n    // A7108: status confirmation elements are absent for view-only roles.\n    if (!next || !msg || !confirm) return;\n    const close",
        ],
        default=>throw new RuntimeException('Unexpected target file'),
    };
    $matched=0;$applied=0;$modified=$s;
    foreach($rules as $old=>$new){
        $oldCount=substr_count($modified,$old);
        $newCount=substr_count($modified,$new);
        if($newCount===1){$matched++;}
        elseif($oldCount===1){$modified=str_replace($old,$new,$modified);$applied++;}
        else throw new RuntimeException("Unexpected anchor in $relative (old=$oldCount new=$newCount); no files modified");
    }
    if($matched>0&&$applied>0)throw new RuntimeException("Partially modified $relative; refusing patch");
    return ['after'=>$modified,'state'=>$applied>0?'old':'new','rules'=>count($rules)];
}
function a7108_nav_lint(string $php): ?string
{
    $tmp=tempnam(sys_get_temp_dir(),'qa8nav_');if($tmp===false)return 'Cannot create PHP temp file';
    try{
        if(file_put_contents($tmp,$php)===false)return 'Cannot write PHP temp file';
        $lines=[];$code=0;exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($tmp).' 2>&1',$lines,$code);
        return $code===0?null:implode(' ',$lines);
    }finally{@unlink($tmp);}
}
function a7108_nav_validate(array $changes,string $root): void
{
    if(!is_file($root.'/vendor/autoload.php')||!is_file($root.'/bootstrap/app.php'))throw new RuntimeException('Composer/bootstrap missing');
    require_once $root.'/vendor/autoload.php';
    $app=require $root.'/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $compiler=$app->make('blade.compiler');
    foreach($changes as $relative=>$change){
        $compiled=$compiler->compileString($change['after']);
        $lint=a7108_nav_lint($compiled);
        if($lint!==null)throw new RuntimeException("Blade PHP lint failed $relative: $lint");
        echo '[PASS] Blade compile + PHP lint '.$relative.PHP_EOL;
    }
}
function a7108_nav_atomic(string $path,string $bytes): void
{
    $tmp=$path.'.a7108-'.bin2hex(random_bytes(4)).'.tmp';
    if(file_put_contents($tmp,$bytes,LOCK_EX)===false)throw new RuntimeException("Write failed $tmp");
    // Windows may refuse rename() over an existing destination; fall back to a
    // locked write after preflight checksum and an on-disk original backup.
    if(!@rename($tmp,$path)){
        if(file_put_contents($path,$bytes,LOCK_EX)===false){@unlink($tmp);throw new RuntimeException("Write failed $path");}
        @unlink($tmp);
    }
    if(hash_file('sha256',$path)!==hash('sha256',$bytes))throw new RuntimeException("Post-write checksum mismatch $path");
}
function a7108_nav_main(array $argv): int
{
    $root=dirname(__DIR__);$arg=$argv[1]??'';
    $targets=['resources/views/deliveries/show.blade.php','resources/views/installations/show.blade.php','resources/views/maintenance/_workflow.blade.php'];
    if(count($argv)!==2||($arg!=='--check'&&$arg!=='--apply'&&!str_starts_with($arg,'--rollback='))){
        fwrite(STDERR,"Usage: php tools/a7108_navigation_fix.php --check|--apply|--rollback=BACKUP_ID\n");return 2;
    }
    try{
        $base=$root.'/storage/app/private/a7108_nav_backups';
        if(str_starts_with($arg,'--rollback=')){
            $id=substr($arg,11);
            if(!preg_match('/\A[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\z/D',$id))throw new RuntimeException('Invalid backup ID');
            $dir=$base.'/'.$id;$path=$dir.'/manifest.json';
            if(!is_file($path))throw new RuntimeException('Backup manifest missing');
            $manifest=json_decode((string)file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
            if(!is_array($manifest['files']??null)||count($manifest['files'])!==3)throw new RuntimeException('Backup manifest invalid');
            $restore=[];$current=[];
            foreach($targets as $rel){
                if(!isset($manifest['files'][$rel]))throw new RuntimeException("Backup target missing $rel");
                $m=$manifest['files'][$rel];$file=$root.'/'.$rel;$backup=$dir.'/'.$rel;
                if(!is_file($backup)||!is_file($file))throw new RuntimeException("Backup/source missing $rel");
                $before=(string)file_get_contents($backup);$now=(string)file_get_contents($file);
                if(hash('sha256',$before)!==($m['before']??''))throw new RuntimeException("Backup checksum invalid $rel");
                if(hash('sha256',$now)!==($m['after']??''))throw new RuntimeException("Working file changed since patch $rel");
                $restore[$rel]=$before;$current[$rel]=$now;
            }
            $written=[];
            try{foreach($restore as $rel=>$bytes){a7108_nav_atomic($root.'/'.$rel,$bytes);$written[]=$rel;}}
            catch(\Throwable $e){foreach($written as $rel)a7108_nav_atomic($root.'/'.$rel,$current[$rel]);throw $e;}
            echo "[RESULT] A7108_NAVIGATION_ROLLED_BACK; files=3 (no database changes)\n";return 0;
        }
        $changes=[];$states=[];
        foreach($targets as $rel){
            $file=$root.'/'.$rel;if(!is_file($file))throw new RuntimeException("Missing $rel");
            $source=file_get_contents($file);if($source===false)throw new RuntimeException("Unreadable $rel");
            $rewrite=a7108_nav_rewrite($rel,$source);
            $changes[$rel]=['before'=>$source,'after'=>$rewrite['after'],'state'=>$rewrite['state']];
            $states[$rewrite['state']]=true;
        }
        if(count($states)!==1)throw new RuntimeException('Mixed old/new patch states; refusing partial update');
        a7108_nav_validate($changes,$root);
        $state=array_key_first($states);
        echo '[CHECK] files=3 state='.$state.' (no database operations)'.PHP_EOL;
        if($state==='new'){
            echo '[RESULT] A7108_NAVIGATION_ALREADY_FIXED (no files modified)'.PHP_EOL;return 0;
        }
        if($arg==='--check'){
            echo '[RESULT] A7108_NAVIGATION_FIX_READY (no files modified)'.PHP_EOL;return 0;
        }
        $id=date('Ymd-His').'-'.bin2hex(random_bytes(4));$dir=$base.'/'.$id;$manifest=['id'=>$id,'files'=>[]];
        foreach($changes as $rel=>$entry){
            $dest=$dir.'/'.$rel;
            if(!is_dir(dirname($dest))&&!mkdir(dirname($dest),0775,true)&&!is_dir(dirname($dest)))throw new RuntimeException("Cannot create backup $dest");
            if(file_put_contents($dest,$entry['before'],LOCK_EX)===false)throw new RuntimeException("Cannot back up $rel");
            $manifest['files'][$rel]=['before'=>hash('sha256',$entry['before']),'after'=>hash('sha256',$entry['after'])];
        }
        if(file_put_contents($dir.'/manifest.json',json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),LOCK_EX)===false)throw new RuntimeException('Cannot write backup manifest');
        $written=[];
        try{
            foreach($changes as $rel=>$entry){
                if(hash('sha256',(string)file_get_contents($root.'/'.$rel))!==hash('sha256',$entry['before']))throw new RuntimeException("Working file changed during preflight: $rel");
                a7108_nav_atomic($root.'/'.$rel,$entry['after']);$written[]=$rel;
            }
        }catch(\Throwable $e){foreach($written as $rel)a7108_nav_atomic($root.'/'.$rel,$changes[$rel]['before']);throw $e;}
        echo '[BACKUP_ID] '.$id.PHP_EOL;
        echo '[BACKUP] storage/app/private/a7108_nav_backups/'.$id.PHP_EOL;
        echo '[RESULT] A7108_NAVIGATION_RUNTIME_FIXED; files=3 (no database changes)'.PHP_EOL;
        return 0;
    }catch(\Throwable $e){fwrite(STDERR,'[FAIL] '.$e->getMessage().PHP_EOL.'[RESULT] A7108_NAVIGATION_HOLD; no successful patch applied'.PHP_EOL);return 1;}
}
if(realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__)exit(a7108_nav_main($argv));
