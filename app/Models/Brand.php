<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $table = 'brands';

    protected $primaryKey = 'brand_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'brand_name',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function products()
    {
        return $this->hasMany(
            Product::class,
            'brand_id',
            'brand_id'
        );
    }
}