<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementMedia extends Model
{
    protected $table = 'announcement_media';

    protected $primaryKey = 'media_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'announcement_id',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
        'media_type',
        'alt_text',
        'sort_order',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(
            Announcement::class,
            'announcement_id',
            'announcement_id'
        );
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'user_id');
    }
}
