<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyAttendance extends Model
{
protected $fillable = [
    'teacher_id',
    'attendance_date',
    'attendance_time',

    'check_in_time',
    'check_out_time',
    'check_in_status',
    'check_out_status',

    'status',
    'transport_amount',
    'note',
    'check_in_photo',
    'check_out_photo',
];

public function teacher()
{
    return $this->belongsTo(Teacher::class);
}
}
