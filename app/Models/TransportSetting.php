<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportSetting extends Model
{
    protected $fillable = [
        'check_in_start',
        'check_in_end',
        'check_out_start',
        'check_out_end',
        'amount',
        'is_active',
    ];
}
