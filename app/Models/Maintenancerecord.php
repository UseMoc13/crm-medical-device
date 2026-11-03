<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    use HasUuids;

    protected $table = 'maintenance_records';

    protected $primaryKey = 'maintenance_record_id';

    public $incrementing = false;

    protected $keyType = 'string';

    // Tabel maintenance_records hanya memiliki created_at.
    public const UPDATED_AT = null;

    protected $fillable = [
        'maintenance_schedule_id',
        'user_id',
        'performed_at',
        'condition_before',
        'action_taken',
        'condition_after',
        'result',
        'notes',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(
            MaintenanceSchedule::class,
            'maintenance_schedule_id',
            'maintenance_schedule_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}