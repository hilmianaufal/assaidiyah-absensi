<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    public function teachers()
{
    return $this->belongsToMany(Teacher::class)->withTimestamps();
}
}
