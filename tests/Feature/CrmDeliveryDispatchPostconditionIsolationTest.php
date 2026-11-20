<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Services\CrmDeliveryCompletionGuard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use PDO;
use Tests\TestCase;

/**
 * A7.5.5: isolated functional test of postcondition and transaction rollback.
 * Independent SQLite :memory: connection; never touches the configured CRM data.
 * The controller placement is verified by the paired source-contract test.
 */
final class CrmDeliveryDispatchPostconditionIsolationTest extends TestCase
{
    private function withIsolatedDb(\Closure $callback): void
    {
        self::assertTrue(app()->environment('testing'), 'APP_ENV=testing is required');
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('PDO SQLite unavailable; run in standard Laravel testing environment.');
        }

        $name = 'a755_dispatch_postcondition_memory';
        $previous = DB::getDefaultConnection();
        $oldConfig = config('database.connections.'.$name);
        config()->set('database.connections.'.$name, [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]);
        DB::purge($name);
        DB::setDefaultConnection($name);
        try {
            $schema = Schema::connection($name);
            $schema->create('delivery_items', static function (Blueprint $t): void {
                $t->uuid('delivery_item_id')->primary();
                $t->uuid('delivery_id');
                $t->uuid('serial_number_id');
                $t->uuid('warehouse_id')->nullable();
                $t->integer('quantity');
            });
            $schema->create('serial_numbers', static function (Blueprint $t): void {
                $t->uuid('serial_number_id')->primary();
                $t->uuid('product_id');
                $t->string('status');
            });
            $schema->create('sales_order_items', static function (Blueprint $t): void {
                $t->uuid('sales_order_item_id')->primary();
                $t->uuid('sales_order_id');
                $t->uuid('product_id');
                $t->integer('quantity');
            });
            $schema->create('stock_movements', static function (Blueprint $t): void {
                $t->uuid('stock_movement_id')->primary();
                $t->uuid('reference_id');
                $t->string('reference_type');
                $t->string('movement_type');
                $t->uuid('warehouse_id');
                $t->uuid('product_id');
                $t->integer('quantity');
            });
            self::assertSame(':memory:', (string) DB::connection()->getDatabaseName());
            $callback();
        } finally {
            DB::setDefaultConnection($previous);
            DB::disconnect($name);
            DB::purge($name);
            config()->set('database.connections.'.$name, $oldConfig);
        }
        self::assertSame($previous, DB::getDefaultConnection());
    }

    private function delivery(): Delivery
    {
        DB::table('serial_numbers')->insert([
            'serial_number_id' => 'serial-1', 'product_id' => 'product-1', 'status' => 'assigned',
        ]);
        DB::table('delivery_items')->insert([
            'delivery_item_id' => 'item-1', 'delivery_id' => 'delivery-1',
            'serial_number_id' => 'serial-1', 'warehouse_id' => 'warehouse-1', 'quantity' => 1,
        ]);
        DB::table('sales_order_items')->insert([
            'sales_order_item_id' => 'order-item-1', 'sales_order_id' => 'order-1',
            'product_id' => 'product-1', 'quantity' => 1,
        ]);
        $delivery = new Delivery();
        $delivery->setRawAttributes([
            'delivery_id' => 'delivery-1', 'sales_order_id' => 'order-1', 'status' => 'pending',
        ], true);
        return $delivery;
    }

    private function stockOut(int $quantity, string $warehouse = 'warehouse-1'): void
    {
        DB::table('stock_movements')->insert([
            'stock_movement_id' => 'movement-1', 'reference_type' => 'delivery',
            'reference_id' => 'delivery-1', 'movement_type' => 'stock_out',
            'warehouse_id' => $warehouse, 'product_id' => 'product-1', 'quantity' => $quantity,
        ]);
    }

    public function test_matching_dispatch_postcondition_is_accepted(): void
    {
        $this->withIsolatedDb(function (): void {
            $delivery = $this->delivery();
            $this->stockOut(1);
            app(CrmDeliveryCompletionGuard::class)->assertCanComplete($delivery);
            self::assertSame(1, DB::table('stock_movements')->count());
        });
    }

    public function test_stockout_mismatch_throws_and_transient_stockout_rolls_back(): void
    {
        $this->withIsolatedDb(function (): void {
            $delivery = $this->delivery();
            $rejected = false;
            DB::beginTransaction();
            try {
                $this->stockOut(2);
                app(CrmDeliveryCompletionGuard::class)->assertCanComplete($delivery);
            } catch (ValidationException) {
                $rejected = true;
            } finally {
                DB::rollBack();
            }
            self::assertTrue($rejected, 'Invalid postcondition must reject dispatch.');
            self::assertSame(0, DB::table('stock_movements')->count(), 'Stock-out must roll back.');
        });
    }

    public function test_null_warehouse_cannot_pass_postcondition(): void
    {
        $this->withIsolatedDb(function (): void {
            $delivery = $this->delivery();
            DB::table('delivery_items')->where('delivery_item_id', 'item-1')->update(['warehouse_id' => null]);
            $this->stockOut(1);
            $this->expectException(ValidationException::class);
            app(CrmDeliveryCompletionGuard::class)->assertCanComplete($delivery);
        });
    }

    public function test_correct_total_in_wrong_warehouse_still_fails(): void
    {
        $this->withIsolatedDb(function (): void {
            $delivery = $this->delivery();
            $this->stockOut(1, 'warehouse-2');
            $this->expectException(ValidationException::class);
            app(CrmDeliveryCompletionGuard::class)->assertCanComplete($delivery);
        });
    }
}
