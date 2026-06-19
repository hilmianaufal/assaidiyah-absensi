<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DhuhaSchedule extends Model
{
    protected $fillable = [
        'teacher_id',
        'institution_id',
        'day',
        'is_active',
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
