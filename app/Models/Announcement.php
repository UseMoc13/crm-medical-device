<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
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

    public function scopePublished($query)
    {
        return $query
            ->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
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
