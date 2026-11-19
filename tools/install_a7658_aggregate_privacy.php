<?php
/** A7.6.5.8 - source-fingerprinted, idempotent, transactional file installer. */
declare(strict_types=1);

$root = dirname(__DIR__);
$dryRun = in_array('--dry-run', $argv, true);
$statePath = $root.'/tools/a7658_install_state.json';
$payloadRoot = $root.'/tools/a7658_payload/';
// Both known variants are exact A7.6.5.7 outputs. Refuse every unknown edit.
$patches = [
    'app/Http/Controllers/ReportController.php' => [
        '4d23631f32eb1b86ad34154e60ee1b3a17b12441',
        '8a1b6f8e898990518ba6c90fd7672b73b7904788',
    ],
    'resources/views/reports/index.blade.php' => ['a36398e6f1db119295a880c8f2ee55cdb4a4766b'],
    'config/crm_reporting.php' => ['41afe2ccc7376fd5154c01c9e01e4fa7f53461cf'],
];
$adds = [
    'app/Services/CrmReportAggregatePrivacyPilot.php',
    'tests/Unit/CrmReportAggregatePrivacyPolicyTest.php',
    'tests/Unit/CrmReportAggregatePrivacySourceTest.php',
    'tests/Feature/CrmReportAggregatePrivacyIsolationTest.php',
];
$normal = static fn(string $text): string => str_replace(["\r\n", "\r"], "\n", $text);
$blob = static fn(string $text): string => sha1('blob '.strlen($text)."\0".$text);
$sha = static fn(string $text): string => hash('sha256', $text);
$read = static function(string $path) use ($normal): string {
    if (!is_file($path) || !is_readable($path)) throw new RuntimeException('Missing/unreadable: '.$path);
    $contents = file_get_contents($path);
    if ($contents === false) throw new RuntimeException('Unable to read: '.$path);
    return $normal($contents);
};
$replace = static function(string $text, string $before, string $after): string {
    $count = substr_count($text, $before);
    if ($count !== 1) throw new RuntimeException('Source anchor drift ('.$count.'): '.substr($before,0,68));
    return str_replace($before, $after, $text);
};
$section = static function(string $text, string $from, string $to, callable $callback): string {
    $start = strpos($text, $from);
    $stop = $start === false ? false : strpos($text, $to, $start + strlen($from));
    if ($start === false || $stop === false || $stop <= $start) {
        throw new RuntimeException('Source section drift: '.$from);
    }
    $original = substr($text,$start,$stop-$start);
    $changed = $callback($original);
    if ($original === $changed) throw new RuntimeException('Section unchanged: '.$from);
    return substr($text,0,$start).$changed.substr($text,$stop);
};
$wrap = static function(string $text, string $startMarker, string $endMarker, string $flag, string $name): string {
    $start = strpos($text, $startMarker);
    $end = $start === false ? false : strpos($text, $endMarker, $start + strlen($startMarker));
    if ($start === false || $end === false || $start >= $end) throw new RuntimeException('Blade section drift: '.$name);
    $original = substr($text, $start, $end-$start);
    $replacement = '@if($'.$flag.")\n".$original."@else\n"
        .'<div class="card rp-block"><div class="card-head"><h3>'.$name.'</h3></div>' . "\n"
        .'    <div class="card-body"><div class="rp-empty">'.$name.' restricted for this account.</div></div></div>' . "\n"
        ."@endif\n\n";
    return substr($text,0,$start).$replacement.substr($text,$end);
};
$wrapCard = static function(string $text, string $heading, string $flag): string {
    $marker = '<h3>'.$heading.'</h3>';
    if (substr_count($text,$marker)!==1) throw new RuntimeException('Blade card header drift: '.$heading);
    $pos = strpos($text,$marker);
    $start = strrpos(substr($text,0,$pos), '    <section class="card rp-block">');
    $end = strpos($text,"\n    </section>",$pos);
    if ($start === false || $end === false) throw new RuntimeException('Blade card boundary drift: '.$heading);
    $end += strlen("\n    </section>");
    $content = substr($text,$start,$end-$start);
    $masked = '    <section class="card rp-block"><div class="card-head"><h3>'.$heading.'</h3></div>' . "\n"
        .'        <div class="card-body"><div class="rp-empty">'.$heading.' restricted for this account.</div></div></section>';
    $replacement = '    @if($'.$flag.")\n".$content."\n    @else\n".$masked."\n    @endif";
    return substr($text,0,$start).$replacement.substr($text,$end);
};
$patchOne = static function(string $relative, string $text) use ($replace,$section,$wrap,$wrapCard): string {
    if ($relative === 'config/crm_reporting.php') {
        $old = "    'report_customer_product_privacy_pilot_enabled' => (bool) env('CRM_REPORT_DETAIL_PRIVACY_PILOT_ENABLED', false),";
        return $replace($text, $old, $old."\n    // OFF until management approves per-module aggregate visibility.\n"
            ."    'report_aggregate_privacy_pilot_enabled' => (bool) env('CRM_REPORT_AGGREGATE_PRIVACY_PILOT_ENABLED', false),");
    }
    if ($relative === 'app/Http/Controllers/ReportController.php') {
        $text = $replace($text,
            "        \$canViewReportDetails = \$reportPrivacy['customer_product_details'];\n"
            ."        \$canExportInvoiceCsv = \$reportPrivacy['invoice_csv'];",
            "        // A7.6.5.8: group-level opt-in guards. All decisions precede business queries.\n"
            ."        \$aggregatePrivacy = app(\\App\\Services\\CrmReportAggregatePrivacyPilot::class)\n"
            ."            ->forViewer(Auth::guard('web')->user());\n"
            ."        \$canViewFinancial = \$aggregatePrivacy['financial'];\n"
            ."        \$canViewOperations = \$aggregatePrivacy['operations'];\n"
            ."        \$canViewPipeline = \$aggregatePrivacy['pipeline'];\n"
            ."        \$canViewWarranty = \$aggregatePrivacy['warranty'];\n"
            ."        \$canViewReportDetails = \$reportPrivacy['customer_product_details'] && \$canViewFinancial;\n"
            ."        \$canExportInvoiceCsv = \$reportPrivacy['invoice_csv'] && \$canViewFinancial;");
        $text = $section($text,'/* ---------- Invoice KPI ---------- */','/* ---------- Monthly chart ---------- */',static function(string $s) use ($replace): string {
            $s = $replace($s, '$kpi = app(', '$kpi = $canViewFinancial ? app(');
            $s = $replace($s,'->first();','->first() : null;');
            return $replace($s,'$collectionRate = (float) $kpi->invoiced > 0',
                '$collectionRate = $canViewFinancial && (float) $kpi->invoiced > 0');
        });
        $text = $replace($text,'$monthly = $this->monthlySeries($from, $to);',
            '$monthly = $canViewFinancial ? $this->monthlySeries($from, $to) : [];');
        $text = $section($text,'/* ---------- Status breakdown ---------- */','/* ---------- Top customers ---------- */',static function(string $s) use ($replace): string {
            $s = $replace($s,'$statusBreakdown = ','$statusBreakdown = $canViewFinancial ? ');
            return $replace($s,'->get();','->get() : collect();');
        });
        $text = $section($text,'/* ---------- Service & Maintenance ---------- */','/* ---------- Pipeline & Warranty',static function(string $s) use ($replace): string {
            $s = $replace($s,'$tickets = [','$tickets = $canViewOperations ? [');
            $s = $replace($s, "        ];\n\n        \$maintenance = [",
                "        ] : ['total' => 0, 'open' => 0, 'avg_hours' => null,\n"
                ."            'by_status' => collect(), 'by_priority' => collect()];\n\n"
                ."        \$maintenance = \$canViewOperations ? [");
            $end = strrpos($s,'        ];');
            if ($end === false || trim(substr($s,$end+strlen('        ];'))) !== '') throw new RuntimeException('Maintenance end marker drift');
            return substr($s,0,$end)."        ] : ['schedules' => 0, 'records' => 0, 'overdue_now' => 0,\n"
                ."            'by_type' => collect(), 'by_result' => collect()];".substr($s,$end+strlen('        ];'));
        });
        $text = $section($text,'/* ---------- Pipeline & Warranty',"        return view('reports.index'",static function(string $s) use ($replace): string {
            $s = $replace($s,'$pipeline = ','$pipeline = $canViewPipeline ? ');
            $s = $replace($s,'->get();','->get() : collect();');
            $s = $replace($s,'$warranty = [','$warranty = $canViewWarranty ? [');
            $end = strrpos($s,'        ];');
            if ($end === false || trim(substr($s,$end+strlen('        ];'))) !== '') throw new RuntimeException('Warranty end marker drift');
            return substr($s,0,$end)."        ] : ['by_status' => collect(), 'expiring_soon' => 0];".substr($s,$end+strlen('        ];'));
        });
        $text = $replace($text,"            'canExportInvoiceCsv' => \$canExportInvoiceCsv,",
            "            'canExportInvoiceCsv' => \$canExportInvoiceCsv,\n"
            ."            'canViewFinancial' => \$canViewFinancial,\n"
            ."            'canViewOperations' => \$canViewOperations,\n"
            ."            'canViewPipeline' => \$canViewPipeline,\n"
            ."            'canViewWarranty' => \$canViewWarranty,");
        return $replace($text,
            "|| (bool) config('crm_reporting.report_customer_product_privacy_pilot_enabled', false)) {",
            "|| (bool) config('crm_reporting.report_customer_product_privacy_pilot_enabled', false)\n"
            ."            || (bool) config('crm_reporting.report_aggregate_privacy_pilot_enabled', false)) {");
    }
    if ($relative === 'resources/views/reports/index.blade.php') {
        $text = $wrap($text,'{{-- FINANCIAL KPI --}}','{{-- SERVICE KPI --}}','canViewFinancial','Financial Overview');
        $text = $wrap($text,'{{-- SERVICE KPI --}}','{{-- MONTHLY CHART --}}','canViewOperations','Service Overview');
        $text = $wrap($text,'{{-- MONTHLY CHART --}}','{{-- STATUS + CUSTOMERS --}}','canViewFinancial','Revenue by Month');
        $text = $wrapCard($text,'Invoices by Status','canViewFinancial');
        $text = $wrapCard($text,'Sales Pipeline','canViewPipeline');
        $text = $wrap($text,'{{-- SERVICE DETAIL --}}','{{-- WARRANTY --}}','canViewOperations','Service & Maintenance');
        return $wrap($text,'{{-- WARRANTY --}}',"@include('reports._styles')",'canViewWarranty','Warranty Status');
    }
    throw new RuntimeException('Unexpected patch target: '.$relative);
};

