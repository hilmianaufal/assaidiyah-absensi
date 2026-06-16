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

        $institutions = Institution::where('is_active', true)->get();
        $warnings = [];

        foreach ($institutions as $institution) {

            $teachers = $institution->teachers()
                ->where('is_active', true)
                ->get();

            foreach ($teachers as $teacher) {

                $hasPackage = TeacherHonorPackage::where('teacher_id', $teacher->id)
                    ->where('institution_id', $institution->id)
                    ->where('is_active', true)
                    ->exists();

                if (! $hasPackage) {
                    $warnings[] =
                        "{$teacher->name} belum memiliki paket honor di {$institution->name}";
                }
            }
        }

        if (count($warnings)) {

            $this->warn('');
            $this->warn('==============================');
            $this->warn('VALIDASI DATA HONOR');
            $this->warn('==============================');

            foreach ($warnings as $warning) {
                $this->warn($warning);
            }

            $this->warn('');
            $this->warn('Silakan lengkapi data terlebih dahulu.');

            return Command::FAILURE;
        }
        foreach ($institutions as $institution) {
            $packages = TeacherHonorPackage::with('teacher')
                ->where('institution_id', $institution->id)
                ->where('is_active', true)
                ->get();

            foreach ($packages as $package) {
                $teacher = $package->teacher;

                if (! $teacher || ! $teacher->is_active) {
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

                /*
                 * Transport tetap GLOBAL, bukan per lembaga.
                 * Supaya tidak dobel, transport hanya dimasukkan ke lembaga pertama
                 * yang diproses untuk guru tersebut pada bulan itu.
                 */
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
        }

        $this->info("Rekap honor bulan {$month}-{$year} per lembaga berhasil dibuat.");

        return Command::SUCCESS;
    }
}
