<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi Harian</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 12px; margin-bottom: 20px; }
        .title { font-size: 20px; font-weight: bold; color: #1d4ed8; }
        .subtitle { color: #475569; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #eff6ff; color: #0f172a; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; }
        .right { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">REKAP ABSENSI HARIAN GURU</div>
        <div class="subtitle">Sekolah Assaidiyyah</div>
        <div class="subtitle">Tanggal: {{ $date }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Guru</th>
                <th>Jam Masuk</th>
                <th>Status Masuk</th>
                <th>Jam Pulang</th>
                <th>Status Pulang</th>
                <th>Transport</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $attendance->teacher->name }}</td>
                    <td class="center">{{ $attendance->check_in_time ? substr($attendance->check_in_time, 0, 5) : '-' }}</td>
                    <td class="center">{{ $attendance->check_in_status ?? '-' }}</td>
                    <td class="center">{{ $attendance->check_out_time ? substr($attendance->check_out_time, 0, 5) : '-' }}</td>
                    <td class="center">{{ $attendance->check_out_status ?? '-' }}</td>
                    <td class="right">Rp{{ number_format($attendance->transport_amount, 0, ',', '.') }}</td>
                    <td>{{ $attendance->note }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center">Belum ada data absensi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <br><br>

    <table style="border: none;">
        <tr>
            <td style="border: none; width: 70%;"></td>
            <td style="border: none; text-align: center;">
                Kepala Sekolah<br><br><br><br>
                ____________________
            </td>
        </tr>
    </table>
</body>
</html>