<?php

namespace Tests\Unit;

use App\Services\CrmDeliveryCompletionGuard as Guard;
use PHPUnit\Framework\TestCase;

/** Read-only, database-independent tests for the stock-out matching invariant. */
final class CrmDeliveryCompletionGuardTest extends TestCase
{
    private function row(string $warehouse, string $product, int $quantity): array
    {
        return ['warehouse_id' => $warehouse, 'product_id' => $product, 'quantity' => $quantity];
    }

    public function test_matching_warehouse_and_product_with_aggregated_movements(): void
    {
        $allocations = [$this->row('w1', 'p1', 1), $this->row('w1', 'p1', 1), $this->row('w2', 'p2', 1)];
        $movements = [$this->row('w2', 'p2', 1), $this->row('w1', 'p1', 2)];
        self::assertTrue(Guard::quantitiesMatch($allocations, $movements));
    }

    public function test_missing_warehouse_never_masquerades_as_reconciled(): void
    {
        self::assertFalse(Guard::quantitiesMatch([$this->row('', 'p1', 1)], [$this->row('w1', 'p1', 1)]));
    }

    public function test_equal_total_in_wrong_warehouse_is_still_rejected(): void
    {
        self::assertFalse(Guard::quantitiesMatch([$this->row('w1', 'p1', 1)], [$this->row('w2', 'p1', 1)]));
    }

    public function test_missing_or_extra_stockout_is_rejected(): void
    {
        self::assertFalse(Guard::quantitiesMatch([$this->row('w1', 'p1', 1)], []));
        self::assertFalse(Guard::quantitiesMatch([$this->row('w1', 'p1', 1)], [$this->row('w1', 'p1', 1), $this->row('w2', 'p2', 1)]));
    }

    public function test_wrong_product_or_amount_is_rejected(): void
    {
        self::assertFalse(Guard::quantitiesMatch([$this->row('w1', 'p1', 1)], [$this->row('w1', 'p2', 1)]));
        self::assertFalse(Guard::quantitiesMatch([$this->row('w1', 'p1', 1)], [$this->row('w1', 'p1', 2)]));
    }

    public function test_nonpositive_or_incomplete_groups_are_rejected(): void
    {
        self::assertFalse(Guard::quantitiesMatch([$this->row('w1', 'p1', 0)], [$this->row('w1', 'p1', 1)]));
        self::assertFalse(Guard::quantitiesMatch([$this->row('w1', 'p1', 1)], [['warehouse_id' => 'w1', 'product_id' => 'p1']]));
        self::assertFalse(Guard::quantitiesMatch([], []));
    }
}
