<div>
    <x-slot name="header">
        Jadwal Saya
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <p class="text-blue-100 text-sm">Portal Guru</p>
            <h1 class="text-2xl lg:text-3xl font-extrabold">
                Jadwal Mengajar Saya
            </h1>
            <p class="text-blue-50 mt-2">
                Lihat jadwal mengajar pribadi berdasarkan hari.
            </p>
        </section>

        <section class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
            <select wire:model.live="day"
                class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <option value="">Semua Hari</option>
                @foreach ($days as $item)
                    <option value="{{ $item }}">{{ $item }}</option>
                @endforeach
            </select>
        </section>

        <section class="grid gap-4">
            @forelse ($schedules as $schedule)
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">{{ $schedule->day }}</p>
                            <h3 class="text-xl font-black text-slate-900">
                                {{ $schedule->subject->name }}
                            </h3>
                            <p class="text-sm text-slate-500">
                                {{ $schedule->class_name }}
                            </p>
                        </div>

                        <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center">
                            <i data-lucide="book-open" class="w-6 h-6"></i>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 mt-4">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs text-slate-500">Mulai</p>
                            <p class="font-black">
                                {{ substr($schedule->start_time, 0, 5) }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs text-slate-500">Selesai</p>
                            <p class="font-black">
                                {{ substr($schedule->end_time, 0, 5) }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs text-slate-500">JP</p>
                            <p class="font-black">
                                {{ $schedule->hours_count }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl p-10 text-center text-slate-500">
                    Belum ada jadwal mengajar.
                </div>
            @endforelse
        </section>
    </div>

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</div>