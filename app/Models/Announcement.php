<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Announcement extends Model
{
    use HasUuids;

    protected $table = 'announcements';

    protected $primaryKey = 'announcement_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'slug',
        'category',
        'summary',
        'content',
        'thumbnail_path',
        'thumbnail_fit',
        'thumbnail_position',
        'thumbnail_background_style',
        'thumbnail_background',
        'thumbnail_gradient_start',
        'thumbnail_gradient_end',
        'status',
        'is_pinned',
        'pin_order',
        'scheduled_at',
        'published_at',
        'expires_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'pin_order' => 'integer',
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function uniqueIds(): array
    {
        return ['announcement_id'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(
            AnnouncementAttachment::class,
            'announcement_id',
            'announcement_id'
        )->orderBy('sort_order');
    }

    public function media(): HasMany
    {
        return $this->hasMany(
            AnnouncementMedia::class,
            'announcement_id',
            'announcement_id'
        )->orderBy('sort_order');
    }

    /**
     * Move every scheduled announcement whose time has arrived to "published".
     *
     * Called lazily before announcements are listed, so scheduled posts go live
     * on time without depending on a cron job. The publication time becomes
     * the time that was scheduled.
     */
    public static function publishDueScheduled(): int
    {
        return static::query()
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->update([
                'status' => 'published',
                'published_at' => DB::raw('scheduled_at'),
                'scheduled_at' => null,
            ]);
    }

    public function scopePublished($query)
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopePinned($query)
    {
        return $query
            ->where('is_pinned', true)
            ->orderByRaw('pin_order ASC NULLS LAST')
            ->orderByDesc('published_at');
    }
}