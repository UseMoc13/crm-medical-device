<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $table = 'warehouses';

    protected $primaryKey = 'warehouse_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'warehouse_code',
        'warehouse_name',
        'address',
        'status',
    ];

    public function inventories(): HasMany
    {
        return $this->hasMany(
            Inventory::class,
            'warehouse_id',
            'warehouse_id'
        );
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(
            StockMovement::class,
            'warehouse_id',
            'warehouse_id'
        );
    }
}
