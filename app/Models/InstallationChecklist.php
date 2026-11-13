<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class InstallationChecklist extends Model
{
    use HasUuids;

    protected $table = 'installation_checklists';

    protected $primaryKey = 'checklist_id';

    public $incrementing = false;

    protected $keyType = 'string';

    // installation_checklists deliberately has no created_at / updated_at.
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

    protected static function booted(): void
    {
        static::creating(static function (self $checklist): void {
            $actorId = Auth::user()?->getAuthIdentifier();
            if ($actorId !== null) {
                $checklist->setAttribute('created_by', (string) $actorId);
                $checklist->setAttribute('updated_by', (string) $actorId);

                // Normally a new checklist starts incomplete. If a legitimately
                // precompleted checklist is created, keep the completer accurate.
                if ((bool) $checklist->is_completed) {
                    $checklist->setAttribute('completed_by', (string) $actorId);
                }
            }
        });

        static::updating(static function (self $checklist): void {
            $actorId = Auth::user()?->getAuthIdentifier();
            if ($actorId !== null) {
                $checklist->setAttribute('updated_by', (string) $actorId);
            }

            if ($checklist->isDirty('is_completed')) {
                if ((bool) $checklist->is_completed) {
                    // Only attribute completion to the user who performed it.
                    if ($actorId !== null) {
                        $checklist->setAttribute('completed_by', (string) $actorId);
                    }
                } else {
                    // Reopened checklist is no longer completed.
                    $checklist->setAttribute('completed_by', null);
                }
            }
        });
    }

    public function installation(): BelongsTo
    {
        return $this->belongsTo(
            Installation::class,
            'installation_id',
            'installation_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by', 'user_id');
    }
}
