<?php
/**
 * A7.10.8 CRM source/route/Blade/HTML link audit.
 * Read-only: uses Laravel bootstrap and registered routes, compiles templates in memory,
 * PHP-lints temporary files, never runs HTTP mutation requests or migrations.
 * Requires PHP >=8.2 and the app's installed composer dependencies.
 */
declare(strict_types=1);

function qa_issues_add(array &$issues, string $level, string $category, string $file, int $line, string $detail): void
{
    $key = implode('|', [$level, $category, $file, $line, $detail]);
    $issues[$key] = ['level'=>$level,'category'=>$category,'file'=>$file,'line'=>$line,'detail'=>$detail];
}
function qa_line(string $source, int $offset): int
{
    return substr_count($source, "\n", 0, $offset) + 1;
}
function qa_lint(string $source): ?string
{
    $file = tempnam(sys_get_temp_dir(), 'crmqa_');
    if ($file === false) return 'Cannot create temporary lint file';
    try {
        if (file_put_contents($file, $source) === false) return 'Cannot write temporary lint file';
        $lines=[]; $status=0;
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($file).' 2>&1', $lines, $status);
        return $status === 0 ? null : trim(implode(' ', $lines));
    } finally { @unlink($file); }
}
/** Remove JS, Blade/PHP/HTML comments before looking for links; retain offsets. */
function qa_markup(string $s): string
{
    return preg_replace_callback(
        '~<script\b[^>]*>[\s\S]*?</script\s*>|<style\b[^>]*>[\s\S]*?</style\s*>|<!--(?:(?!-->)[\s\S])*-->|\{\{--[\s\S]*?--\}\}~i',
        static fn(array $m): string => preg_replace('/[^\n]/', ' ', $m[0]),
        $s
    ) ?? $s;
}
/** @return array<string,string> */
function qa_attrs(string $opening): array
{
    $attrs=[];
    preg_match_all('~\b([a-z][-a-z0-9_:]*)\s*=\s*("[^"]*"|\x27[^\x27]*\x27|[^\s>]+)~i', $opening, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) $attrs[strtolower($match[1])] = trim($match[2], " \t\n\r\0\x0B'\"");
    return $attrs;
}
/** @return ?string */
function qa_first_route(string $expr): ?string
{
    return preg_match('~\broute\s*\(\s*([\x27\"])([A-Za-z0-9_.-]+)\1~', $expr, $m) ? $m[2] : null;
}
/** @return ?string */
function qa_form_method(string $opening, string $inner): ?string
{
    $attr=qa_attrs($opening);
    $method=strtoupper($attr['method']??'GET');
    if ($method !== 'POST') return $method;
    if (preg_match('~@method\s*\(\s*([\x27\"])(GET|POST|PUT|PATCH|DELETE)\1\s*\)~i', $inner, $m)) return strtoupper($m[2]);
    if (preg_match('~<input\b[^>]*\bname\s*=\s*([\x27\"])_method\1[^>]*>~i', $inner, $m)) {
        if (preg_match('~\bvalue\s*=\s*([\x27\"])(GET|POST|PUT|PATCH|DELETE)\1~i', $m[0], $v)) return strtoupper($v[2]);
    }
    return 'POST';
}
/**
 * Inspect one template without making any requests.
 * @param array<string,array{methods:list<string>,uri:string,defaults:array}> $routes
 */
