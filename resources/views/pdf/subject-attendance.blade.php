<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi Mata Pelajaran</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 12px; margin-bottom: 20px; }
        .title { font-size: 20px; font-weight: bold; color: #1d4ed8; }
        .subtitle { color: #475569; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #eff6ff; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; }
        .center { text-align: center; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">REKAP ABSENSI MATA PELAJARAN</div>
        <div class="subtitle">Sekolah Assaidiyyah</div>
        <div class="subtitle">Tanggal: {{ $date }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Guru</th>
                <th>Mata Pelajaran</th>
                <th>Kelas</th>
                <th>Jam</th>
                <th>JP</th>
                <th>Honor/JP</th>
                <th>Total Honor</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($attendances as $attendance)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $attendance->teacher->name }}</td>
                    <td>{{ $attendance->subject->name }}</td>
                    <td class="center">{{ $attendance->class_name }}</td>
                    <td class="center">
                        {{ $attendance->start_time ? substr($attendance->start_time, 0, 5) : '-' }}
                        -
                        {{ $attendance->end_time ? substr($attendance->end_time, 0, 5) : '-' }}
                    </td>
                    <td class="center">{{ $attendance->hours_count }}</td>
                    <td class="right">Rp{{ number_format($attendance->hourly_rate, 0, ',', '.') }}</td>
                    <td class="right">Rp{{ number_format($attendance->teaching_honor, 0, ',', '.') }}</td>
                    <td class="center">{{ $attendance->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="center">Belum ada data absensi mata pelajaran.</td>
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