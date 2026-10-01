<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branches';

    protected $primaryKey = 'branch_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'branch_code',
        'branch_name',
        'address',
        'phone',
        'status',
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
        return $this->hasMany(
            User::class,
            'branch_id',
            'branch_id'
        );
    }
}