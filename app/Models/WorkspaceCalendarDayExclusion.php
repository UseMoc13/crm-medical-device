<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WorkspaceCalendarDayExclusion extends Model
{
    use HasUuids;

    protected $table = 'workspace_calendar_day_exclusions';
    protected $primaryKey = 'exclusion_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['calendar_event_id', 'excluded_date'];
    protected function casts(): array { return ['excluded_date' => 'date:Y-m-d']; }
}
