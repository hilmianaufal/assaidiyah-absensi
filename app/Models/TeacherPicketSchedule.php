<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherPicketSchedule extends Model
{
    protected $fillable = [
        'teacher_id',
        'day',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}