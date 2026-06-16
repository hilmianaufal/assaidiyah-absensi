<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionalHonor extends Model
{
    protected $fillable = [
        'teacher_id',
        'title',
        'month',
        'year',
        'amount',
        'note',
        'institution_id',
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
