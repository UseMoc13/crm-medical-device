<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A1 bridge table: user_roles. Inserts go through UserRoleAssignmentService. */
class UserRole extends Model
{
    protected $table = 'user_roles';
    protected $primaryKey = 'user_role_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }
}
