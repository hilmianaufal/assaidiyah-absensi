<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectAttendance extends Model
{
protected $fillable = [
    'teacher_id',
    'subject_id',
    'teaching_schedule_id',

    'recorded_by_teacher_id',
    'source',
    'attendance_status',
    'recorded_at',

    'teaching_date',
    'start_time',
    'end_time',
    'hours_count',
    'hourly_rate',
    'teaching_honor',
    'class_name',
    'status',
    'note',
];

public function teacher()
{
    return $this->belongsTo(Teacher::class);
}

public function subject()
{
    return $this->belongsTo(Subject::class);
}

public function recordedByTeacher()
{
    return $this->belongsTo(Teacher::class, 'recorded_by_teacher_id');
}

}
