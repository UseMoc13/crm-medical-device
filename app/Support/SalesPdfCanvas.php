<?php

namespace App\Support;

class SalesPdfCanvas
{
    private array $pages = [];
    private string $buffer = '';
    private int $page = 0;
    private const W = 595;
    private const H = 842;

    public function page(): void
    {
        if ($this->page > 0) $this->pages[] = $this->buffer;
        $this->buffer = '';
        ++$this->page;
    }

    public function pageNumber(): int { return $this->page; }

    private function point(float $y): float { return self::H - $y; }

    private function color(string $hex): string
    {
        $hex = ltrim($hex, '#');
        return implode(' ', array_map(fn ($v) => sprintf('%.3f', hexdec($v) / 255), str_split($hex, 2)));
    }

    public function rect(float $x, float $y, float $w, float $h, string $fill): void
    {
        $this->buffer .= $this->color($fill) . ' rg ' . sprintf('%.2f %.2f %.2f %.2f re f', $x, $this->point($y + $h), $w, $h) . "\n";
    }

    public function rule(float $x1, float $y, float $x2, string $color = '#E3E9F0'): void
    {
        $this->buffer .= $this->color($color) . ' RG 0.6 w ' . sprintf('%.2f %.2f m %.2f %.2f l S', $x1, $this->point($y), $x2, $this->point($y)) . "\n";
    }

    public function text(string $value, float $x, float $y, int $size = 10, bool $bold = false, string $color = '#17284F', int $maxChars = 0): void
    {
        if ($maxChars > 0 && strlen($value) > $maxChars) $value = substr($value, 0, max(0, $maxChars - 3)) . '...';
        $converted = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value);
        $safe = str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', ' ', ' '], $converted === false ? '' : $converted);
        $this->buffer .= $this->color($color) . ' rg ' . sprintf('BT /F%d %d Tf %.2f %.2f Td (%s) Tj ET', $bold ? 2 : 1, $size, $x, $this->point($y), $safe) . "\n";
    }

    public function header(string $type, string $number = ''): void
    {
        $this->rect(0, 0, self::W, 115, '#17284F');
        $this->rect(35, 24, 34, 34, '#15966F');
        $this->text('R', 47, 46, 19, true, '#FFFFFF');
        $this->text('PT RADJA USADA MANDIRI', 82, 35, 12, true, '#FFFFFF');
        $this->text('CRM MEDICAL DEVICE  /  BUSINESS DOCUMENT', 82, 52, 8, false, '#C4D1E6');
        $this->text(strtoupper($type), 36, 93, 18, true, '#FFFFFF');
        if ($number !== '') $this->text($number, 378, 93, 10, true, '#CCF5E6', 33);
    }

    public function footer(string $caption): void
    {
        $this->rule(35, 792, 560);
        $this->text($caption, 36, 810, 8, false, '#8391A7', 92);
        $this->text('Page ' . $this->page, 522, 810, 8, false, '#8391A7');
    }

    public function output(): string
    {
        if ($this->page === 0) $this->page();
        $this->pages[] = $this->buffer;
        $objects = [1 => '<< /Type /Catalog /Pages 2 0 R >>', 3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>', 4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>'];
        $kids = [];
        foreach ($this->pages as $i => $stream) {
            $pid = 5 + 2 * $i; $sid = $pid + 1;
            $kids[] = $pid . ' 0 R';
            $objects[$pid] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' . $sid . ' 0 R >>';
            $objects[$sid] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";
        }
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($this->pages) . ' >>';
        ksort($objects);
        $pdf = "%PDF-1.4\n"; $offsets = [0];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "$id 0 obj\n$body\nendobj\n";
        }
        $xref = strlen($pdf); $max = max(array_keys($objects));
        $pdf .= "xref\n0 " . ($max + 1) . "\n0000000000 65535 f \n";
        for ($i = 1; $i <= $max; ++$i) $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . "\n";
        return $pdf . 'trailer << /Size ' . ($max + 1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
    }
}
