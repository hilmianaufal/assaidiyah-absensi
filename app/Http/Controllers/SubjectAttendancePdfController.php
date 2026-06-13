<?php

namespace App\Http\Controllers;

use App\Models\SubjectAttendance;
use Barryvdh\DomPDF\Facade\Pdf;

class SubjectAttendancePdfController extends Controller
{
    public function show(string $date)
    {
        $attendances = SubjectAttendance::with(['teacher', 'subject'])
            ->whereDate('teaching_date', $date)
            ->orderBy('start_time')
            ->get();

        $pdf = Pdf::loadView('pdf.subject-attendance', [
            'attendances' => $attendances,
            'date' => $date,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('rekap-absensi-mapel-' . $date . '.pdf');
    }
}