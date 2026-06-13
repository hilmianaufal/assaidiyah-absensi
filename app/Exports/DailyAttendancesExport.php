<?php

namespace App\Exports;

use App\Models\DailyAttendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DailyAttendancesExport implements FromCollection, WithHeadings
{
    public function __construct(
        public string $date
    ) {}

    public function collection()
    {
        return DailyAttendance::with('teacher')
            ->whereDate('attendance_date', $this->date)
            ->get()
            ->map(function ($attendance) {
                return [
                    'nama_guru' => $attendance->teacher->name,
                    'tanggal' => $attendance->attendance_date,
                    'jam_masuk' => $attendance->check_in_time,
                    'status_masuk' => $attendance->check_in_status,
                    'jam_pulang' => $attendance->check_out_time,
                    'status_pulang' => $attendance->check_out_status,
                    'transport' => $attendance->transport_amount,
                    'keterangan' => $attendance->note,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Nama Guru',
            'Tanggal',
            'Jam Masuk',
            'Status Masuk',
            'Jam Pulang',
            'Status Pulang',
            'Transport',
            'Keterangan',
        ];
    }
}