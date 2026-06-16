<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Honor Lembaga</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 11px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            color: #1d4ed8;
        }

        .subtitle {
            color: #64748b;
            margin-top: 4px;
        }

        .summary {
            margin-bottom: 14px;
            padding: 12px;
            border: 1px solid #dbeafe;
            background: #eff6ff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #1d4ed8;
            color: white;
            padding: 8px;
            text-align: left;
        }

        td {
            padding: 7px;
            border-bottom: 1px solid #e2e8f0;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .total-row td {
            font-weight: bold;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .footer {
            margin-top: 24px;
            font-size: 10px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">REKAP HONOR GURU PER LEMBAGA</div>
        <div class="subtitle">
            {{ $institution->name }} -
            {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
            {{ $year }}
        </div>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td><strong>Lembaga</strong></td>
                <td>{{ $institution->name }}</td>
                <td><strong>Total Guru</strong></td>
                <td>{{ $honors->pluck('teacher_id')->unique()->count() }}</td>
            </tr>
            <tr>
                <td><strong>Total Honor</strong></td>
                <td>Rp{{ number_format($totalHonor, 0, ',', '.') }}</td>
                <td><strong>Total Dibayar</strong></td>
                <td>Rp{{ number_format($totalPaid, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th class="center" width="30">No</th>
                <th>Nama Guru</th>
                <th class="right">Honor Paket</th>
                <th class="right">Transport</th>
                <th class="right">Tambahan</th>
                <th class="right">Potongan</th>
                <th class="right">Grand Total</th>
                <th class="right">Dibayar</th>
                <th class="right">Sisa</th>
                <th class="center">Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($honors as $honor)
                @php
                    $paid = $honor->payments->sum('amount');
                    $remaining = max(($honor->grand_total ?? 0) - $paid, 0);
                @endphp

                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $honor->teacher?->name ?? '-' }}</td>
                    <td class="right">Rp{{ number_format($honor->total_teaching_honor ?? 0, 0, ',', '.') }}</td>
                    <td class="right">Rp{{ number_format($honor->total_transport ?? 0, 0, ',', '.') }}</td>
                    <td class="right">Rp{{ number_format($honor->total_additional_honor ?? 0, 0, ',', '.') }}</td>
                    <td class="right">Rp{{ number_format($honor->total_deduction ?? 0, 0, ',', '.') }}</td>
                    <td class="right"><strong>Rp{{ number_format($honor->grand_total ?? 0, 0, ',', '.') }}</strong></td>
                    <td class="right">Rp{{ number_format($paid, 0, ',', '.') }}</td>
                    <td class="right">Rp{{ number_format($remaining, 0, ',', '.') }}</td>
                    <td class="center">
                        @if ($honor->payment_status === 'paid')
                            LUNAS
                        @elseif ($honor->payment_status === 'partial')
                            SEBAGIAN
                        @else
                            BELUM
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="center">
                        Belum ada data honor pada periode ini.
                    </td>
                </tr>
            @endforelse

            <tr class="total-row">
                <td colspan="2">TOTAL</td>
                <td class="right">Rp{{ number_format($honors->sum('total_teaching_honor'), 0, ',', '.') }}</td>
                <td class="right">Rp{{ number_format($honors->sum('total_transport'), 0, ',', '.') }}</td>
                <td class="right">Rp{{ number_format($honors->sum('total_additional_honor'), 0, ',', '.') }}</td>
                <td class="right">Rp{{ number_format($honors->sum('total_deduction'), 0, ',', '.') }}</td>
                <td class="right">Rp{{ number_format($honors->sum('grand_total'), 0, ',', '.') }}</td>
                <td class="right">Rp{{ number_format($totalPaid, 0, ',', '.') }}</td>
                <td class="right">Rp{{ number_format(max($totalHonor - $totalPaid, 0), 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <table style="margin-top: 36px;">
        <tr>
            <td style="border: none; text-align: center;">
                Mengetahui,<br>
                Kepala Lembaga<br><br><br><br>
                ____________________
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
