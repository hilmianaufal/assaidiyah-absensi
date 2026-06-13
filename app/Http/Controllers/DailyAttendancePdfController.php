<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use Barryvdh\DomPDF\Facade\Pdf;

class DailyAttendancePdfController extends Controller
{
    public function show(string $date)
    {
        $attendances = DailyAttendance::with('teacher')
            ->whereDate('attendance_date', $date)
            ->orderBy('check_in_time')
            ->get();

        $pdf = Pdf::loadView('pdf.daily-attendance', [
            'attendances' => $attendances,
            'date' => $date,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('rekap-absensi-harian-' . $date . '.pdf');
    }
}