<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class WorkspaceNoteMention extends Model
{
    use HasUuids;
    protected $table = 'workspace_note_mentions';
    protected $primaryKey = 'mention_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['note_id', 'target_type', 'target_id'];
}
