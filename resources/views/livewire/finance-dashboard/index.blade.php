<div>
    <x-slot name="header">
        Dashboard Bendahara
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <div>
                <p class="text-blue-100 text-sm">Keuangan Multi Lembaga</p>
                <h1 class="text-2xl lg:text-3xl font-extrabold">Dashboard Bendahara</h1>
                <p class="text-blue-50 mt-2">
                    Monitoring total honor, pembayaran, dan sisa pembayaran MTs, SMK, dan MA.
                </p>
            </div>
        </section>

        <section class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Total Honor</p>
                <h3 class="text-xl font-black text-blue-700">
                    Rp{{ number_format($totalHonor, 0, ',', '.') }}
                </h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Sudah Dibayar</p>
                <h3 class="text-xl font-black text-emerald-700">
                    Rp{{ number_format($totalPaid, 0, ',', '.') }}
                </h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Sisa Bayar</p>
                <h3 class="text-xl font-black text-amber-600">
                    Rp{{ number_format($totalRemaining, 0, ',', '.') }}
                </h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Jumlah Guru</p>
                <h3 class="text-xl font-black text-slate-900">
                    {{ $totalTeachers }}
                </h3>
            </div>

            <div class="bg-gradient-to-br from-blue-700 to-sky-500 rounded-3xl p-5 shadow-xl text-white">
                <p class="text-sm text-blue-100">Total Rekap</p>
                <h3 class="text-xl font-black">
                    {{ $totalRecords }}
                </h3>
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

        <section class="grid lg:grid-cols-3 gap-4">
            @foreach ($institutionSummaries as $summary)
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">Lembaga</p>
                            <h3 class="text-xl font-black text-slate-900">
                                {{ $summary['institution']->name }}
                            </h3>
                        </div>

                        <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center">
                            <i data-lucide="building-2" class="w-6 h-6"></i>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Total Honor</span>
                            <strong>Rp{{ number_format($summary['total_honor'], 0, ',', '.') }}</strong>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Sudah Dibayar</span>
                            <strong class="text-emerald-700">
                                Rp{{ number_format($summary['total_paid'], 0, ',', '.') }}
                            </strong>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Sisa Bayar</span>
                            <strong class="text-amber-600">
                                Rp{{ number_format($summary['total_remaining'], 0, ',', '.') }}
                            </strong>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Jumlah Guru</span>
                            <strong>{{ $summary['total_teachers'] }}</strong>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xl font-black text-slate-900">
                        Honor Belum Lunas
                    </h3>
                    <p class="text-sm text-slate-500">
                        Daftar rekap honor yang masih perlu dibayar.
                    </p>
                </div>

                <a href="{{ route('monthly-honors.index') }}"
                    class="px-4 py-2 rounded-2xl bg-blue-50 text-blue-700 font-black">
                    Lihat Rekap
                </a>
            </div>

            <div class="space-y-3">
                @forelse ($unpaidHonors as $honor)
                    <div class="rounded-2xl bg-slate-50 p-4 flex items-center justify-between gap-4">
                        <div>
                            <p class="font-black text-slate-900">
                                {{ $honor->teacher?->name ?? '-' }}
                            </p>
                            <p class="text-sm text-slate-500">
                                {{ $honor->institution?->name ?? '-' }}
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="font-black text-blue-700">
                                Rp{{ number_format($honor->grand_total, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-red-500 font-bold">
                                {{ $honor->payment_status === 'partial' ? 'Dibayar Sebagian' : 'Belum Dibayar' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl bg-emerald-50 p-8 text-center text-emerald-700 font-bold">
                        Semua honor pada periode ini sudah lunas.
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</div>
