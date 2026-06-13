<?php

namespace App\Exports;

use App\Models\SubjectAttendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubjectAttendancesExport implements FromCollection, WithHeadings
{
    public function __construct(
        public string $date
    ) {}

    public function collection()
    {
        return SubjectAttendance::with(['teacher', 'subject'])
            ->whereDate('teaching_date', $this->date)
            ->get()
            ->map(function ($attendance) {
                return [
                    'nama_guru' => $attendance->teacher->name,
                    'mata_pelajaran' => $attendance->subject->name,
                    'kelas' => $attendance->class_name,
                    'tanggal' => $attendance->teaching_date,
                    'jam_mulai' => $attendance->start_time,
                    'jam_selesai' => $attendance->end_time,
                    'jumlah_jp' => $attendance->hours_count,
                    'honor_per_jp' => $attendance->hourly_rate,
                    'total_honor' => $attendance->teaching_honor,
                    'status' => $attendance->status,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Nama Guru',
            'Mata Pelajaran',
            'Kelas',
            'Tanggal',
            'Jam Mulai',
            'Jam Selesai',
            'Jumlah JP',
            'Honor Per JP',
            'Total Honor',
            'Status',
        ];
    }
}