<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Create the sequence required by CustomerController::store().
     * Keep existing Customer Codes intact and start after the largest numeric code.
     */
    public function up(): void
    {
        $existing = DB::selectOne("SELECT to_regclass('public.customer_code_seq') AS sequence_name");

        if ($existing->sequence_name === null) {
            DB::statement('CREATE SEQUENCE public.customer_code_seq AS bigint START WITH 1 INCREMENT BY 1 MINVALUE 1');
        }

        $maxExisting = (int) DB::selectOne(<<<'SQL'
            SELECT COALESCE(
                MAX((substring(customer_code FROM '^CUST-([0-9]{1,15})$'))::bigint),
                0
            ) AS max_number
            FROM public.customers
            WHERE customer_code ~ '^CUST-[0-9]{1,15}$'
        SQL)->max_number;

        // Do not move an existing sequence backwards, if it is already present.
        $sequence = DB::selectOne('SELECT last_value, is_called FROM public.customer_code_seq');
        $wasCalled = in_array($sequence->is_called, [true, 1, '1', 't', 'true'], true);
        $currentAllocated = $wasCalled ? (int) $sequence->last_value : ((int) $sequence->last_value - 1);
        $lastUsed = max($maxExisting, $currentAllocated);

        if ($lastUsed > 0) {
            DB::select('SELECT setval(\'public.customer_code_seq\', ?, true)', [$lastUsed]);
        } else {
            DB::select('SELECT setval(\'public.customer_code_seq\', 1, false)');
        }
    }

    public function down(): void
    {
        // Intentionally preserve the sequence. Dropping it after Customer Codes have
        // been issued can cause duplicate customer_code values after a later migrate.
    }
};
