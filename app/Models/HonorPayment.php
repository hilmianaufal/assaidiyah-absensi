<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HonorPayment extends Model
{
    protected $fillable = [
        'monthly_honor_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'note',
    ];

    public function monthlyHonor()
    {
        return $this->belongsTo(MonthlyHonor::class);
    }
}
