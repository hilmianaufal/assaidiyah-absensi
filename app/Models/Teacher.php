<?php

namespace App\Models;

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
        'address',
        'bio',
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

    public function honorPackage()
    {
        return $this->hasOne(TeacherHonorPackage::class);
    }

    public function institutions()
    {
        return $this->belongsToMany(Institution::class)
            ->withTimestamps();
    }
}
