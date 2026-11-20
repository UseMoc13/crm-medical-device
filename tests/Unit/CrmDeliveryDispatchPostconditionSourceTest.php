<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/** A7.5.5 source contract: postcondition must remain inside the dispatch transaction. */
final class CrmDeliveryDispatchPostconditionSourceTest extends TestCase
{
    public function test_postcondition_is_executed_after_dispatch_before_in_transit_commit(): void
    {
        $path = dirname(__DIR__, 2).'/app/Http/Controllers/DeliveryController.php';
        self::assertFileExists($path);
        $source = str_replace("\r\n", "\n", (string) file_get_contents($path));
        $start = strpos($source, 'public function changeStatus(');
        $end = strpos($source, 'private function dispatchAllocatedUnits(', $start === false ? 0 : $start);
        self::assertNotFalse($start);
        self::assertNotFalse($end);
        $method = substr($source, $start, $end - $start);

        self::assertStringContainsString('DB::transaction(function () use ($delivery, $validated)', $method);
        $dispatch = <<<'CODE'
            if ($validated['status'] === 'in_transit') {
                $this->dispatchAllocatedUnits($locked);
                // A7.5.5: fail closed before committing any stock-out or in_transit state.
                // Throws ValidationException => entire DB::transaction rolls back.
                app(\App\Services\CrmDeliveryCompletionGuard::class)->assertCanComplete($locked);
            }
CODE;
        self::assertStringContainsString($dispatch, $method);
        self::assertSame(2, substr_count($method, '->assertCanComplete($locked);'), 'Both dispatch and completion must be guarded.');
        self::assertLessThan(strpos($method, '$locked->save();'), strpos($method, '$this->dispatchAllocatedUnits($locked);'));
        self::assertLessThan(strpos($method, '$locked->save();'), strpos($method, '// A7.5.5: fail closed'));
        self::assertStringContainsString("if (\$validated['status'] === 'delivered')", $method);
    }

    public function test_unchanged_completion_service_rejects_mismatch_and_missing_warehouse(): void
    {
        $path = dirname(__DIR__, 2).'/app/Services/CrmDeliveryCompletionGuard.php';
        self::assertFileExists($path);
        $source = (string) file_get_contents($path);
        self::assertStringContainsString('public function assertCanComplete(Delivery $delivery)', $source);
        self::assertStringContainsString('quantitiesMatch($allocations, $movements)', $source);
        self::assertStringContainsString("$".'this->reject(\'Dispatch stock-out does not match', $source);
    }
}
