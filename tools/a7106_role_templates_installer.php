<?php
/**
 * A7.10.6: validated, reversible source patch for CRM Role Template editor.
 * Targets only RoleController, CrmRoleAccessCatalog and roles/_form. No DB access.
 * --check (dry run), --apply (backup then patch), --rollback=BACKUP_ID.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$targets = [
    'app/Services/CrmRoleAccessCatalog.php',
    'app/Http/Controllers/RoleController.php',
    'resources/views/roles/_form.blade.php',
];
$backupBase = $root . '/storage/app/private/a7106_role_template_backups';

function a7106_sub(string $text, string $old, string $new, string $label): string
{
    $count = substr_count($text, $old);
    if ($count !== 1) throw new RuntimeException("Unexpected source structure [$label]: matches=$count. No write attempted.");
    return str_replace($old, $new, $text);
}
function a7106_regex(string $text, string $re, string $new, string $label): string
{
    $result = preg_replace($re, $new, $text, -1, $count);
    if ($result === null || $count !== 1) throw new RuntimeException("Unexpected source structure [$label]: matches=$count. No write attempted.");
    return $result;
}
function a7106_patch(string $path, string $source): string
{
    if ($path === 'app/Services/CrmRoleAccessCatalog.php') {
        $start = strpos($source, '    public static function template(string $name): array');
        $end = strrpos($source, "\n}");
        if ($start === false || $end === false || $start >= $end || substr_count($source, 'public static function templateForRole(') !== 1) {
            throw new RuntimeException('Unsupported CrmRoleAccessCatalog version.');
        }
        // Replace only two legacy template helper methods (end of class).
        return substr($source, 0, $start)
            . "    /** A7.10.6: presets live in a single safe catalogue. */\n"
            . "    public static function template(string \$name): array\n"
            . "    {\n        return CrmRoleTemplateCatalog::pages(\$name);\n    }\n\n"
            . "    public static function templateForRole(string \$roleName): string\n"
            . "    {\n        return CrmRoleTemplateCatalog::forRoleName(\$roleName);\n    }\n"
            . substr($source, $end);
    }
    if ($path === 'app/Http/Controllers/RoleController.php') {
        $source = a7106_sub($source,
            "use App\\Services\\CrmWorkflowAccess as Workflow;",
            "use App\\Services\\CrmWorkflowAccess as Workflow;\nuse App\\Services\\CrmRoleTemplateCatalog as Template;",
            'controller imports');
        $source = a7106_regex($source,
            '~        \$workflowTemplates = \[\];\s*foreach \(\[\x27admin\x27.*?\n        \}~s',
            "        \$workflowTemplates = [];\n        foreach (Template::keys() as \$templateName) {\n            \$workflowTemplates[\$templateName] = Template::workflows(\$templateName);\n        }",
            'workflow templates');
        $source = a7106_regex($source,
            "~'accessTemplates'=>\\[[^\\n]*?\\],~",
            "'accessTemplates'=>Template::labels(),",
            'template dropdown labels');
        $source = a7106_sub($source,
            "'defaultTemplate'=>Catalog::templateForRole(\$role?->role_name ?? ''),",
            "'defaultTemplate'=>Template::forRoleName(\$role?->role_name ?? ''),",
            'template default');
        $source = a7106_sub($source,
            "'templateOptions'=>collect(['admin','sales','warehouse','delivery_staff','technician','finance','management'])->mapWithKeys(fn (\$key) => [\$key => Catalog::template(\$key)])->all()];",
            "'templateOptions'=>Template::allPages(), 'templateDescriptions'=>Template::descriptions()];",
            'template options map');
        $source = a7106_regex($source,
            "~'access_template' => \\['nullable', 'in:[^']+'\\],~",
            "'access_template' => ['nullable', \\Illuminate\\Validation\\Rule::in(Template::keys())],",
            'server allowlist validation');
        $source = a7106_sub($source,
            "return \$this->includeViewForActions(Catalog::template(\$input['access_template']));",
            "return \$this->includeViewForActions(Template::pages(\$input['access_template']));",
            'server page grants');
        $source = a7106_sub($source,
            "if (\$request->input('access_mode') === 'template') {\n            return Workflow::permittedByPages(\$pageAccess);\n        }",
            "if (\$request->input('access_mode') === 'template') {\n            // A7.10.6: never grant every sensitive workflow merely because Edit is enabled.\n            \$chosenTemplate = (string) \$request->input('access_template');\n            return array_values(array_intersect(Template::workflows(\$chosenTemplate), Workflow::permittedByPages(\$pageAccess)));\n        }",
            'restricted workflow grants');
        return $source;
    }
    if ($path === 'resources/views/roles/_form.blade.php') {
        $source = a7106_sub($source,
            '<p class="role-help">Template permissions are applied when you save. Existing Custom selections remain unchanged until you explicitly copy the template.</p>',
            '<p class="role-help" id="crmTemplateDescription" aria-live="polite">Select a current CRM position to see its access profile.</p>' . "\n"
            . '                            <p class="role-help">Presets replace this role\'s CRM page/workflow grants only when you save in Template mode. Existing data scopes are retained; new scopes are NOT automatically set to All. Copy to Custom to fine-tune CRUD, workflows and scopes.</p>',
            'template help');
        $source = a7106_sub($source,
            "  const templateInput = document.getElementById('access_template');",
            "  const templateInput = document.getElementById('access_template');\n  const templateDescriptions = @json(\$templateDescriptions ?? []);",
            'template descriptions binding');
        $source = a7106_sub($source,
            "  function updatePreview() {\n    syncAllPageRules();",
            "  function updatePreview() {\n    const help = document.getElementById('crmTemplateDescription');\n    if (help) help.textContent = templateDescriptions[templateInput.value] || 'Select a current CRM position to see its access profile.';\n    syncAllPageRules();",
            'safe dynamic description');
        return $source;
    }
    throw new RuntimeException('Unexpected target file.');
}

