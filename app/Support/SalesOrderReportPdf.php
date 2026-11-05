<?php

namespace App\Support;

use Illuminate\Support\Collection;

class SalesOrderReportPdf
{
    public static function make(Collection $orders, array $filters = []): string
    {
        $pdf = new SalesPdfCanvas();
        $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $total = $orders->sum(fn ($order) => (float) $order->total_amount);
        $start = function () use ($pdf, $filters) {
            $pdf->page();
            $pdf->header('Sales Orders Report');
            $pdf->text('EXPORT DATE', 36, 146, 8, true, '#8290A8');
            $pdf->text(now()->format('d M Y, H:i'), 36, 164, 11, true);
            $pdf->text('SEARCH FILTER', 240, 146, 8, true, '#8290A8');
            $pdf->text((string) ($filters['search'] ?: 'All orders'), 240, 164, 10, false, '#17284F', 30);
            $pdf->text('STATUS', 449, 146, 8, true, '#8290A8');
            $pdf->text(ucfirst((string) ($filters['status'] ?: 'All')), 449, 164, 10, false, '#17284F', 18);
            $pdf->rect(35, 186, 525, 30, '#EAF0F7');
            foreach ([['ORDER NUMBER', 44], ['CUSTOMER', 169], ['DATE', 330], ['STATUS', 416], ['TOTAL (IDR)', 488]] as [$label, $x]) {
                $pdf->text($label, $x, 205, 8, true, '#52637B');
            }
        };
        $start();
        $y = 218;
        foreach ($orders as $order) {
            if ($y > 750) {
                $pdf->footer('Internal report | CRM Medical Device');
                $start(); $y = 218;
            }
            if ((int) (($y - 218) / 36) % 2 === 0) $pdf->rect(35, $y, 525, 36, '#F8FAFD');
            $pdf->text((string) $order->sales_order_number, 44, $y + 23, 9, true, '#17284F', 24);
            $pdf->text((string) ($order->quotation?->opportunity?->customer?->customer_name ?? '-'), 169, $y + 23, 9, false, '#41506A', 27);
            $pdf->text($order->order_date?->format('d M Y') ?? '-', 330, $y + 23, 9, false, '#41506A');
            $pdf->text(ucfirst((string) $order->status), 416, $y + 23, 9, false, '#41506A', 12);
            $pdf->text($money($order->total_amount), 488, $y + 23, 8, true, '#146E58', 22);
            $y += 36;
        }
        if ($y > 718) { $pdf->footer('Internal report | CRM Medical Device'); $start(); $y = 234; }
        $pdf->rule(35, $y + 13, 560);
        $pdf->text('TOTAL ORDERS', 40, $y + 37, 10, true, '#8290A8');
        $pdf->text((string) $orders->count(), 136, $y + 37, 13, true);
        $pdf->text('TOTAL VALUE', 318, $y + 37, 10, true, '#8290A8');
        $pdf->text($money($total), 420, $y + 37, 12, true, '#15966F');
        $pdf->footer('Internal report | CRM Medical Device | PDF export max. 2,000 rows');
        return $pdf->output();
    }
}
