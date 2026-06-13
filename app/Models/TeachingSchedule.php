<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeachingSchedule extends Model
{
    protected $fillable = [
    'teacher_id',
    'subject_id',
    'class_name',
    'day',
    'start_time',
    'end_time',
    'hours_count',
];

public function teacher()
{
    return $this->belongsTo(Teacher::class);
}

public function subject()
{
    return $this->belongsTo(Subject::class);
}
}
