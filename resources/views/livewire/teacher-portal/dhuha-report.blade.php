<div>
    <x-slot name="header">
        Laporan Sholat Dhuha
    </x-slot>

    <div class="space-y-5">
        <section class="rounded-[2rem] bg-gradient-to-r from-amber-500 via-orange-500 to-yellow-400 p-6 text-white shadow-xl">
            <p class="text-amber-100 text-sm font-bold">Laporan Pagi</p>
            <h1 class="text-2xl font-black">Sholat Dhuha</h1>
            <p class="text-sm text-amber-50 mt-1">
                Pilih guru yang hadir, simpan laporan, lalu kirim preview WA manual.
            </p>
        </section>

        @if (session('success'))
            <div class="rounded-2xl bg-emerald-100 text-emerald-700 p-4 font-bold">
                {{ session('success') }}
            </div>
        @endif

        @if (! $todaySchedule)
            <section class="rounded-[2rem] bg-white p-8 text-center shadow-sm border border-slate-100">
                <div class="mx-auto w-16 h-16 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mb-4">
                    <i data-lucide="calendar-x" class="w-8 h-8"></i>
                </div>

                <h2 class="text-xl font-black text-slate-900">
                    Tidak Ada Jadwal Dhuha Hari Ini
                </h2>

                <p class="text-sm text-slate-500 mt-2">
                    Menu ini hanya aktif untuk guru yang dijadwalkan sebagai petugas dhuha.
                </p>
            </section>
        @else
            <section class="rounded-[2rem] bg-white p-5 shadow-sm border border-slate-100">
                <p class="text-xs font-black text-amber-600 uppercase">Petugas Hari Ini</p>

                <h2 class="text-xl font-black text-slate-900 mt-1">
                    {{ $todaySchedule->teacher?->name }}
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Laporan • {{ $indonesianDate }}
                </p>
            </section>

            <section class="rounded-[2rem] bg-white p-5 shadow-sm border border-slate-100">
                <form wire:submit="save" class="space-y-5">
                    <div>
                        <label class="text-sm font-black text-slate-700">Status Pelaksanaan</label>

                        <div class="grid grid-cols-2 gap-3 mt-2">
                            <label class="rounded-2xl border p-4 cursor-pointer {{ $status === 'done' ? 'bg-emerald-50 border-emerald-400 text-emerald-700' : 'bg-slate-50 border-slate-100 text-slate-500' }}">
                                <input type="radio" wire:model.live="status" value="done" class="hidden">
                                <div class="font-black">Terlaksana</div>
                                <div class="text-xs mt-1">Dhuha berjalan</div>
                            </label>

                            <label class="rounded-2xl border p-4 cursor-pointer {{ $status === 'not_done' ? 'bg-red-50 border-red-400 text-red-700' : 'bg-slate-50 border-slate-100 text-slate-500' }}">
                                <input type="radio" wire:model.live="status" value="not_done" class="hidden">
                                <div class="font-black">Tidak</div>
                                <div class="text-xs mt-1">Tidak terlaksana</div>
                            </label>
                        </div>
                    </div>

                    <input
                        wire:model.live.debounce.300ms="searchTeacher"
                        type="text"
                        placeholder="Cari nama guru..."
                        class="mb-3 w-full rounded-2xl border-slate-200 focus:border-amber-500 focus:ring-amber-500">
                    <div class="rounded-[1.5rem] bg-slate-50 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <label class="text-sm font-black text-slate-700">
                                    Guru yang Hadir
                                </label>
                                <p class="text-xs text-slate-500">
                                    Pilih nama guru yang hadir saat sholat dhuha.
                                </p>
                            </div>

                            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-black">
                                {{ count($present_teacher_ids) }} Hadir
                            </span>
                        </div>

                        <div class="space-y-2 max-h-[340px] overflow-y-auto pr-1">
                            @forelse ($teachers as $teacher)
                                <label class="flex items-center gap-3 rounded-2xl bg-white p-3 border border-slate-100">
                                    <input
                                        type="checkbox"
                                        wire:model.live="present_teacher_ids"
                                        value="{{ $teacher->id }}"
                                        class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">

                                    <div class="flex-1 min-w-0">
                                        <p class="font-black text-slate-900 truncate">
                                            {{ $teacher->name }}
                                        </p>

                                        <p class="text-xs text-slate-500 truncate">
                                            {{ $teacher->position ?? 'Guru' }}
                                        </p>
                                    </div>
                                </label>
                            @empty
                                <div class="rounded-2xl bg-white p-5 text-center text-slate-500">
                                    Belum ada data guru aktif.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-black text-slate-700">Keterangan</label>
                        <textarea wire:model.live="note" rows="4"
                            placeholder="Contoh: Kegiatan berjalan tertib dan lancar."
                            class="mt-2 w-full rounded-2xl border-slate-200 focus:border-amber-500 focus:ring-amber-500"></textarea>
                    </div>

                    <button type="submit"
                        class="w-full rounded-2xl bg-amber-500 text-white py-4 font-black shadow-lg shadow-amber-500/30">
                        Simpan Laporan
                    </button>
                </form>
            </section>

            <section class="rounded-[2rem] bg-slate-950 p-5 text-white shadow-xl">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div>
                        <p class="text-xs font-black text-emerald-300 uppercase">Preview WhatsApp</p>
                        <h3 class="text-xl font-black">Pesan Group</h3>
                    </div>

                    <div class="w-12 h-12 rounded-2xl bg-emerald-500 flex items-center justify-center">
                        <i data-lucide="message-circle" class="w-6 h-6"></i>
                    </div>
                </div>

                <pre class="whitespace-pre-wrap text-sm leading-relaxed bg-white/10 rounded-2xl p-4 font-sans">{{ $waMessage }}</pre>

                <a href="{{ $waUrl }}" target="_blank"
                    class="mt-4 flex items-center justify-center gap-2 w-full rounded-2xl bg-emerald-500 text-white py-4 font-black shadow-lg shadow-emerald-500/30">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    Buka WhatsApp
                </a>

                <p class="text-xs text-slate-400 text-center mt-3">
                    WhatsApp akan terbuka dengan pesan otomatis. Pilih group tujuan secara manual.
                </p>
            </section>
        @endif
    </div>

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</div>
