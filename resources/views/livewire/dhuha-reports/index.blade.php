<div>
    <x-slot name="header">
        Riwayat Laporan Dhuha
    </x-slot>

    <div class="space-y-5">
        <section class="rounded-[2rem] bg-gradient-to-r from-amber-500 to-orange-400 p-6 text-white shadow-xl">
            <h1 class="text-2xl font-black">Riwayat Laporan Dhuha</h1>
            <p class="text-sm text-amber-50 mt-1">
                Monitoring laporan sholat dhuha guru.
            </p>
        </section>

        <section class="bg-white rounded-[2rem] p-5 shadow-sm border border-slate-100">
            <label class="text-sm font-black text-slate-700">Tanggal</label>
            <input type="date" wire:model.live="date"
                class="mt-2 w-full rounded-2xl border-slate-200 focus:border-amber-500 focus:ring-amber-500">
        </section>

        <section class="space-y-4">
            @forelse ($reports as $report)
                <div class="bg-white rounded-[2rem] p-5 shadow-sm border border-slate-100">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-black text-amber-600 uppercase">
                                {{ $report->report_date?->locale('id')->translatedFormat('l, d F Y') }}
                            </p>

                            <h3 class="text-lg font-black text-slate-900 mt-1">
                                {{ $report->teacher?->name }}
                            </h3>

                            <p class="text-sm text-slate-500">
                                Pelapor
                            </p>
                        </div>

                        <span class="px-3 py-1 rounded-full text-xs font-black
                            {{ $report->status === 'done' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $report->status === 'done' ? 'Terlaksana' : 'Tidak Terlaksana' }}
                        </span>
                    </div>

                    <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                        <p class="text-sm font-black text-slate-700">
                            Jumlah Guru Hadir: {{ $report->teacher_count }} guru
                        </p>

                        <p class="text-sm text-slate-500 mt-2">
                            {{ $report->note ?: 'Tidak ada keterangan.' }}
                        </p>
                    </div>

                    <details class="mt-4">
                        <summary class="cursor-pointer font-black text-emerald-700">
                            Lihat Preview WA
                        </summary>

                        <pre class="mt-3 whitespace-pre-wrap text-sm bg-slate-950 text-white rounded-2xl p-4 font-sans">{{ $report->whatsapp_message }}</pre>
                    </details>
                </div>
            @empty
                <div class="bg-white rounded-[2rem] p-10 text-center text-slate-500">
                    Belum ada laporan dhuha pada tanggal ini.
                </div>
            @endforelse

            {{ $reports->links() }}
        </section>
    </div>
</div>
