<?php
/** A7.10.5 regression fixtures. No CRM DB queries or writes. */
declare(strict_types=1);
require __DIR__.'/a7105_rebuild_role_ui.php';
$root=dirname(__DIR__);
if (!is_file($root.'/vendor/autoload.php')) {fwrite(STDERR,"[FAIL] vendor/autoload.php missing\n");exit(1);}
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$blade=app('blade.compiler');
$fixtures=[
    'role unless adjacency'=>[
        <<<'BLADE'
@unless($isProtected)<a href="{{ route('roles.edit', $role) }}">Edit</a>@endunless
BLADE,
        1,
    ],
    'branch else adjacency'=>[
        <<<'BLADE'
@forelse($branches as $branch)
<a href="{{ route('branches.show', $branch) }}">View</a>
@empty
<div>@if($filter)None @else<a href="{{ route('branches.create') }}">New</a>@endif</div>
@endforelse
BLADE,
        2,
    ],
    'side-by-side anchor links'=>[
        <<<'BLADE'
<div><a href="{{ route('organization.index') }}">Organization</a><a href="{{ route('teams.create') }}">New Team</a></div>
BLADE,
        2,
    ],
    'nested anchor and form'=>[
        <<<'BLADE'
<form method="POST" action="{{ route('deliveries.change-status', $delivery) }}"><button type="submit">Confirm</button><a href="{{ route('deliveries.index') }}">Back</a></form>
BLADE,
        2,
    ],
    'GET filter not guarded'=>[
        <<<'BLADE'
<form method="GET" action="{{ route('branches.index') }}"><button type="submit">Filter</button></form>
BLADE,
        0,
    ],
    'unmanaged workspace not rewritten'=>[
        <<<'BLADE'
<a href="{{ route('todos.create') }}">To-do</a>
BLADE,
        0,
    ],
];
$count=0;
foreach ($fixtures as $label=>[$source,$expected]) {
    try {
        $result=a7105_transform($source);
        if ($result['guards'] !== $expected) throw new RuntimeException('Unexpected guards: '.$result['guards'].' expected '.$expected);
        if (substr_count($result['source'],'{{-- A7105:') !== substr_count($result['source'],'{{-- /A7105 --}}')) throw new RuntimeException('Unbalanced markers');
        $error=a7105_php_lint($blade->compileString($result['source']));
        if ($error !== null) throw new RuntimeException($error);
        echo '[PASS] '.$label.PHP_EOL;$count++;
    } catch (Throwable $e) {fwrite(STDERR,'[FAIL] '.$label.': '.$e->getMessage().PHP_EOL);exit(1);}
}
echo '[RESULT] A7105_SMOKE_PASS cases='.$count.' (no DB queries or view writes)'.PHP_EOL;
