<div wire:poll.5s>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 lg:p-8 text-white shadow-xl">
            <div class="relative z-10">
                <p class="text-blue-100 text-sm mb-2">Realtime Monitoring</p>
                <h1 class="text-2xl lg:text-4xl font-extrabold">
                    Dashboard Absensi Assaidiyyah
                </h1>
                <p class="mt-3 max-w-2xl text-blue-50">
                    Pantau absensi masuk, pulang, transport, dan honor guru secara realtime.
                </p>
            </div>

            <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-white/10 rounded-full"></div>
            <div class="absolute right-20 top-10 w-24 h-24 bg-white/10 rounded-full"></div>
        </section>

        <section class="grid grid-cols-2 lg:grid-cols-6 gap-4">
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="w-11 h-11 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center mb-4">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <p class="text-sm text-slate-500">Guru Aktif</p>
                <h3 class="text-2xl font-extrabold">{{ $totalTeachers }}</h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
                    <i data-lucide="user-check" class="w-6 h-6"></i>
                </div>
                <p class="text-sm text-slate-500">Hadir</p>
                <h3 class="text-2xl font-extrabold">{{ $presentToday }}</h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="w-11 h-11 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mb-4">
                    <i data-lucide="user-x" class="w-6 h-6"></i>
                </div>
                <p class="text-sm text-slate-500">Belum Hadir</p>
                <h3 class="text-2xl font-extrabold">{{ $notPresentToday }}</h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mb-4">
                    <i data-lucide="clock-alert" class="w-6 h-6"></i>
                </div>
                <p class="text-sm text-slate-500">Terlambat</p>
                <h3 class="text-2xl font-extrabold">{{ $lateToday }}</h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="w-11 h-11 rounded-2xl bg-sky-100 text-sky-600 flex items-center justify-center mb-4">
                    <i data-lucide="log-out" class="w-6 h-6"></i>
                </div>
                <p class="text-sm text-slate-500">Sudah Pulang</p>
                <h3 class="text-2xl font-extrabold">{{ $checkedOutToday }}</h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="w-11 h-11 rounded-2xl bg-violet-100 text-violet-600 flex items-center justify-center mb-4">
                    <i data-lucide="wallet" class="w-6 h-6"></i>
                </div>
                <p class="text-sm text-slate-500">Transport</p>
                <h3 class="text-xl font-extrabold">
                    Rp{{ number_format($transportToday, 0, ',', '.') }}
                </h3>
            </div>
        </section>

        <section class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-xl font-extrabold">Aktivitas Hari Ini</h3>
                        <p class="text-sm text-slate-500">Update otomatis setiap 5 detik.</p>
                    </div>

                    <a href="{{ route('face-attendance.index') }}"
                        class="px-4 py-2 rounded-2xl bg-blue-600 text-white font-bold">
                        Buka Kamera
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse ($recentActivities as $activity)
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center">
                                    <i data-lucide="scan-face" class="w-5 h-5"></i>
                                </div>

                                <div>
                                    <p class="font-bold">{{ $activity->teacher->name }}</p>
                                    <p class="text-sm text-slate-500">
                                        Masuk:
                                        {{ $activity->check_in_time ? substr($activity->check_in_time, 0, 5) : '-' }}
                                        |
                                        Pulang:
                                        {{ $activity->check_out_time ? substr($activity->check_out_time, 0, 5) : '-' }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-right">
                                <p class="font-bold text-blue-700">
                                    Rp{{ number_format($activity->transport_amount, 0, ',', '.') }}
                                </p>

                                @if ($activity->check_in_status === 'ontime')
                                    <span class="text-xs text-emerald-600 font-bold">Tepat Waktu</span>
                                @elseif ($activity->check_in_status === 'late')
                                    <span class="text-xs text-amber-600 font-bold">Terlambat</span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-slate-50 p-8 text-center text-slate-500">
                            Belum ada aktivitas absensi hari ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-gradient-to-br from-slate-900 to-blue-900 rounded-3xl p-6 text-white shadow-xl">
                    <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center mb-5">
                        <i data-lucide="badge-dollar-sign" class="w-7 h-7"></i>
                    </div>

                    <p class="text-blue-100 text-sm">Honor Bulan Ini</p>
                    <h3 class="text-3xl font-extrabold mt-1">
                        Rp{{ number_format($honorThisMonth, 0, ',', '.') }}
                    </h3>

                    <a href="{{ route('monthly-honors.index') }}"
                        class="mt-5 inline-flex px-4 py-2 rounded-2xl bg-white text-blue-700 font-bold">
                        Lihat Rekap
                    </a>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                    <h3 class="text-xl font-extrabold mb-4">Shortcut</h3>

                    <div class="grid gap-3">
                        <a href="{{ route('teachers.index') }}"
                            class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 font-bold">
                            <i data-lucide="users" class="w-5 h-5 text-blue-600"></i>
                            Data Guru
                        </a>

                        <a href="{{ route('face-enrollment.index') }}"
                            class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 font-bold">
                            <i data-lucide="scan-line" class="w-5 h-5 text-blue-600"></i>
                            Registrasi Wajah
                        </a>

                        <a href="{{ route('subject-attendances.index') }}"
                            class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 font-bold">
                            <i data-lucide="book-check" class="w-5 h-5 text-blue-600"></i>
                            Absensi Mapel
                        </a>
                    </div>
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