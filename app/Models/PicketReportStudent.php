<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PicketReportStudent extends Model
{
    protected $fillable = [
        'picket_report_id',
        'class_name',
        'student_name',
        'status',
        'note',
    ];

    public function report()
    {
        return $this->belongsTo(PicketReport::class, 'picket_report_id');
    }
}