<div>
    <x-slot name="header">
        Dashboard Guru
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <p class="text-blue-100 text-sm">Portal Guru</p>
            <h1 class="text-2xl lg:text-3xl font-extrabold">
                Assalamu'alaikum, {{ $teacher->name }}
            </h1>
            <p class="text-blue-50 mt-2">
                Ringkasan absensi, jadwal mengajar, honor, dan jadwal piket Anda.
            </p>
        </section>

        <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="w-11 h-11 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center mb-4">
                    <i data-lucide="log-in" class="w-5 h-5"></i>
                </div>
                <p class="text-sm text-slate-500">Masuk Hari Ini</p>
                <h3 class="text-xl font-black">
                    {{ $todayAttendance?->check_in_time ? substr($todayAttendance->check_in_time, 0, 5) : '-' }}
                </h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="w-11 h-11 rounded-2xl bg-sky-100 text-sky-600 flex items-center justify-center mb-4">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                </div>
                <p class="text-sm text-slate-500">Pulang Hari Ini</p>
                <h3 class="text-xl font-black">
                    {{ $todayAttendance?->check_out_time ? substr($todayAttendance->check_out_time, 0, 5) : '-' }}
                </h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
                <p class="text-sm text-slate-500">Transport Hari Ini</p>
                <h3 class="text-xl font-black">
                    Rp{{ number_format($todayAttendance?->transport_amount ?? 0, 0, ',', '.') }}
                </h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mb-4">
                    <i data-lucide="badge-dollar-sign" class="w-5 h-5"></i>
                </div>
                <p class="text-sm text-slate-500">Honor Bulan Ini</p>
                <h3 class="text-xl font-black">
                    Rp{{ number_format($monthlyHonor?->grand_total ?? 0, 0, ',', '.') }}
                </h3>
            </div>
        </section>

        @if ($picketScheduleToday)
            <section class="rounded-3xl bg-emerald-50 border border-emerald-100 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-emerald-800">Anda Piket Hari Ini</h3>
                        <p class="text-sm text-emerald-700">
                            {{ substr($picketScheduleToday->start_time, 0, 5) }}
                            -
                            {{ substr($picketScheduleToday->end_time, 0, 5) }}
                        </p>
                    </div>
                </div>

                <a href="{{ route('picket-reports.create') }}"
                    class="mt-4 inline-flex px-5 py-3 rounded-2xl bg-emerald-600 text-white font-black">
                    Buat Laporan Piket
                </a>
            </section>
        @endif

        <section class="grid lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <h3 class="text-xl font-black mb-4">Absensi Mapel Hari Ini</h3>

                <div class="space-y-3">
                    @forelse ($teachingToday as $item)
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="font-black">{{ $item->subject->name }}</p>
                            <p class="text-sm text-slate-500">
                                {{ $item->class_name }} -
                                {{ $item->hours_count }} JP -
                                Rp{{ number_format($item->teaching_honor, 0, ',', '.') }}
                            </p>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-slate-50 p-5 text-center text-slate-500">
                            Belum ada absensi mapel hari ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <h3 class="text-xl font-black mb-4">Jadwal Mengajar Saya</h3>

                <div class="space-y-3">
                    @forelse ($schedules as $schedule)
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="font-black">{{ $schedule->day }} - {{ $schedule->subject->name }}</p>
                            <p class="text-sm text-slate-500">
                                {{ $schedule->class_name }}
                                |
                                {{ substr($schedule->start_time, 0, 5) }}
                                -
                                {{ substr($schedule->end_time, 0, 5) }}
                            </p>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-slate-50 p-5 text-center text-slate-500">
                            Belum ada jadwal mengajar.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</div>