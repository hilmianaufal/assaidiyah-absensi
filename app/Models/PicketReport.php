<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PicketReport extends Model
{
    protected $fillable = [
        'teacher_id',
        'report_date',
        'teacher_absences',
        'whatsapp_message',
        'sent_at',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function students()
    {
        return $this->hasMany(PicketReportStudent::class);
    }
}