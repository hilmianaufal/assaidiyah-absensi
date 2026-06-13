<?php

namespace App\Console\Commands;

use App\Models\AdditionalHonor;
use App\Models\DailyAttendance;
use App\Models\MonthlyHonor;
use App\Models\SubjectAttendance;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyHonor extends Command
{
    protected $signature = 'honor:generate-monthly {month?} {year?}';

    protected $description = 'Generate rekap honor bulanan guru secara otomatis';

    public function handle(): int
    {
        $targetDate = now()->subMonth();

        $month = $this->argument('month') ?: $targetDate->month;
        $year = $this->argument('year') ?: $targetDate->year;

        $teachers = Teacher::where('is_active', true)->get();

        foreach ($teachers as $teacher) {
            $subjectQuery = SubjectAttendance::where('teacher_id', $teacher->id)
                ->whereMonth('teaching_date', $month)
                ->whereYear('teaching_date', $year);

            $transportQuery = DailyAttendance::where('teacher_id', $teacher->id)
                ->whereMonth('attendance_date', $month)
                ->whereYear('attendance_date', $year);

            $totalTeachingHours = (int) $subjectQuery->sum('hours_count');
            $totalTeachingHonor = (int) $subjectQuery->sum('teaching_honor');
            $totalTransport = (int) $transportQuery->sum('transport_amount');

            $totalAdditionalHonor = (int) AdditionalHonor::where('teacher_id', $teacher->id)
                ->where('month', $month)
                ->where('year', $year)
                ->sum('amount');

            $grandTotal = $totalTeachingHonor + $totalTransport + $totalAdditionalHonor;


            MonthlyHonor::updateOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'month' => $month,
                    'year' => $year,
                ],
                [
                    'total_teaching_hours' => $totalTeachingHours,
                    'total_teaching_honor' => $totalTeachingHonor,
                    'total_transport' => $totalTransport,
                    'grand_total' => $grandTotal,
                    'payment_status' => 'unpaid',
                    'total_additional_honor' => $totalAdditionalHonor,
                ]
            );
        }

        $this->info("Rekap honor bulan {$month}-{$year} berhasil dibuat.");

        return Command::SUCCESS;
    }
}
