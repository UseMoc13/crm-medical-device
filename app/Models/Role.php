<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $primaryKey = 'role_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'role_name',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }

    public function rolePermissions()
    {
        return $this->hasMany(
            RolePermission::class,
            'role_id',
            'role_id'
        );
    }

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions',
            'role_id',
            'permission_id',
            'role_id',
            'permission_id'
        );
    }
}