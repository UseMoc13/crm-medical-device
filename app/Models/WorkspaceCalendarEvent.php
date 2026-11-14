<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkspaceCalendarEvent extends Model
{
    protected $table = 'workspace_calendar_events';
    protected $primaryKey = 'calendar_event_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'owner_user_id', 'title', 'description', 'visibility', 'starts_at',
        'ends_at', 'is_all_day', 'location',
    ];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_all_day' => 'boolean'];
    }

    public function owner() { return $this->belongsTo(User::class, 'owner_user_id', 'user_id'); }
}
