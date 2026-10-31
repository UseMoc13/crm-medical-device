<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallationChecklist extends Model
{
    use HasUuids;

    protected $table = 'installation_checklists';

    protected $primaryKey = 'checklist_id';

    public $incrementing = false;

    protected $keyType = 'string';

    // Tabel tidak menggunakan created_at dan updated_at.
    public $timestamps = false;

    protected $fillable = [
        'checklist_id',
        'installation_id',
        'item_name',
        'is_completed',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function installation(): BelongsTo
    {
        return $this->belongsTo(
            Installation::class,
            'installation_id',
            'installation_id'
        );
    }
}