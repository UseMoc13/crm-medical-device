<?php
/**
 * A7.6.5.4: OFF by default; enabling changes existing CSV entitlements.
 * Never enable in production without Finance/Management authorization review.
 */
return [
    'csv_invoice_all_scope_pilot_enabled' => (bool) env('CRM_REPORT_CSV_INVOICE_ALL_SCOPE_ENABLED', false),
    // OFF until Finance/Management approve which named report details may be displayed.
    'report_customer_product_privacy_pilot_enabled' => (bool) env('CRM_REPORT_DETAIL_PRIVACY_PILOT_ENABLED', false),
    // OFF until management approves per-module aggregate visibility.
    'report_aggregate_privacy_pilot_enabled' => (bool) env('CRM_REPORT_AGGREGATE_PRIVACY_PILOT_ENABLED', false),
];
