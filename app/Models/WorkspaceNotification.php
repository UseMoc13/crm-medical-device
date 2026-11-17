<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class WorkspaceNotification extends Model
{
    use HasUuids;

    protected $table = 'workspace_notifications';
    protected $primaryKey = 'notification_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'recipient_user_id', 'actor_user_id', 'kind', 'title', 'message',
        'target_type', 'target_id', 'dedupe_key', 'read_at',
    ];

    protected function casts(): array
    {
        return ['read_at' => 'datetime', 'created_at' => 'datetime'];
    }
}
