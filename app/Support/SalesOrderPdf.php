<?php

namespace App\Support;

use App\Models\SalesOrder;

class SalesOrderPdf
{
    public static function make(SalesOrder $order): string
    {
        $pdf = new SalesPdfCanvas();
        $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $customer = $order->quotation?->opportunity?->customer;
        $start = function () use ($pdf, $order) {
            $pdf->page(); $pdf->header('Sales Order', (string) $order->sales_order_number);
            $pdf->text('ORDER INFORMATION', 35, 145, 9, true, '#15966F');
            $pdf->rule(35, 153, 560);
            $pdf->text('ORDER DATE', 35, 174, 8, true, '#8391A7');
            $pdf->text($order->order_date?->format('d M Y') ?? '-', 35, 192, 11, true);
            $pdf->text('QUOTATION', 224, 174, 8, true, '#8391A7');
            $pdf->text((string) ($order->quotation?->quotation_number ?? '-'), 224, 192, 10, true, '#17284F', 31);
            $pdf->text('STATUS', 452, 174, 8, true, '#8391A7');
            $pdf->text(ucfirst((string) $order->status), 452, 192, 10, true, '#15966F');
            $pdf->rect(35, 209, 525, 65, '#F4F7FB');
            $pdf->text('CUSTOMER', 48, 229, 8, true, '#8391A7');
            $pdf->text((string) ($order->quotation?->opportunity?->customer?->customer_name ?? '-'), 48, 248, 12, true, '#17284F', 70);
            $pdf->text('ORDER ITEMS', 35, 300, 9, true, '#15966F');
            $pdf->rect(35, 310, 525, 29, '#EAF0F7');
            foreach ([['PRODUCT / CODE', 46], ['QTY', 290], ['UNIT PRICE', 335], ['DISCOUNT', 418], ['SUBTOTAL', 496]] as [$label, $x]) {
                $pdf->text($label, $x, 329, 8, true, '#52637B');
            }
        };
        $start(); $y = 341;
        foreach ($order->items as $item) {
            if ($y > 706) { $pdf->footer('Sales Order | ' . $order->sales_order_number); $start(); $y = 341; }
            $pdf->rect(35, $y, 525, 46, '#FAFBFE');
            $pdf->text((string) ($item->product?->product_name ?? 'Product'), 46, $y + 18, 9, true, '#17284F', 42);
            $pdf->text((string) ($item->product?->product_code ?? '-'), 46, $y + 33, 8, false, '#8A99AD', 32);
            $pdf->text((string) $item->quantity, 290, $y + 26, 9);
            $pdf->text($money($item->unit_price), 335, $y + 26, 8, false, '#41506A', 17);
            $pdf->text($money($item->discount ?? 0), 418, $y + 26, 8, false, '#41506A', 16);
            $pdf->text($money($item->subtotal), 496, $y + 26, 8, true, '#17284F', 18);
            $y += 48;
        }
        if ($y > 620) { $pdf->footer('Sales Order | ' . $order->sales_order_number); $start(); $y = 341; }
        $discount = max(0, (float) $order->subtotal + (float) $order->tax - (float) $order->total_amount);
        $pdf->rule(35, $y + 14, 560);
        foreach ([['Subtotal', $money($order->subtotal)], ['Discount', $discount ? '- ' . $money($discount) : '-'], ['Tax', '+ ' . $money($order->tax)]] as $i => [$label, $value]) {
            $pdf->text($label, 379, $y + 42 + $i * 21, 10, false, '#8391A7');
            $pdf->text($value, 468, $y + 42 + $i * 21, 10, true, '#17284F', 19);
        }
        $pdf->rect(365, $y + 112, 195, 46, '#E8F7F0');
        $pdf->text('TOTAL', 377, $y + 140, 10, true, '#146E58');
        $pdf->text($money($order->total_amount), 442, $y + 140, 12, true, '#146E58', 24);
        $pdf->footer('Generated from CRM Medical Device | For internal transaction records');
        return $pdf->output();
    }
}
