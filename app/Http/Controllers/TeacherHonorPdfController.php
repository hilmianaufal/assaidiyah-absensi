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
    public function downloadByHonor(MonthlyHonor $honor)
{
    $teacher = auth()->user()->teacher;

    if (! $teacher) {
        abort(403, 'Akun ini belum terhubung dengan data guru.');
    }

    if ($honor->teacher_id !== $teacher->id && auth()->user()->role !== 'admin') {
        abort(403);
    }

    $honor->load(['teacher', 'institution']);

    $additionalHonors = \App\Models\AdditionalHonor::where('teacher_id', $honor->teacher_id)
        ->where('institution_id', $honor->institution_id)
        ->where('month', $honor->month)
        ->where('year', $honor->year)
        ->get();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.teacher-honor-slip', [
        'teacher' => $honor->teacher,
        'honor' => $honor,
        'additionalHonors' => $additionalHonors,
    ])->setPaper('a4', 'portrait');

    return $pdf->download(
        'slip-honor-' .
        str($honor->teacher->name)->slug('-') . '-' .
        str($honor->institution?->name ?? 'lembaga')->slug('-') . '-' .
        $honor->month . '-' . $honor->year . '.pdf'
    );
}
}
