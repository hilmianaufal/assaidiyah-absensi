<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Honor Guru</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 12px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            color: #1d4ed8;
        }

        .subtitle {
            color: #64748b;
            margin-top: 4px;
        }

        .box {
            border: 1px solid #dbeafe;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            padding: 9px 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #eff6ff;
            color: #1d4ed8;
            text-align: left;
        }

        .right {
            text-align: right;
            font-weight: bold;
        }

        .total {
            font-size: 15px;
            font-weight: bold;
            color: #1d4ed8;
            background: #eff6ff;
        }

        .minus {
            color: #dc2626;
        }

        .plus {
            color: #059669;
        }

        .footer {
            margin-top: 35px;
            font-size: 11px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">SLIP HONOR GURU</div>
        <div class="subtitle">Sistem Absensi & Honor Assaidiyyah</div>
    </div>

    <div class="box">
        <table>
            <tr>
                <td>Nama Guru</td>
                <td class="right">{{ $teacher->name }}</td>
            </tr>

            <tr>
                <td>Lembaga</td>
                <td class="right">
                    {{ $honor->institution?->name ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>Periode</td>
                <td class="right">
                    {{ \Carbon\Carbon::create()->month($honor->month)->translatedFormat('F') }}
                    {{ $honor->year }}
                </td>
            </tr>

            <tr>
                <td>Status Pembayaran</td>
                <td class="right">
                    @if ($honor->payment_status === 'paid')
                        LUNAS
                    @elseif ($honor->payment_status === 'partial')
                        DIBAYAR SEBAGIAN
                    @else
                        BELUM DIBAYAR
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="box">
        <table>
            <tr>
                <th colspan="2">Rincian Honor</th>
            </tr>

            <tr>
                <td>Total JP Mengajar</td>
                <td class="right">
                    {{ $honor->total_teaching_hours ?? 0 }} JP
                </td>
            </tr>

            <tr>
                <td>Honor Paket Mengajar</td>
                <td class="right">
                    Rp{{ number_format($honor->total_teaching_honor ?? 0, 0, ',', '.') }}
                </td>
            </tr>

            <tr>
                <td>Transport</td>
                <td class="right">
                    Rp{{ number_format($honor->total_transport ?? 0, 0, ',', '.') }}
                </td>
            </tr>

            <tr>
                <td>Total Tambahan Honor</td>
                <td class="right plus">
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
                                <small style="color:#64748b;">
                                    {{ $additional->note }}
                                </small>
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
                <td class="right minus">
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
        </table>
    </div>

    @if(isset($honor->payments) && $honor->payments->count())
        <div class="box">
            <table>
                <tr>
                    <th colspan="3">Riwayat Pembayaran</th>
                </tr>

                @foreach($honor->payments as $payment)
                    <tr>
                        <td>
                            {{ \Carbon\Carbon::parse($payment->payment_date)->translatedFormat('d F Y') }}
                        </td>

                        <td>
                            {{ strtoupper($payment->payment_method) }}
                            @if($payment->reference_number)
                                <br>
                                <small>{{ $payment->reference_number }}</small>
                            @endif
                        </td>

                        <td class="right">
                            Rp{{ number_format($payment->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    <table style="margin-top: 40px;">
        <tr>
            <td style="border: none; text-align: center;">
                Guru<br><br><br><br>
                <strong>{{ $teacher->name }}</strong>
            </td>

            <td style="border: none; text-align: center;">
                Bendahara<br><br><br><br>
                ____________________
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem Absensi & Honor Assaidiyyah
    </div>
</body>
</html>
