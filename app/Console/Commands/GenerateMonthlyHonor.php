<?php

namespace App\Console\Commands;

use App\Models\AdditionalHonor;
use App\Models\DailyAttendance;
use App\Models\Institution;
use App\Models\MonthlyHonor;
use App\Models\SubjectAttendance;
use App\Models\TeacherHonorPackage;
use Illuminate\Console\Command;

class GenerateMonthlyHonor extends Command
{
    protected $signature = 'honor:generate-monthly {month?} {year?}';

    protected $description = 'Generate rekap honor bulanan guru per lembaga secara otomatis';

    public function handle(): int
    {
        $targetDate = now()->subMonth();

        $month = (int) ($this->argument('month') ?: $targetDate->month);
        $year = (int) ($this->argument('year') ?: $targetDate->year);

        $packages = TeacherHonorPackage::with(['teacher', 'institution'])
            ->where('is_active', true)
            ->get();

        foreach ($packages as $package) {
            $teacher = $package->teacher;
            $institution = $package->institution;

            if (! $teacher || ! $teacher->is_active || ! $institution) {
                continue;
            }

            $subjectQuery = SubjectAttendance::where('teacher_id', $teacher->id)
                ->where('institution_id', $institution->id)
                ->whereMonth('teaching_date', $month)
                ->whereYear('teaching_date', $year);

            $totalTeachingHours = (int) $subjectQuery->sum('hours_count');

            $baseTeachingHonor = (int) $package->monthly_honor;

            $totalAdditionalHonor = (int) AdditionalHonor::where('teacher_id', $teacher->id)
                ->where('institution_id', $institution->id)
                ->where('month', $month)
                ->where('year', $year)
                ->sum('amount');

            $totalAbsentHours = (int) SubjectAttendance::where('teacher_id', $teacher->id)
                ->where('institution_id', $institution->id)
                ->whereMonth('teaching_date', $month)
                ->whereYear('teaching_date', $year)
                ->where('attendance_status', 'absent')
                ->sum('hours_count');

            $totalDeduction = (int) ($totalAbsentHours * $package->deduction_per_hour);

            $alreadyHasTransport = MonthlyHonor::where('teacher_id', $teacher->id)
                ->where('month', $month)
                ->where('year', $year)
                ->where('total_transport', '>', 0)
                ->exists();

            $totalTransport = 0;

            if (! $alreadyHasTransport) {
                $totalTransport = (int) DailyAttendance::where('teacher_id', $teacher->id)
                    ->whereMonth('attendance_date', $month)
                    ->whereYear('attendance_date', $year)
                    ->sum('transport_amount');
            }

            $grandTotal = $baseTeachingHonor
                + $totalTransport
                + $totalAdditionalHonor
                - $totalDeduction;

            MonthlyHonor::updateOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'institution_id' => $institution->id,
                    'month' => $month,
                    'year' => $year,
                ],
                [
                    'total_teaching_hours' => $totalTeachingHours,
                    'total_teaching_honor' => $baseTeachingHonor,
                    'total_transport' => $totalTransport,
                    'total_additional_honor' => $totalAdditionalHonor,
                    'total_absent_hours' => $totalAbsentHours,
                    'total_deduction' => $totalDeduction,
                    'grand_total' => max($grandTotal, 0),
                    'payment_status' => 'unpaid',
                ]
            );
        }

        $this->info("Rekap honor bulan {$month}-{$year} berhasil dibuat dari paket honor aktif.");

        return Command::SUCCESS;
    }
}
