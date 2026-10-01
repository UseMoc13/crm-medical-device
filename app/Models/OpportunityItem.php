<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpportunityItem extends Model
{
    protected $table = 'opportunity_items';

    protected $primaryKey = 'opportunity_item_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'opportunity_id',
        'product_id',
        'quantity',
        'estimated_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'estimated_price' => 'decimal:2',
        ];
    }

    public function opportunity()
    {
        return $this->belongsTo(
            Opportunity::class,
            'opportunity_id',
            'opportunity_id'
        );
    }

    public function product()
    {
        return $this->belongsTo(
            Product::class,
            'product_id',
            'product_id'
        );
    }
}