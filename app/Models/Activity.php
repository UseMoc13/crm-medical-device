<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'activities';
    protected $primaryKey = 'activity_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'customer_id', 'lead_id', 'opportunity_id',
        'activity_type', 'subject', 'description', 'activity_date',
    ];

    protected function casts(): array
    {
        return ['activity_date' => 'datetime', 'created_at' => 'datetime'];
    }

    public function user() { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
    public function customer() { return $this->belongsTo(Customer::class, 'customer_id', 'customer_id'); }
    public function lead() { return $this->belongsTo(Lead::class, 'lead_id', 'lead_id'); }
    public function opportunity() { return $this->belongsTo(Opportunity::class, 'opportunity_id', 'opportunity_id'); }
}
