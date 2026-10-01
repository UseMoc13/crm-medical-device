<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    protected $table = 'product_categories';

    protected $primaryKey = 'category_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'category_name',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(
            Product::class,
            'category_id',
            'category_id'
        );
    }
}
