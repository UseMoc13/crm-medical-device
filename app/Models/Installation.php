<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Installation extends Model
{
    use HasUuids;

    protected $table = 'installations';

    protected $primaryKey = 'installation_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'installation_id',
        'delivery_id',
        'user_id',
        'installation_date',
        'location',
        'status',
        'notes',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke Delivery.
     */
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(
            Delivery::class,
            'delivery_id',
            'delivery_id'
        );
    }

    /**
     * Relasi ke User yang bertanggung jawab.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'user_id'
        );
    }
    /**
     * Relasi ke checklist instalasi.
     */
    public function checklists(): HasMany
    {
        return $this->hasMany(
            InstallationChecklist::class,
            'installation_id',
            'installation_id'
        );
    }
}