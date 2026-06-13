<?php

namespace App\Exports;

use App\Models\MonthlyHonor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MonthlyHonorsExport implements FromCollection, WithHeadings
{
    public function __construct(
        public int $month,
        public int $year
    ) {}

    public function collection()
    {
        return MonthlyHonor::with('teacher')
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->get()
            ->map(function ($honor) {
                return [
                    'nama_guru' => $honor->teacher->name,
                    'bulan' => $honor->month,
                    'tahun' => $honor->year,
                    'total_jp' => $honor->total_teaching_hours,
                    'honor_mengajar' => $honor->total_teaching_honor,
                    'transport' => $honor->total_transport,
                    'grand_total' => $honor->grand_total,
                    'status' => $honor->payment_status === 'paid' ? 'Sudah Dibayar' : 'Belum Dibayar',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Nama Guru',
            'Bulan',
            'Tahun',
            'Total JP',
            'Honor Mengajar',
            'Transport',
            'Grand Total',
            'Status Pembayaran',
        ];
    }
}