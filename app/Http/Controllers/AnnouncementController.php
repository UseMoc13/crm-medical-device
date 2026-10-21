<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $now = now();

        $query = Announcement::query()
            ->with('creator')
            ->withCount(['attachments', 'media'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $now)
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $now);
            });

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function (Builder $query) use ($search) {
                $query->where('title', 'ilike', "%{$search}%")
                    ->orWhere('summary', 'ilike', "%{$search}%")
                    ->orWhere('category', 'ilike', "%{$search}%");
            });
        }

        $announcements = $query
            ->orderByDesc('is_pinned')
            ->orderByRaw('pin_order ASC NULLS LAST')
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('announcements.index', compact('announcements'));
    }

    public function show(Announcement $announcement): View
    {
        $now = now();

        $isVisible = Announcement::query()
            ->where('announcement_id', $announcement->announcement_id)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $now)
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $now);
            })
            ->exists();

        abort_unless($isVisible, 404);

        $announcement->load([
            'creator',
            'attachments.uploader',
            'media.uploader',
        ]);

        return view('announcements.show', compact('announcement'));
    }
}