function qa_scan_view(string $source, string $file, array $routes, string $root, array &$issues, array &$stat): void
{
    $markup=qa_markup($source);
    // Route names in Blade-backed <script> blocks are also real route references.
    $routeMarkup=preg_replace_callback('~<!--(?:(?!-->)[\s\S])*-->|\{\{--[\s\S]*?--\}\}~',
        static fn(array $m): string=>preg_replace('/[^\n]/',' ',$m[0]),$source)??$source;
    preg_match_all('~\broute\s*\(\s*([\x27\"])([A-Za-z0-9_.-]+)\1~', $routeMarkup, $calls, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    foreach ($calls as $m) {
        $name=$m[2][0]; $line=qa_line($source, $m[0][1]); $stat['named_route_calls']++;
        if (!isset($routes[$name])) {
            $conditional=(str_contains($source,"Route::has('$name')")||str_contains($source,'Route::has("'.$name.'")'));
            qa_issues_add($issues,$conditional?'WARN':'ERROR',$conditional?'ROUTE_OPTIONAL':'ROUTE_MISSING',$file,$line,"route('$name') is not registered");
        }
    }
    preg_match_all('~\broute\s*\(\s*([\x27\"])([A-Za-z0-9_.-]+)\1\s*\)~', $routeMarkup, $calls, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    foreach ($calls as $m) {
        $name=$m[2][0]; if(!isset($routes[$name]))continue;
        $uri=$routes[$name]['uri'];
        preg_match_all('~\{([a-zA-Z_][a-zA-Z_0-9]*)(\?)?\}~', $uri, $segments, PREG_SET_ORDER);
        $need=array_filter($segments,static fn($x)=>!isset($x[2])||$x[2]!=='?');
        $defaults=$routes[$name]['defaults'];
        $required=array_filter($need,static fn($x)=>!array_key_exists($x[1],$defaults));
        if ($required) qa_issues_add($issues,'ERROR','ROUTE_PARAMS',$file,qa_line($source,$m[0][1]),"route('$name') lacks required URL parameters");
    }
    preg_match_all('~@(?:include|extends|component)\s*\(\s*([\x27\"])([A-Za-z0-9_.:-]+)\1~', $markup, $includes, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
    foreach ($includes as $m) {
        $target=$m[2][0]; if(str_contains($target,'::'))continue;
        $stat['blade_view_references']++;
        $path=$root.'/resources/views/'.str_replace('.','/',$target).'.blade.php';
        if(!is_file($path))qa_issues_add($issues,'ERROR','VIEW_MISSING',$file,qa_line($source,$m[0][1]),"@include/@extends target '$target' not found");
    }
    // Route-based links are expected to target GET endpoints.
    preg_match_all('~<a\b(?:(?:"[^"]*"|\x27[^\x27]*\x27|[^\x27">])*)>~is', $markup, $anchors,PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
    foreach($anchors as $m){
        $attrs=qa_attrs($m[0][0]);$name=qa_first_route($attrs['href']??'');
        if($name===null||!isset($routes[$name]))continue;
        $stat['route_links']++;
        if(!in_array('GET',$routes[$name]['methods'],true)){
            qa_issues_add($issues,'ERROR','LINK_METHOD',$file,qa_line($source,$m[0][1]),"Link to '$name' uses GET but route accepts ".implode('/',$routes[$name]['methods']));
        }
    }
    // Route-based forms: validate real browser method, including Laravel spoofing.
    preg_match_all('~<form\b(?:(?:"[^"]*"|\x27[^\x27]*\x27|[^\x27">])*)>~is', $markup,$forms,PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
    foreach($forms as $m){
        $opening=$m[0][0];$start=$m[0][1];
        $end=stripos($markup,'</form>', $start+strlen($opening));
        if($end===false){qa_issues_add($issues,'ERROR','FORM_UNCLOSED',$file,qa_line($source,$start),'Unclosed <form> element');continue;}
        $body=substr($source,$start+strlen($opening),$end-$start-strlen($opening));
        $name=qa_first_route(qa_attrs($opening)['action']??'');
        $verb=qa_form_method($opening,$body);
        $stat['forms']++;
        if($verb!=='GET'&&!preg_match('~@csrf\b|csrf_field\s*\(|\bname\s*=\s*([\x27\"])_token\1~i',$body)){
            qa_issues_add($issues,'WARN','CSRF_CHECK',$file,qa_line($source,$start),"Mutating $verb form has no explicit @csrf within its form body (check included partials)");
        }
        if($name===null||!isset($routes[$name]))continue;
        $stat['route_forms']++;
        if(!in_array($verb,$routes[$name]['methods'],true)){
            qa_issues_add($issues,'ERROR','FORM_METHOD',$file,qa_line($source,$start),"Form '$name': actual $verb does not match route ".implode('/',$routes[$name]['methods']));
        }
    }
    $stat['files']++;
}
function qa_main(string $root): int
{
    $issues=[]; $stat=['files'=>0,'php_sources'=>0,'blade_compiled'=>0,'named_routes'=>0,'named_route_calls'=>0,'route_links'=>0,'route_forms'=>0,'forms'=>0,'blade_view_references'=>0,'controllers'=>0];
    if(!is_file($root.'/vendor/autoload.php')||!is_file($root.'/bootstrap/app.php')){
        fwrite(STDERR,"[ERROR] Composer/vendor or bootstrap missing. No changes made.\n");return 2;
    }
    try{
        require_once $root.'/vendor/autoload.php';
        $app=require $root.'/bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $compiler=$app->make('blade.compiler');
        $registered=[];$signatures=[];
        foreach(\Illuminate\Support\Facades\Route::getRoutes()->getRoutes() as $route){
            $methods=array_values(array_filter($route->methods(),static fn($method)=>$method!=='HEAD'));
            $name=$route->getName();$uri=$route->uri();
            if($name){
                if(isset($registered[$name])&&$registered[$name]['uri']!==$uri)qa_issues_add($issues,'ERROR','ROUTE_DUPLICATE','routes/*',0,"Duplicate named route '$name'");
                $registered[$name]=['methods'=>$methods,'uri'=>$uri,'defaults'=>$route->defaults];
            }
            foreach($methods as $method){
                $signature=$method.'|'.($route->getDomain()??''). '|'.$uri;
                if(isset($signatures[$signature]))qa_issues_add($issues,'WARN','ROUTE_OVERLAP','routes/*',0,"Overlapping $method $uri (may shadow another route)");
                $signatures[$signature]=true;
            }
            $action=$route->getActionName();
            if(str_contains($action,'@')){
                [$class,$method]=explode('@',$action,2);$stat['controllers']++;
                if(!class_exists($class)||!method_exists($class,$method))qa_issues_add($issues,'ERROR','CONTROLLER_MISSING','routes/*',0,"Action $action is not callable");
            }
        }
        $stat['named_routes']=count($registered);
        $walk=function(string $folder)use($root,&$issues,&$stat,$compiler,$registered):void{
            if(!is_dir($root.'/'.$folder))return;
            $it=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root.'/'.$folder,\FilesystemIterator::SKIP_DOTS));
            foreach($it as $entry){
                if(!$entry->isFile()||!str_ends_with($entry->getFilename(),'.php'))continue;
                $absolute=$entry->getPathname();$rel=str_replace('\\','/',substr($absolute,strlen($root)+1));
                if(str_contains($rel,'/.git/')||str_contains($rel,'/vendor/'))continue;
                $source=file_get_contents($absolute);
                if($source===false){qa_issues_add($issues,'ERROR','READ_FAIL',$rel,0,'Cannot read PHP/Blade file');continue;}
                if(str_ends_with($rel,'.blade.php')){
                    try{
                        $compiled=$compiler->compileString($source);
                        $lint=qa_lint($compiled);
                        if($lint!==null)qa_issues_add($issues,'ERROR','BLADE_PARSE',$rel,0,$lint);
                        else $stat['blade_compiled']++;
                    }catch(\Throwable $e){qa_issues_add($issues,'ERROR','BLADE_COMPILE',$rel,0,$e->getMessage());}
                    qa_scan_view($source,$rel,$registered,$root,$issues,$stat);
                }else{
                    $stat['php_sources']++;
                    $lint=qa_lint($source);
                    if($lint!==null)qa_issues_add($issues,'ERROR','PHP_PARSE',$rel,0,$lint);
                }
            }
        };
        // Restrict source PHP lint to application code; never vendor or storage.
        foreach(['app','routes','resources/views'] as $dir)$walk($dir);
        usort($issues,static fn($a,$b)=>strcmp($a['level'],$b['level'])?:strcmp($a['category'],$b['category'])?:strcmp($a['file'],$b['file'])?:($a['line']<=>$b['line']));
        $error=count(array_filter($issues,static fn($v)=>$v['level']==='ERROR'));
        $warning=count($issues)-$error;
        echo '[COUNTS] '.implode(' ',array_map(static fn($k)=>$k.'='.$stat[$k],array_keys($stat))).PHP_EOL;
        $limit=100;
        foreach(array_slice($issues,0,$limit) as $v)printf('[%s] %s %s:%d %s'.PHP_EOL,$v['level'],$v['category'],$v['file'],$v['line'],$v['detail']);
        if(count($issues)>$limit)echo '[NOTICE] '.(count($issues)-$limit).' more findings; use --json for complete report'.PHP_EOL;
        echo '[SUMMARY] errors='.$error.' warnings='.$warning.PHP_EOL;
        echo '[SCOPE] Static route/link/form checks and Blade compilation only; browser clicks, data-dependent controller execution and JS runtime require manual UAT'.PHP_EOL;
        echo '[RESULT] '.($error===0?'A7108_STATIC_AUDIT_COMPLETE':'A7108_STATIC_AUDIT_FINDINGS').' (no project/data writes)'.PHP_EOL;
        return $error>0?1:0;
    }catch(\Throwable $e){
        fwrite(STDERR,'[FATAL] '.$e->getMessage().PHP_EOL.'[RESULT] A7108_AUDIT_BLOCKED (no project/data writes)'.PHP_EOL);
        return 2;
    }
}
function qa_self_test(): int
{
    $i=[];$s=['files'=>0,'named_route_calls'=>0,'route_links'=>0,'route_forms'=>0,'forms'=>0,'blade_view_references'=>0];
    $r=[
        'deliveries.change-status'=>['methods'=>['PATCH'],'uri'=>'deliveries/{delivery}/change-status','defaults'=>[]],
        'deliveries.index'=>['methods'=>['GET'],'uri'=>'deliveries','defaults'=>[]],
        'deliveries.show'=>['methods'=>['GET'],'uri'=>'deliveries/{delivery}','defaults'=>[]],
    ];
    $bad=<<<'BLADE'
<form method="POST" action="{{ route('deliveries.change-status', $delivery) }}">@csrf<button>Save</button></form>
<a href="{{ route('deliveries.show') }}">Show</a>
<a href="{{ route('deliveries.change-status', $delivery) }}">Bad Link</a>
BLADE;
    qa_scan_view($bad,'fixture.blade.php',$r,__DIR__,$i,$s);
    $types=array_column($i,'category');
    foreach(['FORM_METHOD','ROUTE_PARAMS','LINK_METHOD'] as $required){
        if(!in_array($required,$types,true)){fwrite(STDERR,"[FAIL] Fixture missed $required\n");return 1;}
    }
    $i=[];
    $good=<<<'BLADE'
<form method="POST" action="{{ route('deliveries.change-status', $delivery) }}">@csrf @method('PATCH')<button>Save</button></form>
<a href="{{ route('deliveries.show', $delivery) }}">Show</a>
BLADE;
    qa_scan_view($good,'fixture.blade.php',$r,__DIR__,$i,$s);
    if($i){fwrite(STDERR,'[FAIL] Clean fixture flagged: '.json_encode($i).PHP_EOL);return 1;}
    $mut=<<<'BLADE'
<form method="POST" action="{{ route('deliveries.change-status', $delivery) }}">@csrf <input type="hidden" name="_method" value="PATCH"></form>
BLADE;
    qa_scan_view($mut,'fixture.blade.php',$r,__DIR__,$i,$s);
    if($i){fwrite(STDERR,'[FAIL] Spoofed PATCH fixture flagged: '.json_encode($i).PHP_EOL);return 1;}
    echo "[PASS] Form POST/PATCH mismatch detected\n[PASS] Required route parameters detected\n[PASS] GET links cannot target PATCH routes\n[PASS] Valid PATCH and spoofed PATCH accepted\n[RESULT] A7108_SELF_TEST_PASS\n";
    return 0;
}
if(realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__){
    $arg=$argv[1]??'--help';
    if($arg==='--self-test')exit(qa_self_test());
    if($arg==='--check'&&count($argv)===2)exit(qa_main(dirname(__DIR__)));
    fwrite(STDERR,"Usage: php tools/a7108_full_audit.php --self-test|--check\n");exit(2);
}
