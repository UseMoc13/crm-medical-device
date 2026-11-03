<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceSchedule extends Model
{
    use HasUuids;

    protected $table = 'maintenance_schedules';

    protected $primaryKey = 'maintenance_schedule_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'serial_number_id',
        'user_id',
        'scheduled_date',
        'maintenance_type',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function serialNumber(): BelongsTo
    {
        return $this->belongsTo(
            SerialNumber::class,
            'serial_number_id',
            'serial_number_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(
            MaintenanceRecord::class,
            'maintenance_schedule_id',
            'maintenance_schedule_id'
        )->orderByDesc('performed_at');
    }

    /**
     * Jadwal dianggap terlambat jika masih Scheduled / In Progress
     * dan tanggalnya sudah lewat.
     */
    public function isOverdue(): bool
    {
        return in_array($this->status, ['Scheduled', 'In Progress'], true)
            && $this->scheduled_date !== null
            && $this->scheduled_date->lt(today());
    }
}