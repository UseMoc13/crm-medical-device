<?php

namespace App\Support;

use App\Models\SalesOrder;

class SalesOrderPdf
{
    public static function make(SalesOrder $order): string
    {
        $pages = [];
        $items = $order->items;
        $chunks = $items->chunk(15);
        if ($chunks->isEmpty()) {
            $chunks = collect([collect()]);
        }
        $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $customer = $order->quotation?->opportunity?->customer?->customer_name ?? '-';
        foreach ($chunks as $pageIndex => $chunk) {
            $lines = [
                ['CRM MEDICAL DEVICE', 18, true],
                ['SALES ORDER', 14, true],
                ['Order Number: ' . $order->sales_order_number, 11, true],
                ['Customer: ' . $customer, 10, false],
                ['Quotation: ' . ($order->quotation?->quotation_number ?? '-'), 10, false],
                ['Date: ' . ($order->order_date?->format('d M Y') ?? '-'), 10, false],
                ['Status: ' . ucfirst((string) $order->status), 10, false],
                [' ', 10, false],
                ['ORDER ITEMS (page ' . ($pageIndex + 1) . ')', 11, true],
                ['No | Product / Code | Qty | Unit Price | Discount | Subtotal', 9, true],
            ];
            foreach ($chunk as $index => $item) {
                $product = $item->product?->product_name ?? 'Unknown product';
                $line = ($pageIndex * 15 + $index + 1) . '. ' . $product . ' (' . ($item->product?->product_code ?? '-') . ')';
                $lines[] = [mb_strimwidth($line, 0, 91, '...'), 9, false];
                $lines[] = ['    Qty ' . $item->quantity . '  |  ' . $money($item->unit_price) . '  |  Disc ' . $money($item->discount) . '  |  ' . $money($item->subtotal), 9, false];
            }
            if ($pageIndex === $chunks->count() - 1) {
                $lines[] = [' ', 10, false];
                $lines[] = ['Subtotal: ' . $money($order->subtotal), 11, false];
                $effectiveDiscount = max(0, (float) $order->subtotal + (float) $order->tax - (float) $order->total_amount);
                if ($effectiveDiscount > 0.009) {
                    $lines[] = ['Discount: -' . $money($effectiveDiscount), 11, false];
                }
                $lines[] = ['Tax: ' . $money($order->tax), 11, false];
                $lines[] = ['TOTAL: ' . $money($order->total_amount), 13, true];
                $lines[] = ['Notes: ' . mb_strimwidth((string) ($order->notes ?? '-'), 0, 100, '...'), 9, false];
            }
            $pages[] = $lines;
        }
        return self::encode($pages);
    }

    private static function encode(array $pages): string
    {
        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';
        $kids = [];
        foreach ($pages as $index => $lines) {
            $pageId = 5 + $index * 2;
            $streamId = $pageId + 1;
            $kids[] = $pageId . ' 0 R';
            $commands = '';
            $y = 800;
            foreach ($lines as [$text, $fontSize, $bold]) {
                $converted = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
                $safe = str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\(', '\)', '', ' '], $converted === false ? '' : $converted);
                $commands .= sprintf('BT /F%d %d Tf 46 %d Td (%s) Tj ET', $bold ? 2 : 1, $fontSize, $y, $safe) . "\n";
                $y -= $fontSize >= 13 ? 23 : 15;
            }
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' . $streamId . ' 0 R >>';
            $objects[$streamId] = '<< /Length ' . strlen($commands) . " >>\nstream\n" . $commands . 'endstream';
        }
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($pages) . ' >>';
        ksort($objects);
        $output = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($output);
            $output .= $id . " 0 obj\n" . $body . "\nendobj\n";
        }
        $xref = strlen($output);
        $output .= "xref\n0 " . (max(array_keys($objects)) + 1) . "\n0000000000 65535 f \n";
        for ($id = 1; $id <= max(array_keys($objects)); $id++) {
            $output .= sprintf('%010d 00000 n ', $offsets[$id]) . "\n";
        }
        $output .= 'trailer << /Size ' . (max(array_keys($objects)) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";
        return $output;
    }
}
