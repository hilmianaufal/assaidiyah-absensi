<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyHonor extends Model
{
    protected $fillable = [
    'teacher_id',
    'month',
    'year',
    'total_teaching_hours',
    'total_teaching_honor',
    'total_transport',
    'grand_total',
    'payment_status',
    'paid_at',
    'total_additional_honor',
];

public function teacher()
{
    return $this->belongsTo(Teacher::class);
}
}
