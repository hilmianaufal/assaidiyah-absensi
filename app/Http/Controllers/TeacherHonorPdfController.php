<?php

namespace App\Http\Controllers;

use App\Models\AdditionalHonor;
use App\Models\MonthlyHonor;
use Barryvdh\DomPDF\Facade\Pdf;

class TeacherHonorPdfController extends Controller
{
    public function download(int $month, int $year)
    {
        $teacher = auth()->user()->teacher;

        if (! $teacher) {
            abort(403, 'Akun ini belum terhubung dengan data guru.');
        }

        $honor = MonthlyHonor::where('teacher_id', $teacher->id)
            ->where('month', $month)
            ->where('year', $year)
            ->firstOrFail();

        $additionalHonors = AdditionalHonor::where('teacher_id', $teacher->id)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $pdf = Pdf::loadView('pdf.teacher-honor-slip', [
            'teacher' => $teacher,
            'honor' => $honor,
            'additionalHonors' => $additionalHonors,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('slip-honor-' . $teacher->name . '-' . $month . '-' . $year . '.pdf');
    }
}
