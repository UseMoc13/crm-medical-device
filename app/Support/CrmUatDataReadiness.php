<?php
declare(strict_types=1);

namespace App\Support;

/**
 * A7.8.2: static, read-only UAT sample-data probes; never returns business rows.
 * All SQL statements are fixed source constants and only return boolean EXISTS.
 */
final class CrmUatDataReadiness
{
    /** @return array<string,array{tables:list<string>,sql:string}> */
    public static function chains(): array
    {
        return [
            'customer_opportunity' => [
                'tables' => ['customers','opportunities'],
                'sql' => 'SELECT EXISTS (SELECT 1 FROM public.opportunities o JOIN public.customers c ON c.customer_id=o.customer_id LIMIT 1) AS found',
            ],
            'customer_quotation' => [
                'tables' => ['customers','opportunities','quotations'],
                'sql' => 'SELECT EXISTS (SELECT 1 FROM public.quotations q JOIN public.opportunities o ON o.opportunity_id=q.opportunity_id JOIN public.customers c ON c.customer_id=o.customer_id LIMIT 1) AS found',
            ],
            'customer_sales_order' => [
                'tables' => ['customers','opportunities','quotations','sales_orders'],
                'sql' => 'SELECT EXISTS (SELECT 1 FROM public.sales_orders so JOIN public.quotations q ON q.quotation_id=so.quotation_id JOIN public.opportunities o ON o.opportunity_id=q.opportunity_id JOIN public.customers c ON c.customer_id=o.customer_id LIMIT 1) AS found',
            ],
            'delivery_sales_chain' => [
                'tables' => ['customers','opportunities','quotations','sales_orders','deliveries'],
                'sql' => 'SELECT EXISTS (SELECT 1 FROM public.deliveries d JOIN public.sales_orders so ON so.sales_order_id=d.sales_order_id JOIN public.quotations q ON q.quotation_id=so.quotation_id JOIN public.opportunities o ON o.opportunity_id=q.opportunity_id JOIN public.customers c ON c.customer_id=o.customer_id LIMIT 1) AS found',
            ],
            'installation_delivery_chain' => [
                'tables' => ['customers','opportunities','quotations','sales_orders','deliveries','installations'],
                'sql' => 'SELECT EXISTS (SELECT 1 FROM public.installations i JOIN public.deliveries d ON d.delivery_id=i.delivery_id JOIN public.sales_orders so ON so.sales_order_id=d.sales_order_id JOIN public.quotations q ON q.quotation_id=so.quotation_id JOIN public.opportunities o ON o.opportunity_id=q.opportunity_id JOIN public.customers c ON c.customer_id=o.customer_id LIMIT 1) AS found',
            ],
            'invoice_sales_chain' => [
                'tables' => ['customers','opportunities','quotations','sales_orders','invoices'],
                'sql' => 'SELECT EXISTS (SELECT 1 FROM public.invoices i JOIN public.sales_orders so ON so.sales_order_id=i.sales_order_id JOIN public.quotations q ON q.quotation_id=so.quotation_id JOIN public.opportunities o ON o.opportunity_id=q.opportunity_id JOIN public.customers c ON c.customer_id=o.customer_id LIMIT 1) AS found',
            ],
            'warranty_serial_chain' => [
                'tables' => ['warranties','serial_numbers'],
                'sql' => 'SELECT EXISTS (SELECT 1 FROM public.warranties w JOIN public.serial_numbers sn ON sn.serial_number_id=w.serial_number_id LIMIT 1) AS found',
            ],
        ];
    }

    /** @return array{decision:string,table_filled:int,table_empty:int,table_missing:int,chain_ready:int,chain_gap:int,chain_blocked:int} */
    public static function summarize(array $tables, array $chains): array
    {
        $tableStatuses = ['HAS_SAMPLE'=>0, 'EMPTY'=>0, 'MISSING'=>0];
        $chainStatuses = ['LINKED_SAMPLE'=>0, 'NO_LINKED_SAMPLE'=>0, 'BLOCKED_MISSING_TABLE'=>0];
        foreach ($tables as $status) {
            if (!is_string($status) || !array_key_exists($status, $tableStatuses)) {
                throw new \InvalidArgumentException('Unknown table status.');
            }
            $tableStatuses[$status]++;
        }
        foreach ($chains as $status) {
            if (!is_string($status) || !array_key_exists($status, $chainStatuses)) {
                throw new \InvalidArgumentException('Unknown relation-chain status.');
            }
            $chainStatuses[$status]++;
        }
        if ($tables === [] || $chains === []) throw new \InvalidArgumentException('Empty UAT manifest.');
        if ($tableStatuses['MISSING'] > 0 || $chainStatuses['BLOCKED_MISSING_TABLE'] > 0) {
            $decision = 'HOLD_SCHEMA_GAPS';
        } elseif ($tableStatuses['HAS_SAMPLE'] === 0) {
            $decision = 'SYNTHETIC_DATA_REQUIRED';
        } elseif ($tableStatuses['EMPTY'] > 0 || $chainStatuses['NO_LINKED_SAMPLE'] > 0) {
            $decision = 'PARTIAL_SAMPLE_DATA_REVIEW';
        } else {
            $decision = 'DATA_COVERAGE_CANDIDATE_NOT_UAT_PASS';
        }
        return [
            'decision' => $decision,
            'table_filled' => $tableStatuses['HAS_SAMPLE'],
            'table_empty' => $tableStatuses['EMPTY'],
            'table_missing' => $tableStatuses['MISSING'],
            'chain_ready' => $chainStatuses['LINKED_SAMPLE'],
            'chain_gap' => $chainStatuses['NO_LINKED_SAMPLE'],
            'chain_blocked' => $chainStatuses['BLOCKED_MISSING_TABLE'],
        ];
    }
}
