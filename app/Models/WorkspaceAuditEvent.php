<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/** Application-level append-only record. No update/delete routes are provided. */
final class WorkspaceAuditEvent extends Model
{
    use HasUuids;

    protected $table = 'workspace_audit_events';
    protected $primaryKey = 'audit_event_id';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';
    protected $fillable = ['actor_user_id', 'event_type', 'subject_type', 'subject_id', 'details'];

    protected function casts(): array
    {
        return ['details' => 'array', 'created_at' => 'datetime'];
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id', 'user_id');
    }
}
