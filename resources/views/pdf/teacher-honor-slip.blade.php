<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Honor Guru</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 13px; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 14px; margin-bottom: 24px; }
        .title { font-size: 22px; font-weight: bold; color: #1d4ed8; }
        .subtitle { color: #475569; margin-top: 4px; }
        .box { border: 1px solid #dbeafe; border-radius: 12px; padding: 16px; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        th { background: #eff6ff; text-align: left; }
        .right { text-align: right; }
        .total { font-size: 18px; font-weight: bold; color: #1d4ed8; }
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
                <td class="right"><strong>{{ $teacher->name }}</strong></td>
            </tr>
            <tr>
                <td>Periode</td>
                <td class="right">{{ $honor->month }} / {{ $honor->year }}</td>
            </tr>
            <tr>
                <td>Status Pembayaran</td>
                <td class="right">
                    {{ $honor->payment_status === 'paid' ? 'Sudah Dibayar' : 'Belum Dibayar' }}
                </td>
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
                    <td class="right">
                        Rp{{ number_format($honor->total_transport ?? 0, 0, ',', '.') }}
                    </td>
                </tr>

                <tr>
                    <td>Total Tambahan Honor</td>
                    <td class="right">
                        Rp{{ number_format($honor->total_additional_honor ?? 0, 0, ',', '.') }}
                    </td>
                </tr>

                @if(isset($additionalHonors) && $additionalHonors->count())
                    @foreach($additionalHonors as $additional)
                        <tr>
                            <td style="padding-left: 24px;">
                                • {{ $additional->title }}
                                @if($additional->note)
                                    <br>
                                    <small style="color:#64748b;">{{ $additional->note }}</small>
                                @endif
                            </td>
                            <td class="right">
                                Rp{{ number_format($additional->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                @endif
                <tr>
                    <td>Potongan Alpa</td>
                    <td class="right">
                        - Rp{{ number_format($honor->total_deduction ?? 0, 0, ',', '.') }}
                    </td>
                </tr>

                <tr>
                    <td>Total JP Alpa</td>
                    <td class="right">
                        {{ $honor->total_absent_hours ?? 0 }} JP
                    </td>
                </tr>
                <tr>
                    <td class="total">Grand Total</td>
                    <td class="right total">
                        Rp{{ number_format($honor->grand_total ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <br><br>

    <table style="border: none;">
        <tr>
            <td style="border: none; text-align: center;">
                Guru Penerima<br><br><br><br>
                <strong>{{ $teacher->name }}</strong>
            </td>
            <td style="border: none; text-align: center;">
                Bendahara<br><br><br><br>
                ____________________
            </td>
        </tr>
    </table>
</body>
</html>
