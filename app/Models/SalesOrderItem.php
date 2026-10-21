<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SalesOrderItem extends Model
{
    protected $table = 'sales_order_items';
    protected $primaryKey = 'sales_order_item_id';

    public $incrementing = false;
    protected $keyType = 'string';

    // Tabel sales_order_items tidak memiliki created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'sales_order_item_id',
        'sales_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount',
        'subtotal',
    ];

    protected static function booted(): void
    {
        static::creating(function ($item) {
            if (empty($item->sales_order_item_id)) {
                $item->sales_order_item_id = (string) Str::uuid();
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(
            SalesOrder::class,
            'sales_order_id',
            'sales_order_id'
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
