<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Honor</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 15px; }
        .title { font-size: 22px; font-weight: bold; color: #1d4ed8; }
        .subtitle { font-size: 13px; color: #475569; }
        .box { margin-top: 25px; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        td, th { padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
        th { text-align: left; background: #eff6ff; }
        .total { font-size: 18px; font-weight: bold; color: #1d4ed8; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">SLIP HONOR GURU</div>
        <div class="subtitle">Sekolah Assaidiyyah</div>
    </div>

    <div class="box">
        <table>
            <tr>
                <td>Nama Guru</td>
                <td class="right"><strong>{{ $honor->teacher->name }}</strong></td>
            </tr>
            <tr>
                <td>Bulan / Tahun</td>
                <td class="right">{{ $honor->month }} / {{ $honor->year }}</td>
            </tr>
            <tr>
                <td>Status Pembayaran</td>
                <td class="right">{{ $honor->payment_status === 'paid' ? 'Sudah Dibayar' : 'Belum Dibayar' }}</td>
            </tr>
        </table>
    </div>

    <div class="box">
        <table>
            <thead>
                <tr>
                    <th>Komponen</th>
                    <th class="right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total JP Mengajar</td>
                    <td class="right">{{ $honor->total_teaching_hours }} JP</td>
                </tr>
                <tr>
                    <td>Honor Mengajar</td>
                    <td class="right">Rp{{ number_format($honor->total_teaching_honor, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Transport</td>
                    <td class="right">Rp{{ number_format($honor->total_transport, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="total">Grand Total</td>
                    <td class="right total">Rp{{ number_format($honor->grand_total, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <br><br>

    <table style="border: none;">
        <tr>
            <td style="border: none; text-align: center;">
                Guru Penerima<br><br><br><br>
                {{ $honor->teacher->name }}
            </td>
            <td style="border: none; text-align: center;">
                Bendahara<br><br><br><br>
                ____________________
            </td>
        </tr>
    </table>
</body>
</html>