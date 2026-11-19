<?php

namespace App\Services;

use App\Models\User;

/**
 * A7.6.5.7: opt-in data-minimization for Reports' named customer/product details.
 * OFF by default. Aggregate-only Report access is a separate business policy decision.
 * No business rows are read by this service.
 */
final class CrmReportPrivacyPilot
{
    public function __construct(private readonly CrmInvoiceCsvPolicy $invoiceCsv) {}

    /** @return array{detail_pilot:bool,csv_pilot:bool,customer_product_details:bool,invoice_csv:bool} */
    public function forViewer(?User $viewer): array
    {
        $detail = (bool) config('crm_reporting.report_customer_product_privacy_pilot_enabled', false);
        $csv = (bool) config('crm_reporting.csv_invoice_all_scope_pilot_enabled', false);
        // Do not query permissions when both pilots are OFF: preserve existing route behavior.
        $invoiceAll = ($detail || $csv) && $this->invoiceCsv->canExport($viewer);
        return self::decide($detail, $csv, $invoiceAll);
    }

    /** @return array{detail_pilot:bool,csv_pilot:bool,customer_product_details:bool,invoice_csv:bool} */
    public static function decide(bool $detailPilot, bool $csvPilot, bool $invoiceAll): array
    {
        return [
            'detail_pilot' => $detailPilot,
            'csv_pilot' => $csvPilot,
            'customer_product_details' => !$detailPilot || $invoiceAll,
            // ON detail pilot also closes direct CSV URLs even if the older CSV flag is OFF.
            'invoice_csv' => !($detailPilot || $csvPilot) || $invoiceAll,
        ];
    }
}
