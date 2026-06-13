<?php

namespace App\Models;

use App\Models\TeacherPicketSchedule;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
    'name',
    'nip',
    'phone',
    'photo',
    'face_descriptor',
    'hourly_rate',
    'is_active',
    'is_picket_officer',
];

protected $casts = [
    'face_descriptor' => 'array',
    'is_active' => 'boolean',
    'is_picket_officer' => 'boolean',
];

public function picketSchedules()
{
    return $this->hasMany(TeacherPicketSchedule::class);
}
public function user()
{
    return $this->hasOne(User::class);
}


}
