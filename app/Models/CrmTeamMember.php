<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CrmTeamMember extends Model
{
    use HasUuids;

    protected $table = 'crm_team_members';
    protected $primaryKey = 'team_member_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['team_id', 'user_id', 'member_role', 'joined_at', 'left_at'];

    protected function casts(): array
    {
        return ['joined_at' => 'datetime', 'left_at' => 'datetime'];
    }

    public function team() { return $this->belongsTo(CrmTeam::class, 'team_id', 'team_id'); }
    public function user() { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
}
