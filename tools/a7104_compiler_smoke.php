<?php
/** A7.10.4 regression of the exact inline Blade comment + @endunless issue. */
declare(strict_types=1);
$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$blade = app('blade.compiler');

function checkSnippet(string $fragment, bool $valid): void
{
    global $blade;
    $compiled = $blade->compileString($fragment);
    $temp = tempnam(sys_get_temp_dir(), 'a7104_smoke_');
    if (!$temp) throw new RuntimeException('No temp file');
    try {
        file_put_contents($temp, $compiled);
        $output = []; $exit = 0;
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($temp).' 2>&1', $output, $exit);
        if (($exit === 0) !== $valid) {
            throw new RuntimeException('Unexpected compile result: '.implode(' ', $output));
        }
    } finally {
        @unlink($temp);
    }
}
$bad = "@unless(\$protected){{-- A7103:roles.edit --}}@if(true)\n".
       "<a href='#'>Edit</a>\n@endif{{-- /A7103 --}}@endunless";
$fixed = str_replace('{{-- /A7103 --}}@endunless', "{{-- /A7103 --}}\n@endunless", $bad);
checkSnippet($bad, false);
echo '[PASS] Reproduced broken Blade @endunless adjacency'.PHP_EOL;
checkSnippet($fixed, true);
echo '[PASS] Repaired Blade compiles to syntactically valid PHP'.PHP_EOL;
$inline = "@if(true){{-- A7103:products.edit --}}@if(true)<a href='#'>Edit</a>@endif{{-- /A7103 --}}@else<p>None</p>@endif";
$inlineFixed = str_replace('{{-- /A7103 --}}@else', "{{-- /A7103 --}}\n@else", $inline);
checkSnippet($inlineFixed, true);
echo '[PASS] Inline @else remains compilable after normalizing whitespace'.PHP_EOL;
echo '[RESULT] A7104_BLADE_COMPILER_SMOKE_PASS'.PHP_EOL;