/** @return array{ok:bool,detail:string} */
function a7106_lint(string $php, string $ext = '.php'): array
{
    $tmp = tempnam(sys_get_temp_dir(), 'a7106_');
    if ($tmp === false) return ['ok'=>false,'detail'=>'Unable to allocate lint file'];
    file_put_contents($tmp, $php);
    try {
        $cmd = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($tmp) . ' 2>&1';
        exec($cmd, $output, $exitCode);
        return ['ok'=>$exitCode === 0,'detail'=>implode(' ', $output)];
    } finally {
        @unlink($tmp);
    }
}
function a7106_validate(string $root, array $patched): void
{
    foreach ($patched as $path => $source) {
        if (str_ends_with($path, '.blade.php')) {
            $autoload = $root . '/vendor/autoload.php';
            if (!is_file($autoload)) throw new RuntimeException('Composer vendor/autoload.php is required to compile Blade.');
            require_once $autoload;
            $compiler = new \Illuminate\View\Compilers\BladeCompiler(new \Illuminate\Filesystem\Filesystem(), sys_get_temp_dir());
            $source = $compiler->compileString($source);
        }
        $result = a7106_lint($source);
        if (!$result['ok']) throw new RuntimeException('Syntax validation failed in ' . $path . ': ' . $result['detail']);
        echo '[PASS] ' . $path . ' ('.(str_ends_with($path,'.blade.php')?'Blade compile + PHP lint':'PHP lint').')' . PHP_EOL;
    }
}
function a7106_restore(string $root, string $backupBase, string $id, array $targets): int
{
    if (!preg_match('/\A[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\z/D', $id)) {
        throw new RuntimeException('Invalid backup ID.');
    }
    $dir = $backupBase . '/' . $id;
    if (!is_file($dir . '/manifest.json')) throw new RuntimeException('Backup manifest not found.');
    $manifest = json_decode((string) file_get_contents($dir . '/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($manifest) || array_keys($manifest) !== $targets) throw new RuntimeException('Backup target list does not match installer.');
    $restore = [];
    foreach ($targets as $p) {
        $current = $root . '/' . $p;
        $saved = $dir . '/' . $p;
        if (!is_file($current) || !is_file($saved) || is_link($current)) throw new RuntimeException('Missing file for safe rollback: ' . $p);
        if (hash_file('sha256', $current) !== $manifest[$p]['patched_sha256']) throw new RuntimeException('Modified since apply; refusing overwrite: ' . $p);
        if (hash_file('sha256', $saved) !== $manifest[$p]['original_sha256']) throw new RuntimeException('Corrupt original backup: ' . $p);
        $restore[$p] = file_get_contents($saved);
    }
    foreach ($restore as $p => $contents) {
        if (file_put_contents($root.'/'.$p, $contents, LOCK_EX) !== strlen($contents)) throw new RuntimeException('Rollback incomplete; inspect backup: ' . $dir);
    }
    echo '[RESULT] A7106_ROLE_TEMPLATE_ROLLED_BACK; backup=' . $id . PHP_EOL;
    return 0;
}
try {
    $argvOptions = array_slice($argv, 1);
    if (count($argvOptions) !== 1) throw new RuntimeException('Usage: php tools/a7106_role_templates_installer.php --check | --apply | --rollback=BACKUP_ID');
    $operation = $argvOptions[0];
    if (str_starts_with($operation, '--rollback=')) {
        exit(a7106_restore($root, $backupBase, substr($operation, 11), $targets));
    }
    if (!in_array($operation, ['--check', '--apply'], true)) throw new RuntimeException('Unknown argument.');
    $catalog = $root . '/app/Services/CrmRoleTemplateCatalog.php';
    if (!is_file($catalog)) throw new RuntimeException('Unpack A7106 files into CRM project root before running.');
    require_once $catalog;
    if (count(\App\Services\CrmRoleTemplateCatalog::keys()) < 20) throw new RuntimeException('Incomplete template definitions.');

    $patched = [];
    $current = [];
    $already = 0;
    foreach ($targets as $path) {
        $full = $root . '/' . $path;
        if (!is_file($full) || is_link($full)) throw new RuntimeException('Required regular file missing: ' . $path);
        $current[$path] = file_get_contents($full);
        $marker = match ($path) {
            'app/Services/CrmRoleAccessCatalog.php' => 'A7.10.6: presets live in a single safe catalogue.',
            'app/Http/Controllers/RoleController.php' => 'A7.10.6: never grant every sensitive workflow',
            default => 'id="crmTemplateDescription"',
        };
        if (str_contains($current[$path], $marker)) {
            $already++;
            $patched[$path] = $current[$path];
        } else {
            $patched[$path] = a7106_patch($path, $current[$path]);
        }
    }
    if ($already !== 0 && $already !== count($targets)) {
        throw new RuntimeException('Partially installed patch detected. No files touched; review source/backup manually.');
    }
    a7106_validate($root, $patched);
    if ($already === count($targets)) {
        echo '[RESULT] A7106_ALREADY_INSTALLED; templates=' . count(\App\Services\CrmRoleTemplateCatalog::keys()) . '; no files modified' . PHP_EOL;
        exit(0);
    }
    if ($operation === '--check') {
        echo '[RESULT] A7106_ROLE_TEMPLATES_READY; templates=' . count(\App\Services\CrmRoleTemplateCatalog::keys()) . '; files_to_patch=' . count($targets) . '; no files modified' . PHP_EOL;
        exit(0);
    }
    $id = date('Ymd-His') . '-' . bin2hex(random_bytes(4));
    $dir = $backupBase . '/' . $id;
    if (!@mkdir($dir, 0700, true) && !is_dir($dir)) throw new RuntimeException('Cannot create private backup directory.');
    $manifest = [];
    foreach ($targets as $p) {
        $backupPath = $dir . '/' . $p;
        if (!@mkdir(dirname($backupPath), 0700, true) && !is_dir(dirname($backupPath))) throw new RuntimeException('Cannot create backup subdirectory.');
        if (file_put_contents($backupPath, $current[$p], LOCK_EX) !== strlen($current[$p])) throw new RuntimeException('Backup incomplete; refusing apply.');
        $manifest[$p] = ['original_sha256'=>hash('sha256', $current[$p]), 'patched_sha256'=>hash('sha256', $patched[$p])];
    }
    if (file_put_contents($dir.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR), LOCK_EX) === false) throw new RuntimeException('Could not persist rollback manifest.');
    foreach ($targets as $p) {
        if (hash_file('sha256', $root.'/'.$p) !== $manifest[$p]['original_sha256']) throw new RuntimeException('Source changed during preflight: '.$p);
    }
    $written = [];
    try {
        foreach ($targets as $p) {
            if (file_put_contents($root.'/'.$p, $patched[$p], LOCK_EX) !== strlen($patched[$p])) throw new RuntimeException('Write incomplete: '.$p);
            $written[] = $p;
        }
    } catch (Throwable $error) {
        foreach ($written as $p) @file_put_contents($root.'/'.$p, $current[$p], LOCK_EX);
        throw $error;
    }
    echo '[BACKUP] ' . $dir . PHP_EOL;
    echo '[RESULT] A7106_ROLE_TEMPLATES_INSTALLED; templates=' . count(\App\Services\CrmRoleTemplateCatalog::keys()) . '; files=3' . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, '[FAIL] ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
