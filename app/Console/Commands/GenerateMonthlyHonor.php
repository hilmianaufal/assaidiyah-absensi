<?php

namespace App\Console\Commands;

use App\Models\AdditionalHonor;
use App\Models\DailyAttendance;
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

        if ($month < 1 || $month > 12) {
            $this->error('Bulan harus antara 1 sampai 12.');

            return Command::FAILURE;
        }

        if ($year < 2020 || $year > 2100) {
            $this->error('Tahun tidak valid.');

            return Command::FAILURE;
        }

        $packages = TeacherHonorPackage::with([
            'teacher',
            'institution',
        ])
            ->where('is_active', true)
            ->orderBy('teacher_id')
            ->orderBy('institution_id')
            ->get();

        /*
         * Transport adalah hak guru per hari, bukan per lembaga.
         * Karena satu guru dapat berada di beberapa lembaga,
         * transport hanya dimasukkan satu kali pada rekap guru.
         */
        $transportAssigned = [];

        $processed = 0;
        $skipped = 0;

        foreach ($packages as $package) {
            $teacher = $package->teacher;
            $institution = $package->institution;

            if (
                ! $teacher
                || ! $teacher->is_active
                || ! $institution
            ) {
                $skipped++;

                continue;
            }

            /*
             * Total JP yang tercatat pada lembaga tersebut.
             */
            $subjectQuery = SubjectAttendance::query()
                ->where('teacher_id', $teacher->id)
                ->where('institution_id', $institution->id)
                ->whereMonth('teaching_date', $month)
                ->whereYear('teaching_date', $year);

            $totalTeachingHours = (int) (
                clone $subjectQuery
            )->sum('hours_count');

            /*
             * Honor dasar mengikuti paket honor aktif.
             */
            $baseTeachingHonor = (int) $package->monthly_honor;

            /*
             * Tambahan honor dihitung per guru dan per lembaga.
             */
            $totalAdditionalHonor = (int) AdditionalHonor::query()
                ->where('teacher_id', $teacher->id)
                ->where('institution_id', $institution->id)
                ->where('month', $month)
                ->where('year', $year)
                ->sum('amount');

            /*
             * Potongan hanya dihitung dari JP berstatus absent.
             */
            $totalAbsentHours = (int) SubjectAttendance::query()
                ->where('teacher_id', $teacher->id)
                ->where('institution_id', $institution->id)
                ->whereMonth('teaching_date', $month)
                ->whereYear('teaching_date', $year)
                ->where('attendance_status', 'absent')
                ->sum('hours_count');

            $totalDeduction = (int) (
                $totalAbsentHours
                * $package->deduction_per_hour
            );

            /*
             * Transport hanya diberikan satu kali untuk setiap guru,
             * walaupun guru tersebut mempunyai honor di beberapa lembaga.
             *
             * Array ini direset setiap command dijalankan sehingga
             * generate ulang menghasilkan nilai yang konsisten.
             */
            $totalTransport = 0;

            if (! isset($transportAssigned[$teacher->id])) {
                $totalTransport = (int) DailyAttendance::query()
                    ->where('teacher_id', $teacher->id)
                    ->whereMonth('attendance_date', $month)
                    ->whereYear('attendance_date', $year)
                    ->sum('transport_amount');

                $transportAssigned[$teacher->id] = true;
            }

            $grandTotal = max(
                $baseTeachingHonor
                + $totalTransport
                + $totalAdditionalHonor
                - $totalDeduction,
                0
            );

            /*
             * Jangan set payment_status di sini.
             * Status pembayaran harus mengikuti transaksi pembayaran
             * yang benar-benar tersimpan di honor_payments.
             */
            $honor = MonthlyHonor::updateOrCreate(
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
                    'grand_total' => $grandTotal,
                ]
            );

            /*
             * Sinkronkan status dengan pembayaran yang sudah ada.
             * Jadi generate ulang tidak menghilangkan status lunas.
             */
            $totalPaid = (int) $honor
                ->payments()
                ->sum('amount');

            if ($totalPaid <= 0) {
                $honor->update([
                    'payment_status' => 'unpaid',
                    'paid_at' => null,
                ]);
            } elseif ($totalPaid >= $grandTotal) {
                $honor->update([
                    'payment_status' => 'paid',
                    'paid_at' => $honor->paid_at ?: now(),
                ]);
            } else {
                $honor->update([
                    'payment_status' => 'partial',
                    'paid_at' => null,
                ]);
            }

            $processed++;
        }

        $this->info(
            "Rekap honor {$month}-{$year} selesai. "
            . "Diproses: {$processed}, dilewati: {$skipped}."
        );

        return Command::SUCCESS;
    }
}
