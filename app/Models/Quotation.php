<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $table = 'quotations';

    protected $primaryKey = 'quotation_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'opportunity_id',
        'quotation_number',
        'quotation_date',
        'valid_until',
        'subtotal',
        'discount',
        'discount_type',
        'tax',
        'tax_type',
        'total_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quotation_date' => 'date',
            'valid_until' => 'date',

            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total_amount' => 'decimal:2',

            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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

    public function items()
    {
        return $this->hasMany(
            QuotationItem::class,
            'quotation_id',
            'quotation_id'
        );
    }
}