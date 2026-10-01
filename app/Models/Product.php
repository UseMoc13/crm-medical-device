<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $primaryKey = 'product_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'category_id',
        'brand_id',
        'product_code',
        'product_name',
        'product_type',
        'specification',
        'unit',
        'price',
        'warranty_period',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'warranty_period' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function category()
    {
        return $this->belongsTo(
            ProductCategory::class,
            'category_id',
            'category_id'
        );
    }

    public function brand()
    {
        return $this->belongsTo(
            Brand::class,
            'brand_id',
            'brand_id'
        );
    }

    public function opportunityItems()
    {
        return $this->hasMany(
            OpportunityItem::class,
            'product_id',
            'product_id'
        );
    }
}