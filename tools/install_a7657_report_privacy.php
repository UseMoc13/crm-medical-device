<?php
/** A7.6.5.7 guarded and idempotent: two surgical patches + one config patch + 4 new files. */
declare(strict_types=1);

$root = dirname(__DIR__);
$dry = in_array('--dry-run', $argv, true);
$statePath = $root . '/tools/a7657_install_state.json';
$payloadRoot = $root . '/tools/a7657_payload/';
$patches = [
    'app/Http/Controllers/ReportController.php' => '880a4162520f205a521d32db7edf6c23cd6276aa',
    'resources/views/reports/index.blade.php' => 'a83952e10ac06d421d0a7733b9898cc59a57227b',
    'config/crm_reporting.php' => '690ff7c2c2f10f6f84245ae784145762f794e482',
];
$adds = [
    'app/Services/CrmReportPrivacyPilot.php',
    'tests/Unit/CrmReportPrivacyPilotPolicyTest.php',
    'tests/Unit/CrmReportPrivacyPilotSourceTest.php',
    'tests/Feature/CrmReportPrivacyPilotHttpTest.php',
];
$norm = static fn (string $s): string => str_replace(["\r\n", "\r"], "\n", $s);
$blob = static fn (string $s): string => sha1('blob ' . strlen($s) . "\0" . $s);
$sha = static fn (string $s): string => hash('sha256', $s);
$read = static function (string $p) use ($norm): string {
    if (!is_file($p) || !is_readable($p)) throw new RuntimeException('Missing or unreadable: ' . $p);
    $s = file_get_contents($p);
    if ($s === false) throw new RuntimeException('Cannot read: ' . $p);
    return $norm($s);
};
$replaceOne = static function (string $text, string $old, string $new): string {
    $count = substr_count($text, $old);
    if ($count !== 1) throw new RuntimeException('Unexpected anchor count ' . $count . ' for: ' . substr($old, 0, 76));
    return str_replace($old, $new, $text);
};
$patchQuery = static function (string $source, string $from, string $to, string $variable) use ($replaceOne): string {
    $a = strpos($source, $from);
    $b = strpos($source, $to);
    if ($a === false || $b === false || $b <= $a) throw new RuntimeException('Report query section drift: ' . $variable);
    $sub = substr($source, $a, $b-$a);
    $sub = $replaceOne($sub, '$'.$variable.' = app(', '$'.$variable.' = $canViewReportDetails ? app(');
    $sub = $replaceOne($sub, '->get();', '->get() : collect();');
    return substr($source, 0, $a) . $sub . substr($source, $b);
};
$patchViewBlock = static function (string $source, string $heading): string {
    $h = strpos($source, '<h3>'.$heading.'</h3>');
    if ($h === false || substr_count($source, '<h3>'.$heading.'</h3>') !== 1) throw new RuntimeException('Missing view section: '.$heading);
    $body = strpos($source, '<div class="card-body">', $h);
    $end = $body === false ? false : strpos($source, "\n        </div>", $body);
    if ($end === false) throw new RuntimeException('Card boundary drift for: '.$heading);
    $insideStart = $body + strlen('<div class="card-body">');
    $inside = substr($source, $insideStart, $end - $insideStart);
    if (substr_count($inside, "@include('reports._bars', [") !== 1) throw new RuntimeException('Card content drift: '.$heading);
    $replacement = "\n            @if(\$canViewReportDetails)" . $inside
        . "\n            @else\n                <div class=\"rp-empty\">Invoice details restricted for this account.</div>\n            @endif";
    return substr($source, 0, $insideStart) . $replacement . substr($source, $end);
};
$patchFile = static function (string $rel, string $text) use ($replaceOne, $patchQuery, $patchViewBlock): string {
    if ($rel === 'config/crm_reporting.php') {
        return $replaceOne($text,
            "    'csv_invoice_all_scope_pilot_enabled' => (bool) env('CRM_REPORT_CSV_INVOICE_ALL_SCOPE_ENABLED', false),",
            "    'csv_invoice_all_scope_pilot_enabled' => (bool) env('CRM_REPORT_CSV_INVOICE_ALL_SCOPE_ENABLED', false),\n"
            . "    // OFF until Finance/Management approve which named report details may be displayed.\n"
            . "    'report_customer_product_privacy_pilot_enabled' => (bool) env('CRM_REPORT_DETAIL_PRIVACY_PILOT_ENABLED', false),");
    }
    if ($rel === 'app/Http/Controllers/ReportController.php') {
        $text = $replaceOne($text,
            "        \$today = today()->toDateString();\n\n        /* ---------- Invoice KPI ---------- */",
            "        \$today = today()->toDateString();\n\n"
            . "        // A7.6.5.7: permission check precedes sensitive customer/product queries.\n"
            . "        \$reportPrivacy = app(\\App\\Services\\CrmReportPrivacyPilot::class)\n"
            . "            ->forViewer(Auth::guard('web')->user());\n"
            . "        \$canViewReportDetails = \$reportPrivacy['customer_product_details'];\n"
            . "        \$canExportInvoiceCsv = \$reportPrivacy['invoice_csv'];\n\n"
            . "        /* ---------- Invoice KPI ---------- */");
        $text = $patchQuery($text, '/* ---------- Top customers ---------- */', '/* ---------- Top products ---------- */', 'topCustomers');
        $text = $patchQuery($text, '/* ---------- Top products ---------- */', '/* ---------- Service & Maintenance ---------- */', 'topProducts');
        $text = $replaceOne($text,
            "            'topProducts' => \$topProducts,",
            "            'topProducts' => \$topProducts,\n"
            . "            'canViewReportDetails' => \$canViewReportDetails,\n"
            . "            'canExportInvoiceCsv' => \$canExportInvoiceCsv,");
        $text = $replaceOne($text,
            "if ((bool) config('crm_reporting.csv_invoice_all_scope_pilot_enabled', false)) {",
            "if ((bool) config('crm_reporting.csv_invoice_all_scope_pilot_enabled', false)\n"
            . "            || (bool) config('crm_reporting.report_customer_product_privacy_pilot_enabled', false)) {");
        return $text;
    }
    if ($rel === 'resources/views/reports/index.blade.php') {
        $text = $replaceOne($text,
            "        <a href=\"{{ route('reports.export.invoices', \$currentRange) }}\" class=\"btn primary\">\n"
            . "            Export Invoices (CSV)\n        </a>",
            "        @if(\$canExportInvoiceCsv)\n"
            . "            <a href=\"{{ route('reports.export.invoices', \$currentRange) }}\" class=\"btn primary\">\n"
            . "                Export Invoices (CSV)\n            </a>\n"
            . "        @else\n"
            . "            <span class=\"btn\" title=\"Invoice View permission and full Invoice scope required\" aria-disabled=\"true\">Export Invoices (CSV) — Restricted</span>\n"
            . "        @endif");
        $text = $patchViewBlock($text, 'Top Customers');
        return $patchViewBlock($text, 'Top Products');
    }
    throw new RuntimeException('Unexpected patch target');
};

