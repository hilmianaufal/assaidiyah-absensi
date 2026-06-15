<?php

namespace App\Http\Controllers;

use App\Models\HonorPayment;
use Barryvdh\DomPDF\Facade\Pdf;

class HonorPaymentReceiptController extends Controller
{
    public function show(HonorPayment $payment)
    {
        $payment->load('monthlyHonor.teacher');

        $pdf = Pdf::loadView('pdf.honor-payment-receipt', [
            'payment' => $payment,
            'honor' => $payment->monthlyHonor,
            'teacher' => $payment->monthlyHonor->teacher,
        ])->setPaper('a5', 'portrait');

        return $pdf->download('bukti-pembayaran-honor.pdf');
    }
}
