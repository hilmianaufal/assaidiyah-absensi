<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\MonthlyHonor;
use Barryvdh\DomPDF\Facade\Pdf;

class InstitutionHonorReportPdfController extends Controller
{
    public function show(Institution $institution, int $month, int $year)
    {
        $honors = MonthlyHonor::with(['teacher', 'payments'])
            ->where('institution_id', $institution->id)
            ->where('month', $month)
            ->where('year', $year)
            ->orderBy('teacher_id')
            ->get();

        $pdf = Pdf::loadView('pdf.institution-honor-report', [
            'institution' => $institution,
            'honors' => $honors,
            'month' => $month,
            'year' => $year,
            'totalHonor' => $honors->sum('grand_total'),
            'totalPaid' => $honors->sum(fn ($honor) => $honor->payments->sum('amount')),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('rekap-honor-' . str($institution->code)->slug() . '-' . $month . '-' . $year . '.pdf');
    }
}
