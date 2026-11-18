<?php
/** A7.5.3.2: opt-in, permission-scoped CRM status notifications. */
declare(strict_types=1);
$root = dirname(__DIR__);
$dry = in_array('--dry-run', $argv, true);
foreach (array_slice($argv, 1) as $arg) {
    if ($arg !== '--dry-run') { fwrite(STDERR, "Unknown option: $arg\n"); exit(2); }
}
function a7532_once(string $source, string $old, string $new, string $label): array
{
    if (substr_count($source, $new) === 1) return [$source, null];
    if (substr_count($source, $old) !== 1 || str_contains($source, $new)) return [null, "$label baseline differs or patch is partial"];
    return [str_replace($old, $new, $source), null];
}
function a7532_eol(string $text): string { return str_contains($text, "\r\n") ? "\r\n" : "\n"; }
function a7532_patch_preferences(string $s): array
{
    if (substr_count($s, "'crm_status_enabled'") >= 3 && str_contains($s, "'crm_status' => 'crm_status_enabled'")) return [$s, null];
    if (str_contains($s, "'crm_status_enabled'") || str_contains($s, "'crm_status' =>")) return [null, 'Partial preference patch'];
    $e = a7532_eol($s);
    $anchors = [
        "        'reminder_activity_enabled', 'reminder_todo_enabled'," => "        'reminder_activity_enabled', 'reminder_todo_enabled',{$e}        'crm_status_enabled',",
        "        return array_fill_keys(self::FLAGS, true) + ['reminder_lead_minutes' => 1440];" => "        \$defaults = array_fill_keys(self::FLAGS, true);{$e}        \$defaults['crm_status_enabled'] = false; // explicit opt-in{$e}        return \$defaults + ['reminder_lead_minutes' => 1440];",
        "            'approval', 'revision' => 'review_enabled'," => "            'approval', 'revision' => 'review_enabled',{$e}            'crm_status' => 'crm_status_enabled',",
    ];
    foreach ($anchors as $old => $new) {
        if (substr_count($s, $old) !== 1) return [null, 'Unknown notification preference baseline'];
        $s = str_replace($old, $new, $s);
    }
    return [$s, null];
}
function a7532_patch_model(string $s): array
{
    if (substr_count($s, "'crm_status_enabled',") === 1 && substr_count($s, "'crm_status_enabled' => 'boolean'") === 1) return [$s, null];
    if (str_contains($s, 'crm_status_enabled')) return [null, 'Partial notification preference model patch'];
    $e = a7532_eol($s);
    $a = "        'reminder_lead_minutes',";
    $b = "            'reminder_lead_minutes' => 'integer',";
    if (substr_count($s, $a) !== 1 || substr_count($s, $b) !== 1) return [null, 'Unknown notification preference model'];
    $s = str_replace($a, "        'crm_status_enabled',{$e}" . $a, $s);
    $s = str_replace($b, "            'crm_status_enabled' => 'boolean',{$e}" . $b, $s);
    return [$s, null];
}
function a7532_patch_settings(string $s): array
{
    $new = "                'crm_status_enabled' => ['CRM status updates', 'Opt in to status changes for CRM records you can still access.'],";
    if (substr_count($s, $new) === 1) return [$s, null];
    if (str_contains($s, "'crm_status_enabled'")) return [null, 'Partial notification settings patch'];
    $old = "                'review_enabled' => ['Approval and revision decisions', 'A creator has approved or requested revisions to your submission.'],";
    if (substr_count($s, $old) !== 1) return [null, 'Notification Settings baseline changed'];
    return [str_replace($old, $old . a7532_eol($s) . $new, $s), null];
}
function a7532_patch_center(string $s): array
{
    $new = "        'delivery' => ['deliveries.show', 'delivery'],";
    $auth = "            'delivery', 'installation', 'invoice' => app(CrmStatusNotificationAccess::class)->canOpen(\$user, (string) \$notice->target_type, \$id),";
    if (substr_count($s, $new) === 1 && substr_count($s, $auth) === 1) return [$s, null];
    if (str_contains($s, "'delivery' => ['deliveries.show'") || str_contains($s, 'CrmStatusNotificationAccess::class')) return [null, 'Partial notification routing patch'];
    $old = "        'note' => ['notes.show', 'note'],";
    $oldAuth = "            default => false,";
    if (substr_count($s, $old) !== 1 || substr_count($s, $oldAuth) !== 1) return [null, 'Notification target baseline changed'];
    $e = a7532_eol($s);
    $s = str_replace($old, $old . $e . $new . $e . "        'installation' => ['installations.show', 'installation']," . $e . "        'invoice' => ['invoices.show', 'invoice'],", $s);
    return [str_replace($oldAuth, $auth . $e . $oldAuth, $s), null];
}
function a7532_patch_settings_controller(string $s): array
{
    $marker = "if (\$field === 'crm_status_enabled' && !\\Illuminate\\Support\\Facades\\Schema::hasColumn('workspace_notification_preferences', 'crm_status_enabled')) continue;";
    if (str_contains($s, $marker)) return [$s, null];
    if (str_contains($s, "if (\$field === 'crm_status_enabled'")) return [null, 'Partially patched Notification Settings controller'];
    $e = a7532_eol($s);
    $old = '        foreach (WorkspaceNotificationPreferences::FLAGS as $field) {' . $e
         . '            $payload[$field] = $request->boolean($field);';
    $new = '        foreach (WorkspaceNotificationPreferences::FLAGS as $field) {' . $e
         . "            if (\$field === 'crm_status_enabled' && !\\Illuminate\\Support\\Facades\\Schema::hasColumn('workspace_notification_preferences', 'crm_status_enabled')) continue;" . $e
         . '            $payload[$field] = $request->boolean($field);';
    if (substr_count($s, $old) !== 1) return [null, 'Notification Settings updateSettings baseline differs'];
    return [str_replace($old, $new, $s), null];
}
function a7532_patch_provider(string $s): array
{
    $new = '        \\App\\Models\\Installation::observe(\\App\\Observers\\CrmCrossModuleStatusNotificationObserver::class);';
    $other = '        \\App\\Models\\Delivery::observe(\\App\\Observers\\CrmCrossModuleStatusNotificationObserver::class);';
    if (substr_count($s, $new) === 1 && substr_count($s, $other) === 1) return [$s, null];
    if (str_contains($s, 'CrmCrossModuleStatusNotificationObserver')) return [null, 'Partial observer patch'];
    $old = '        \\App\\Models\\Installation::observe(\\App\\Observers\\CrmCrossModuleStatusAuditObserver::class);';
    if (substr_count($s, $old) !== 1) return [null, 'A7.5.3.1 status audit observer not found'];
    if (substr_count($s, '\\App\\Models\\Delivery::observe(') !== 1
        || substr_count($s, '\\App\\Models\\Installation::observe(') !== 1) {
        return [null, 'Additional local CRM status observers need manual review'];
    }
    $e = a7532_eol($s);
    return [str_replace($old, $old . $e . '        // A7.5.3.2: opt-in private CRM status notifications.' . $e . $other . $e . $new, $s), null];
}
function a7532_patch_invoice(string $s): array
{
    $start = strpos($s, 'public function updateStatus(');
    $end = ($start === false) ? false : strpos($s, 'public function destroy(', $start);
    if ($start === false || $end === false || $end <= $start) return [null, 'Invoice status method not found'];
    $method = substr($s, $start, $end - $start);
    $marker = 'CrmStatusNotificationPublisher::class';
    if (substr_count($method, $marker) === 1) return [$s, null];
    if (str_contains($method, $marker) || substr_count($method, "'invoice.status_changed'") !== 1
        || substr_count($method, 'return null;') !== 1 || substr_count($method, 'DB::transaction(') !== 1
        || !str_contains($method, 'CrmInvoiceRowGuard::class')) {
        return [null, 'Invoice A7.5.3.1 locked status method baseline not recognized'];
    }
    $needle = '            return null;';
    if (substr_count($method, $needle) !== 1 || strpos($method, $needle) < strpos($method, "'invoice.status_changed'")) {
        return [null, 'Invoice audit transaction location mismatch'];
    }
    $line = "            app(\\App\\Services\\CrmStatusNotificationPublisher::class)->changed('invoice', (string) \$existing->invoice_id, auth('web')->id() ? (string) auth('web')->id() : null);";
    $method = str_replace($needle, $line . a7532_eol($s) . $needle, $method);
    return [substr($s, 0, $start) . $method . substr($s, $end), null];
}
$edits = [
    'app/Services/WorkspaceNotificationPreferences.php' => 'a7532_patch_preferences',
    'app/Models/WorkspaceNotificationPreference.php' => 'a7532_patch_model',
    'resources/views/workspace-notifications/settings.blade.php' => 'a7532_patch_settings',
    'app/Services/WorkspaceNotificationCenter.php' => 'a7532_patch_center',
    'app/Providers/AppServiceProvider.php' => 'a7532_patch_provider',
    'app/Http/Controllers/InvoiceController.php' => 'a7532_patch_invoice',
    'app/Http/Controllers/WorkspaceNotificationController.php' => 'a7532_patch_settings_controller',
];
$additions = [
    'app/Services/CrmStatusNotificationAccess.php',
    'app/Services/CrmStatusNotificationPublisher.php',
    'app/Observers/CrmCrossModuleStatusNotificationObserver.php',
    'database/migrations/2026_11_18_190000_add_crm_status_notification_preference.php',
    'tests/Unit/CrmStatusNotificationSourceTest.php',
    'tests/Feature/CrmStatusNotificationPrivacyTest.php',
];
$changes = []; $errors = [];
foreach ($edits as $rel => $fn) {
    $path = "$root/$rel";
    $before = is_file($path) ? file_get_contents($path) : false;
    if ($before === false) { $errors[] = "Missing $rel"; continue; }
    [$after, $err] = $fn($before);
    if ($err !== null || $after === null) { $errors[] = "$rel: $err"; continue; }
    if ($after !== $before) $changes[$rel] = ['before' => $before, 'after' => $after];
}
foreach ($additions as $rel) {
    $target = "$root/$rel";
    $payload = "$root/tools/a7532_payload/$rel";
    $expected = is_file($payload) ? file_get_contents($payload) : false;
    if ($expected === false) { $errors[] = "Missing payload $rel"; continue; }
    if (is_file($target)) {
        if (file_get_contents($target) !== $expected) $errors[] = "Custom file exists: $rel";
    } else $changes[$rel] = ['before' => null, 'after' => $expected];
}
if ($errors) {
    foreach ($errors as $err) fwrite(STDERR, "[FAIL] $err\n");
    fwrite(STDERR, "No files or database records modified.\n");
    exit(1);
}
if (!$changes) { echo "[PASS] A7.5.3.2 already installed. No changes.\n"; exit(0); }
foreach ($changes as $rel => $d) echo '[' . ($dry ? 'CHECK' : 'INFO') . '] ' . ($d['before'] === null ? 'add' : 'patch') . ": $rel\n";
if ($dry) { echo "[PASS] Dry run. No files or database changes.\n"; exit(0); }
$backupRel = 'tools/a7532_backups/' . date('Ymd-His') . '-' . bin2hex(random_bytes(4));
$written = [];
try {
    foreach ($changes as $rel => $d) {
        if ($d['before'] === null) continue;
        $path = "$root/$backupRel/$rel";
        if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0775, true) && !is_dir(dirname($path))) throw new RuntimeException("Backup directory: $rel");
        if (file_put_contents($path, $d['before'], LOCK_EX) === false) throw new RuntimeException("Backup failure: $rel");
    }
    foreach ($changes as $rel => $d) {
        $target = "$root/$rel";
        if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0775, true) && !is_dir(dirname($target))) throw new RuntimeException("Destination directory: $rel");
        if ($d['before'] === null ? file_exists($target) : (!is_file($target) || file_get_contents($target) !== $d['before'])) throw new RuntimeException("Concurrent edit: $rel");
        $tmp = "$target.a7532.tmp";
        if (file_put_contents($tmp, $d['after'], LOCK_EX) === false || !rename($tmp, $target)) { @unlink($tmp); throw new RuntimeException("Write failure: $rel"); }
        $written[] = $rel;
        echo '[PASS] ' . ($d['before'] === null ? 'Added' : 'Patched') . ": $rel\n";
    }
} catch (Throwable $e) {
    foreach (array_reverse($written) as $rel) {
        $old = $changes[$rel]['before'];
        if ($old === null) @unlink("$root/$rel"); else file_put_contents("$root/$rel", $old, LOCK_EX);
    }
    fwrite(STDERR, '[FAIL] ' . $e->getMessage() . ". Written files rolled back.\n");
    exit(1);
}
echo "[PASS] A7.5.3.2 installed. Backup: $backupRel\n";
echo "[INFO] Run only the additive migration after preflight; notification preference defaults OFF.\n";
