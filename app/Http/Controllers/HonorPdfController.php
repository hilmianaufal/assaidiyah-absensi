<?php

namespace App\Http\Controllers;

use App\Models\MonthlyHonor;
use Barryvdh\DomPDF\Facade\Pdf;

class HonorPdfController extends Controller
{
    public function show(MonthlyHonor $honor)
    {
        $honor->load('teacher');

        $pdf = Pdf::loadView('pdf.honor-slip', [
            'honor' => $honor,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('slip-honor-' . $honor->teacher->name . '.pdf');
    }
}