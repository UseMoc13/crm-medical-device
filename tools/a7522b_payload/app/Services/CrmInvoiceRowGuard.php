<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Locks an Invoice row for a single business transaction.
 * This helper MUST run in a transaction; it does not mutate the record.
 */
final class CrmInvoiceRowGuard
{
    public function lockedStatus(string $invoiceId): string
    {
        if (DB::transactionLevel() < 1) {
            throw new \LogicException('Invoice status lock requires an active DB transaction.');
        }

        $row = DB::table('invoices')
            ->where('invoice_id', $invoiceId)
            ->lockForUpdate()
            ->first(['status']);

        abort_if($row === null, 404);

        // No case folding or legacy state conversion: unknown statuses fail
        // closed against the existing controller policy.
        return (string) $row->status;
    }
}
