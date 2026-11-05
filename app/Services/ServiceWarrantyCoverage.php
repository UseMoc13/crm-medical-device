<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ServiceWarrantyCoverage
{
    /** Resolve warranty information by serial number without mutating coverage records. */
    public function forSerials(array $serialIds): array
    {
        $serialIds = array_values(array_unique(array_filter($serialIds)));
        if (!$serialIds) return [];

        $today = today()->toDateString();
        $rows = DB::table('warranties')
            ->whereIn('serial_number_id', $serialIds)
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->get();
        $out = [];
        foreach ($rows->groupBy('serial_number_id') as $id => $warranties) {
            $coverage = $warranties->first(function ($w) use ($today) {
                return strtolower(trim((string) $w->status)) === 'active'
                    && $w->start_date <= $today && $w->end_date >= $today;
            });
            $w = $coverage ?? $warranties->first();
            $raw = strtolower(trim((string) $w->status));
            $state = $coverage ? 'covered'
                : (in_array($raw, ['void','claimed'], true) ? $raw
                : ($w->end_date < $today ? 'expired'
                : ($w->start_date > $today ? 'upcoming' : 'inactive')));
            $out[$id] = [
                'status' => $state,
                'label' => match ($state) {
                    'covered' => 'Under Warranty',
                    'expired' => 'Expired',
                    'void' => 'Void',
                    'claimed' => 'Claimed',
                    'upcoming' => 'Not Yet Active',
                    default => 'Not Active',
                },
                'warranty_id' => $w->warranty_id,
                'warranty_number' => $w->warranty_number,
                'customer_id' => $w->customer_id,
                'start_date' => $w->start_date,
                'end_date' => $w->end_date,
                'days_left' => $coverage ? max(0, Carbon::parse($w->end_date)->startOfDay()->diffInDays(today(), false) * -1) : null,
            ];
        }
        return $out;
    }

    public function forSerial(string $serialId): ?array
    {
        return $this->forSerials([$serialId])[$serialId] ?? null;
    }
}
