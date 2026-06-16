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
        'total_additional_honor',
        'total_absent_hours',
        'total_deduction',
        'grand_total',
        'payment_status',
        'paid_at',
        'institution_id',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function payments()
    {
        return $this->hasMany(HonorPayment::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