try {
    if (is_file($statePath)) {
        $state = json_decode($read($statePath), true, 512, JSON_THROW_ON_ERROR);
        if (!isset($state['after']) || !isset($state['adds'])) throw new RuntimeException('State manifest incomplete');
        foreach ($state['after'] as $rel => $fingerprint) {
            if ($sha($read($root.'/'.$rel)) !== $fingerprint) throw new RuntimeException('Installed file modified: '.$rel);
        }
        foreach ($state['adds'] as $rel => $fingerprint) {
            if ($sha($read($root.'/'.$rel)) !== $fingerprint) throw new RuntimeException('Installed added file modified: '.$rel);
        }
        echo "[PASS] A7.6.5.7 already installed; all fingerprints match. No changes.\n";
        exit(0);
    }
    $before = $after = $added = [];
    foreach ($patches as $rel => $expected) {
        $actual = $read($root.'/'.$rel);
        if ($blob($actual) !== $expected) throw new RuntimeException('Source drift; refusing patch: '.$rel.' (Git blob '.$blob($actual).')');
        $before[$rel] = $actual;
        $after[$rel] = $patchFile($rel, $actual);
        echo '[CHECK] patch: '.$rel."\n";
    }
    foreach ($adds as $rel) {
        if (file_exists($root.'/'.$rel)) throw new RuntimeException('Existing file collision: '.$rel);
        $added[$rel] = $read($payloadRoot.$rel);
        echo '[CHECK] add: '.$rel."\n";
    }
    if ($dry) { echo "[PASS] Dry run. No files, DB records, grants, or Git index changed.\n"; exit(0); }
    $suffix = date('Ymd-His').'-'.substr(bin2hex(random_bytes(4)),0,8);
    $backup = $root.'/tools/a7657_backups/'.$suffix;
    if (!mkdir($backup, 0775, true) && !is_dir($backup)) throw new RuntimeException('Could not create backup');
    foreach ($before as $rel => $_) {
        $dst = $backup.'/'.$rel;
        if (!is_dir(dirname($dst)) && !mkdir(dirname($dst), 0775, true)) throw new RuntimeException('Backup directory error');
        if (!copy($root.'/'.$rel, $dst)) throw new RuntimeException('Backup failed: '.$rel);
    }
    $written = [];
    try {
        foreach ($after as $rel => $content) {
            if (file_put_contents($root.'/'.$rel, $content, LOCK_EX) === false) throw new RuntimeException('Write failed: '.$rel);
            $written[] = $rel;
        }
        foreach ($added as $rel => $content) {
            $dst=$root.'/'.$rel;
            if (!is_dir(dirname($dst)) && !mkdir(dirname($dst),0775,true)) throw new RuntimeException('Directory failed');
            if (file_put_contents($dst,$content,LOCK_EX)===false) throw new RuntimeException('Add failed: '.$rel);
            $written[]=$rel;
        }
        $manifest = ['stage'=>'A7.6.5.7','after'=>array_map($sha,$after),'adds'=>array_map($sha,$added),
            'backup'=>str_replace('\\','/',$backup),'flag_default'=>'OFF'];
        if (file_put_contents($statePath,json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),LOCK_EX)===false) throw new RuntimeException('Manifest write failed');
    } catch (Throwable $e) {
        foreach ($written as $rel) {
            if (isset($before[$rel])) copy($backup.'/'.$rel,$root.'/'.$rel);
            else @unlink($root.'/'.$rel);
        }
        throw $e;
    }
    echo '[PASS] A7.6.5.7 installed. Backup: '.$backup."\n";
    echo "[INFO] Report privacy pilot defaults OFF. No migrations, grants, CRM records or financial calculations changed.\n";
} catch (Throwable $e) {
    fwrite(STDERR, '[FAIL] '.$e->getMessage()."\n");
    exit(1);
}
