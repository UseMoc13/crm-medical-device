<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WorkspaceCalendarEvent extends Model
{
    use HasUuids;

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

    public function excludedDays()
    {
        return $this->hasMany(WorkspaceCalendarDayExclusion::class, 'calendar_event_id', 'calendar_event_id');
    }

    public function owner() { return $this->belongsTo(User::class, 'owner_user_id', 'user_id'); }
}