try {
    if (is_file($statePath)) {
        $manifest = json_decode($read($statePath),true,512,JSON_THROW_ON_ERROR);
        if (!is_array($manifest) || !isset($manifest['after'],$manifest['adds'])) throw new RuntimeException('Invalid install manifest');
        foreach ([$manifest['after'],$manifest['adds']] as $entries) {
            foreach ($entries as $path => $expected) {
                if ($sha($read($root.'/'.$path))!==$expected) throw new RuntimeException('Installed file modified: '.$path);
            }
        }
        echo "[PASS] A7.6.5.8 already installed with intact fingerprints. No changes.\n";
        exit(0);
    }
    $before=[];$after=[];$added=[];
    foreach ($patches as $rel => $fingerprints) {
        $source = $read($root.'/'.$rel);
        $id = $blob($source);
        if (!in_array($id,$fingerprints,true)) throw new RuntimeException('Unknown source revision, refusing to overwrite: '.$rel.' (Git blob '.$id.')');
        $before[$rel]=$source;
        $after[$rel]=$patchOne($rel,$source);
        echo '[CHECK] patch: '.$rel."\n";
    }
    foreach ($adds as $rel) {
        if (file_exists($root.'/'.$rel)) throw new RuntimeException('Existing file collision: '.$rel);
        $added[$rel]=$read($payloadRoot.$rel);
        echo '[CHECK] add: '.$rel."\n";
    }
    if ($dryRun) {echo "[PASS] Dry run. No application files, role grants, DB rows or Git index changed.\n";exit(0);}
    $backup=$root.'/tools/a7658_backups/'.date('Ymd-His').'-'.bin2hex(random_bytes(4));
    if (!mkdir($backup,0775,true) && !is_dir($backup)) throw new RuntimeException('Unable to create backup directory');
    foreach ($before as $rel=>$_) {
        $dest=$backup.'/'.$rel;
        if (!is_dir(dirname($dest)) && !mkdir(dirname($dest),0775,true)) throw new RuntimeException('Unable to create backup directory');
        if (!copy($root.'/'.$rel,$dest)) throw new RuntimeException('Unable to back up: '.$rel);
    }
    $written=[];
    try {
        foreach ($after as $rel=>$value) {
            if (file_put_contents($root.'/'.$rel,$value,LOCK_EX)===false) throw new RuntimeException('Unable to patch: '.$rel);
            $written[]=$rel;
        }
        foreach ($added as $rel=>$value) {
            $dest=$root.'/'.$rel;
            if (!is_dir(dirname($dest)) && !mkdir(dirname($dest),0775,true)) throw new RuntimeException('Unable to make directory: '.$rel);
            if (file_put_contents($dest,$value,LOCK_EX)===false) throw new RuntimeException('Unable to add: '.$rel);
            $written[]=$rel;
        }
        $state=['stage'=>'A7.6.5.8','after'=>array_map($sha,$after),'adds'=>array_map($sha,$added),
            'backup'=>str_replace('\\','/',$backup),'pilot_default'=>'OFF'];
        if (file_put_contents($statePath,json_encode($state,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),LOCK_EX)===false) throw new RuntimeException('Manifest write failed');
    } catch (Throwable $error) {
        foreach ($written as $rel) {
            if (isset($before[$rel])) copy($backup.'/'.$rel,$root.'/'.$rel);
            else @unlink($root.'/'.$rel);
        }
        throw $error;
    }
    echo '[PASS] A7.6.5.8 installed. Backup: '.$backup."\n";
    echo "[INFO] Aggregate privacy flag OFF. No migrations, grants, financial records or CSV format changed.\n";
} catch (Throwable $error) {
    fwrite(STDERR,'[FAIL] '.$error->getMessage()."\n");
    exit(1);
}
