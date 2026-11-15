<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmTeam extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'crm_teams';
    protected $primaryKey = 'team_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'team_code', 'team_name', 'team_type', 'status', 'branch_id',
        'leader_user_id', 'created_by_user_id', 'description', 'starts_on', 'ends_on',
    ];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'deleted_at' => 'datetime'];
    }

    public function branch() { return $this->belongsTo(Branch::class, 'branch_id', 'branch_id'); }
    public function leader() { return $this->belongsTo(User::class, 'leader_user_id', 'user_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by_user_id', 'user_id'); }
    public function memberships() { return $this->hasMany(CrmTeamMember::class, 'team_id', 'team_id'); }
    public function activeMemberships()
    {
        return $this->memberships()->whereNull('left_at');
    }
}
