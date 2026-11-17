<?php
/**
 * CRM Medical Device A7.4.8 — UI installer v2 (Big-Changes)
 * Re-run safely after replacing this tool. Does NOT access or migrate the database.
 * Only edits five existing files. Makes backups before any modification.
 * Fix: the New Activity link is present twice (header + empty state); target header only.
 */
declare(strict_types=1);

$root = dirname(__DIR__);

$jobs = [
    'resources/views/activities/show.blade.php' => [
        'marker' => "@include('workspace-collaboration._panel', ['entityType'=>'activity', 'entity'=>\$activity])",
        'needle' => '@endsection',
        'replacement' => "@include('workspace-collaboration._panel', ['entityType'=>'activity', 'entity'=>\$activity])\n@endsection",
    ],
    'resources/views/todos/show.blade.php' => [
        'marker' => "@include('workspace-collaboration._panel', ['entityType'=>'todo', 'entity'=>\$todo])",
        'needle' => "@include('todos._style')",
        'replacement' => "@if(\$todo->visibility === 'work')\n@include('workspace-collaboration._panel', ['entityType'=>'todo', 'entity'=>\$todo])\n@endif\n@include('todos._style')",
    ],
    'resources/views/activities/index.blade.php' => [
        'marker' => "route('activities.shared')",
        'needle' => '<a href="{{ route(\'activities.create\') }}" class="btn primary">+ New Activity</a>',
        'replacement' => '<a href="{{ route(\'activities.shared\') }}" class="btn">Shared with Me</a>' . "\n        " . '<a href="{{ route(\'activities.create\') }}" class="btn primary">+ New Activity</a>',
        'header_only' => true,
    ],
    'resources/views/todos/index.blade.php' => [
        'marker' => "route('todos.shared')",
        'needle' => '<div class="actions"><a href="{{ route(\'todos.create\') }}" class="btn primary">+ New To-Do</a></div>',
        'replacement' => '<div class="actions"><a href="{{ route(\'todos.shared\') }}" class="btn">Shared with Me</a><a href="{{ route(\'todos.create\') }}" class="btn primary">+ New To-Do</a></div>',
    ],
    'app/Http/Controllers/TodoController.php' => [
        'marker' => 'A7.4.8: revoke explicit collaborators when task becomes personal',
        'needle' => "            \$record->update(\$data);\n",
        'replacement' => "            \$record->update(\$data);\n"
            ."            // A7.4.8: revoke explicit collaborators when task becomes personal.\n"
            ."            // Avoid restoring a previous grant if task is switched back to Work.\n"
            ."            if (\$record->visibility === 'personal') {\n"
            ."                DB::table('workspace_collaboration_members')->where('task_id', \$record->getKey())->delete();\n"
            ."            }\n",
    ],
];

// Read Git HEAD directly, without running Windows shell commands like "2>/dev/null".
// That redirection can print "The system cannot find the path specified" on Windows.
function detectGitBranch(string $root): ?string
{
    $gitDir = $root . DIRECTORY_SEPARATOR . '.git';
    if (is_file($gitDir)) {
        $gitFile = (string) file_get_contents($gitDir);
        if (preg_match('/^gitdir:\s*(.+)\s*$/m', $gitFile, $matches)) {
            $pointer = trim($matches[1]);
            $gitDir = (preg_match('~^(?:[A-Za-z]:[/\\\\]|/)~', $pointer))
                ? $pointer
                : $root . DIRECTORY_SEPARATOR . $pointer;
        }
    }
    $head = $gitDir . DIRECTORY_SEPARATOR . 'HEAD';
    if (!is_file($head)) return null;
    $raw = trim((string) file_get_contents($head));
    if (!str_starts_with($raw, 'ref: refs/heads/')) return null;
    return substr($raw, strlen('ref: refs/heads/'));
}

