<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DhuhaReport extends Model
{
    protected $fillable = [
        'teacher_id',
        'institution_id',
        'report_date',
        'status',
        'present_teacher_ids',
        'teacher_count',
        'imam_name',
        'note',
        'whatsapp_message',
    ];

    protected $casts = [
        'report_date' => 'date',
        'present_teacher_ids' => 'array',
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
