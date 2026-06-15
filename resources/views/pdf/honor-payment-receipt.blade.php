<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bukti Pembayaran Honor</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 12px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
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

        .box {
            border: 1px solid #dbeafe;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .right {
            text-align: right;
            font-weight: bold;
        }

        .amount {
            font-size: 22px;
            color: #059669;
            font-weight: bold;
            text-align: center;
            margin: 18px 0;
        }

        .footer {
            margin-top: 28px;
            text-align: center;
            color: #64748b;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">BUKTI PEMBAYARAN HONOR</div>
        <div class="subtitle">Sekolah Assaidiyyah</div>
    </div>

    <div class="box">
        <table>
            <tr>
                <td>Nama Guru</td>
                <td class="right">{{ $teacher->name }}</td>
            </tr>

            <tr>
                <td>Periode</td>
                <td class="right">{{ $honor->month }} / {{ $honor->year }}</td>
            </tr>

            <tr>
                <td>Tanggal Bayar</td>
                <td class="right">
                    {{ \Carbon\Carbon::parse($payment->payment_date)->translatedFormat('d F Y') }}
                </td>
            </tr>

            <tr>
                <td>Metode</td>
                <td class="right">{{ strtoupper($payment->payment_method) }}</td>
            </tr>

            @if ($payment->reference_number)
                <tr>
                    <td>No. Referensi</td>
                    <td class="right">{{ $payment->reference_number }}</td>
                </tr>
            @endif
        </table>
    </div>

    <div class="amount">
        Rp{{ number_format($payment->amount, 0, ',', '.') }}
    </div>

    @if ($payment->note)
        <div class="box">
            <strong>Catatan:</strong><br>
            {{ $payment->note }}
        </div>
    @endif

    <table style="margin-top: 32px;">
        <tr>
            <td style="border: none; text-align: center;">
                Penerima<br><br><br><br>
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
