<div>
    <x-slot name="header">
        Honor Saya
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <p class="text-blue-100 text-sm">Portal Guru</p>
            <h1 class="text-2xl lg:text-3xl font-extrabold">Honor Saya</h1>
            <p class="text-blue-50 mt-2">
                Lihat rincian honor mengajar dan transport Anda.
            </p>
        </section>

        <section class="grid grid-cols-4 gap-4">
            <select wire:model.live="month"
                class="rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}">
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>

            <select wire:model.live="year"
                class="rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                @foreach (range(now()->year - 2, now()->year + 1) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Honor Mengajar</p>
                <h3 class="text-2xl font-black text-slate-900">
                    Rp{{ number_format($honor?->total_teaching_honor ?? 0, 0, ',', '.') }}
                </h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Transport</p>
                <h3 class="text-2xl font-black text-slate-900">
                    Rp{{ number_format($honor?->total_transport ?? 0, 0, ',', '.') }}
                </h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Potongan Alpa</p>
                <h3 class="text-2xl font-black text-red-600">
                    - Rp{{ number_format($honor?->total_deduction ?? 0, 0, ',', '.') }}
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    {{ $honor?->total_absent_hours ?? 0 }} JP tidak hadir
                </p>
            </div>


         <div class="bg-gradient-to-br from-blue-700 to-sky-500 rounded-3xl p-5 shadow-xl text-white">
                <p class="text-sm text-blue-100">Total Honor</p>
                <h3 class="text-2xl font-black">
                    Rp{{ number_format($honor?->grand_total ?? 0, 0, ',', '.') }}
                </h3>
            </div>
            @if ($honor->payments->count())
                <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-black text-slate-500 mb-3">
                        Riwayat Pembayaran
                    </p>

                    <div class="space-y-2">
                        @foreach ($honor->payments as $payment)
                            <div class="flex items-center justify-between gap-3 rounded-xl bg-white p-3">
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
                                </div>

                                <p class="font-black text-emerald-700">
                                    Rp{{ number_format($payment->amount, 0, ',', '.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        <section class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Status Pembayaran</p>
                    <h3 class="text-xl font-black">
                        @if ($honor?->payment_status === 'paid')
                            Sudah Dibayar
                        @else
                            Belum Dibayar
                        @endif
                    </h3>
                </div>

                @if ($honor?->payment_status === 'paid')
                    <span class="px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 font-black text-sm">
                        Paid
                    </span>
                @else
                    <span class="px-4 py-2 rounded-full bg-amber-100 text-amber-700 font-black text-sm">
                        Unpaid
                    </span>
                @endif
            </div>

            @if ($honor)
                <a href="{{ route('teacher.honors.pdf', [$month, $year]) }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-red-500 px-5 py-3 font-black text-white shadow">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    Download Slip Honor PDF
                </a>
            @endif
        </section>
            <section class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <h3 class="text-xl font-black mb-4">Tambahan Honor</h3>

                <div class="space-y-3">
                    @forelse ($additionalHonors as $additional)
                        <div class="rounded-2xl bg-blue-50 p-4 flex items-center justify-between gap-4">
                            <div>
                                <p class="font-black text-slate-900">
                                    {{ $additional->title }}
                                </p>

                                @if ($additional->note)
                                    <p class="text-sm text-slate-500">
                                        {{ $additional->note }}
                                    </p>
                                @endif
                            </div>

                            <p class="font-black text-blue-700">
                                Rp{{ number_format($additional->amount, 0, ',', '.') }}
                            </p>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-slate-50 p-8 text-center text-slate-500">
                            Belum ada tambahan honor pada bulan ini.
                        </div>
                    @endforelse
                </div>
            </section>
        <section class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
            <h3 class="text-xl font-black mb-4">Rincian Mengajar</h3>

            <div class="space-y-3">
                @forelse ($subjectAttendances as $item)
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <div class="flex justify-between gap-3">
                            <div>
                                <p class="font-black">{{ $item->subject->name }}</p>
                                <p class="text-sm text-slate-500">
                                    {{ $item->class_name }} -
                                    {{ \Carbon\Carbon::parse($item->teaching_date)->translatedFormat('d F Y') }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="font-black text-blue-700">
                                    Rp{{ number_format($item->teaching_honor, 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ $item->hours_count }} JP
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl bg-slate-50 p-8 text-center text-slate-500">
                        Belum ada rincian mengajar pada bulan ini.
                    </div>
                @endforelse
            </div>
        </section>
        @if ($honor && $honor->payments->count())
            <section class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <h3 class="text-xl font-black mb-4">Riwayat Pembayaran</h3>

                <div class="space-y-3">
                    @foreach ($honor->payments as $payment)
                        <div class="rounded-2xl bg-emerald-50 p-4 flex items-center justify-between gap-4">
                            <div>
                                <p class="font-black text-slate-900">
                                    Rp{{ number_format($payment->amount, 0, ',', '.') }}
                                </p>

                                <p class="text-sm text-slate-500">
                                    {{ \Carbon\Carbon::parse($payment->payment_date)->translatedFormat('d F Y') }}
                                    • {{ strtoupper($payment->payment_method) }}
                                </p>
                            </div>

                            <a href="{{ route('honor-payments.receipt', $payment->id) }}"
                                class="px-4 py-2 rounded-2xl bg-white text-blue-700 font-black">
                                Bukti
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
