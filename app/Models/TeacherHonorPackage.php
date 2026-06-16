<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherHonorPackage extends Model
{
    protected $fillable = [
        'teacher_id',
        'weekly_hours',
        'package_rate',
        'monthly_honor',
        'deduction_per_hour',
        'is_active',
        'institution_id',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
