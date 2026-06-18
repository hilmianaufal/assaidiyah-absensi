<div>
    <x-slot name="header">
        Honor Saya
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <div>
                <p class="text-blue-100 text-sm">Portal Guru</p>
                <h1 class="text-2xl lg:text-3xl font-extrabold">Honor Saya</h1>
                <p class="text-blue-50 mt-2">
                    Rincian honor mengajar, transport, tambahan honor, potongan alpa, dan pembayaran per lembaga.
                </p>
            </div>
        </section>

        <section class="grid grid-cols-2 gap-4">
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <label class="text-sm font-bold text-slate-500">Bulan</label>
                <select wire:model.live="month"
                    class="mt-2 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}">
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <label class="text-sm font-bold text-slate-500">Tahun</label>
                <select wire:model.live="year"
                    class="mt-2 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    @foreach (range(now()->year - 3, now()->year + 1) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </section>

        <section class="rounded-3xl bg-gradient-to-r from-blue-600 to-sky-500 p-6 text-white shadow-xl">
            <p class="text-sm text-blue-100">Total Honor Bulan Ini</p>
            <h2 class="text-3xl font-black mt-1">
                Rp{{ number_format($totalHonor ?? 0, 0, ',', '.') }}
            </h2>
            <p class="text-sm text-blue-100 mt-2">
                Total dari semua lembaga pada periode ini.
            </p>
        </section>

        <section class="grid grid-cols-4 gap-4">
        <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
            <p class="text-sm text-slate-500">
                Honor Mengajar Berjalan
            </p>

            <h3 class="text-2xl font-black text-emerald-700 mt-2">
                Rp{{ number_format($runningTeachingHonor ?? 0, 0, ',', '.') }}
            </h3>

            <p class="text-xs text-slate-400 mt-2">
                Akumulasi dari absensi mapel bulan ini.
            </p>
        </div>
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                    <p class="text-sm text-slate-500">Transport Berjalan</p>

                    <h3 class="text-2xl font-black text-amber-600 mt-2">
                        Rp{{ number_format($runningTransport ?? 0, 0, ',', '.') }}
                    </h3>

                    <p class="text-xs text-slate-400 mt-2">
                        Cair jika absen masuk dan pulang valid.
                    </p>
                </div>
        <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
            <p class="text-sm text-slate-500">
                Total JP Hadir
            </p>

            <h3 class="text-2xl font-black text-blue-700 mt-2">
                {{ $runningHours ?? 0 }} JP
            </h3>

            <p class="text-xs text-slate-400 mt-2">
                Total jam mengajar yang sudah hadir.
            </p>
        </div>
    </section>

        <section class="grid gap-4">
            @forelse ($honors as $honor)
                @php
                    $paidTotal = $honor->payments->sum('amount');
                    $remaining = max(($honor->grand_total ?? 0) - $paidTotal, 0);
                @endphp

                <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-black text-blue-600">
                                {{ $honor->institution?->name ?? 'Tanpa Lembaga' }}
                            </p>

                            <h3 class="text-xl font-black text-slate-900 mt-1">
                                Rp{{ number_format($honor->grand_total ?? 0, 0, ',', '.') }}
                            </h3>

                            <p class="text-sm text-slate-500 mt-1">
                                Total diterima periode ini
                            </p>
                        </div>

                        <div>
                            @if ($honor->payment_status === 'paid')
                                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-black">
                                    Lunas
                                </span>
                            @elseif ($honor->payment_status === 'partial')
                                <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-black">
                                    Dibayar Sebagian
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-black">
                                    Belum Dibayar
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-5">
                        <div class="rounded-2xl bg-blue-50 p-4">
                            <p class="text-xs text-blue-500">Honor Paket</p>
                            <p class="font-black text-blue-700">
                                Rp{{ number_format($honor->total_teaching_honor ?? 0, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-blue-400 mt-1">
                                {{ $honor->total_teaching_hours ?? 0 }} JP
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs text-slate-500">Transport</p>
                            <p class="font-black text-slate-900">
                                Rp{{ number_format($honor->total_transport ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-emerald-50 p-4">
                            <p class="text-xs text-emerald-500">Tambahan</p>
                            <p class="font-black text-emerald-700">
                                Rp{{ number_format($honor->total_additional_honor ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-red-50 p-4">
                            <p class="text-xs text-red-500">Potongan Alpa</p>
                            <p class="font-black text-red-700">
                                - Rp{{ number_format($honor->total_deduction ?? 0, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-red-400 mt-1">
                                {{ $honor->total_absent_hours ?? 0 }} JP
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <div class="rounded-2xl bg-emerald-50 p-4">
                            <p class="text-xs text-emerald-500">Sudah Dibayar</p>
                            <p class="font-black text-emerald-700">
                                Rp{{ number_format($paidTotal, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-amber-50 p-4">
                            <p class="text-xs text-amber-600">Sisa Pembayaran</p>
                            <p class="font-black text-amber-700">
                                Rp{{ number_format($remaining, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('teacher.honors.pdf-by-honor', $honor->id) }}"
                            class="px-4 py-2 rounded-2xl bg-red-50 text-red-700 font-black">
                            Download Slip
                        </a>
                    </div>

                    @if ($honor->payments->count())
                        <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-black text-slate-500 mb-3">
                                Riwayat Pembayaran
                            </p>

                            <div class="space-y-2">
                                @foreach ($honor->payments as $payment)
                                    <div class="rounded-xl bg-white p-3 flex items-center justify-between gap-3">
                                        <div>
                                            <p class="font-bold text-slate-800">
                                                {{ \Carbon\Carbon::parse($payment->payment_date)->translatedFormat('d F Y') }}
                                            </p>

                                            <p class="text-xs text-slate-500">
                                                {{ strtoupper($payment->payment_method) }}
                                                @if ($payment->reference_number)
                                                    • {{ $payment->reference_number }}
                                                @endif
                                            </p>

                                            @if ($payment->note)
                                                <p class="text-xs text-slate-400 mt-1">
                                                    {{ $payment->note }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="text-right">
                                            <p class="font-black text-emerald-700">
                                                Rp{{ number_format($payment->amount, 0, ',', '.') }}
                                            </p>

                                            <a href="{{ route('honor-payments.receipt', $payment->id) }}"
                                                class="text-xs font-black text-blue-600">
                                                Bukti
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-3xl p-10 text-center text-slate-500">
                    Belum ada rekap honor pada periode ini.
                </div>
            @endforelse
        </section>
    </div>
</div>
