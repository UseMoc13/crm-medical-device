<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserReportingLine extends Model
{
    use HasUuids;

    protected $table = 'user_reporting_lines';
    protected $primaryKey = 'reporting_line_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['user_id', 'manager_user_id', 'updated_by_user_id'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_user_id', 'user_id');
    }
}