$branch = detectGitBranch($root);
if ($branch !== null && $branch !== 'Big-Changes') {
    fwrite(STDERR, "ERROR: current branch is {$branch}. Switch to Big-Changes before integration.\n");
    exit(1);
}
if ($branch === null) {
    fwrite(STDERR, "WARNING: Git branch could not be determined; confirm you are using Big-Changes.\n");
}

$planned = [];
foreach ($jobs as $relative => $job) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (!is_file($path)) {
        fwrite(STDERR, "ERROR: missing project file: {$relative}. No files changed.\n");
        exit(1);
    }
    $source = file_get_contents($path);
    if ($source === false) {
        fwrite(STDERR, "ERROR: unable to read: {$relative}. No files changed.\n");
        exit(1);
    }
    if (str_contains($source, $job['marker'])) {
        echo "Already integrated: {$relative}\n";
        continue;
    }

    // Activities page has TWO identically labeled New Activity links. Only edit
    // the header action — do NOT alter the empty-state button.
    if (!empty($job['header_only'])) {
        $headerStart = strpos($source, '<div class="page-head">');
        $headerEnd = $headerStart === false ? false : strpos($source, '<section', $headerStart);
        if ($headerStart === false || $headerEnd === false || $headerEnd <= $headerStart) {
            fwrite(STDERR, "ERROR: cannot locate Activity page heading in {$relative}. No files changed.\n");
            exit(1);
        }
        $header = substr($source, $headerStart, $headerEnd - $headerStart);
        $headerMatches = substr_count($header, $job['needle']);
        if ($headerMatches !== 1) {
            fwrite(STDERR, "ERROR: expected exactly one Activity header action, found {$headerMatches} in {$relative}. No files changed.\n");
            exit(1);
        }
        $offset = strpos($source, $job['needle'], $headerStart);
        if ($offset === false || $offset >= $headerEnd) {
            fwrite(STDERR, "ERROR: Activity header action moved. No files changed.\n");
            exit(1);
        }
        $after = substr_replace($source, $job['replacement'], $offset, strlen($job['needle']));
    } else {
        $count = substr_count($source, $job['needle']);
        if ($count !== 1) {
            fwrite(STDERR, "ERROR: unexpected file content: {$relative}; expected 1 anchor, found {$count}. No files changed.\n");
            exit(1);
        }
        $after = str_replace($job['needle'], $job['replacement'], $source);
    }
    $planned[$path] = [$relative, $source, $after];
}

if (!$planned) {
    echo "A7.4.8 UI integration already applied. No files changed.\n";
    exit(0);
}

$backup = $root . '/tools/a748_backups/' . date('Ymd-His') . '-' . getmypid();
if (!is_dir($backup) && !mkdir($backup, 0775, true) && !is_dir($backup)) {
    fwrite(STDERR, "ERROR: unable to create backup directory. No files changed.\n");
    exit(1);
}
$written = [];
try {
    // All backups finish before any modifications are made.
    foreach ($planned as [$relative, $before, $after]) {
        $save = $backup . '/' . $relative;
        if (!is_dir(dirname($save)) && !mkdir(dirname($save), 0775, true) && !is_dir(dirname($save))) {
            throw new RuntimeException('Unable to create backup directory for ' . $relative);
        }
        if (file_put_contents($save, $before, LOCK_EX) === false) {
            throw new RuntimeException('Unable to back up ' . $relative);
        }
    }
    foreach ($planned as $path => [$relative, $before, $after]) {
        if (file_put_contents($path, $after, LOCK_EX) === false) {
            throw new RuntimeException('Unable to update ' . $relative);
        }
        $written[] = [$path, $before];
        echo "Integrated: {$relative}\n";
    }
} catch (Throwable $e) {
    foreach ($written as [$path, $before]) {
        @file_put_contents($path, $before, LOCK_EX);
    }
    fwrite(STDERR, "ERROR: " . $e->getMessage() . ". Previously written files restored.\n");
    exit(1);
}
echo "Complete. Originals saved under " . str_replace($root . '/', '', $backup) . "\n";
