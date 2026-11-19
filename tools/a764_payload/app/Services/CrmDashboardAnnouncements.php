<?php

namespace App\Services;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Collection;

/** Dashboard GET must be read-only; scheduled publication is a separate command. */
final class CrmDashboardAnnouncements
{
    public function latest(): Collection
    {
        return Announcement::query()
            ->with('creator')
            ->published()
            ->orderByDesc('is_pinned')
            ->orderByRaw('pin_order ASC NULLS LAST')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();
    }
}
