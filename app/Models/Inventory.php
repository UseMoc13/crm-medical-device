<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventories';

    protected $primaryKey = 'inventory_id';

    public $incrementing = false;

    protected $keyType = 'string';

    // Tabel tidak memiliki created_at.
    // updated_at akan dikelola secara manual saat proses penyimpanan.
    public $timestamps = false;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'quantity',
        'minimum_stock',
        'updated_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'minimum_stock' => 'integer',
        'updated_at' => 'datetime',
    ];

    public function warehouse()
    {
        return $this->belongsTo(
            Warehouse::class,
            'warehouse_id',
            'warehouse_id'
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
