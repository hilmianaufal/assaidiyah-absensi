<div>
    <x-slot name="header">
        Absensi Mapel Piket
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <p class="text-blue-100 text-sm">Guru Piket</p>
            <h1 class="text-2xl lg:text-3xl font-extrabold">Absensi Mata Pelajaran</h1>
            <p class="text-blue-50 mt-2">
                Catat kehadiran guru mengajar sesuai jadwal hari ini.
            </p>
        </section>
            @if($picketSchedule)
                <div class="mb-4 rounded-2xl bg-blue-50 p-4 text-blue-700 font-bold">
                    Jadwal piket hari ini:
                    {{ $picketSchedule->institution?->name }}
                </div>
            @endif
        @if (! $isAllowed)
            <div class="rounded-3xl bg-red-50 border border-red-100 p-6 text-red-700">
                <h3 class="text-xl font-black">Akses Ditolak</h3>
                <p class="mt-2 text-sm">
                    Anda bukan guru piket, atau tidak memiliki jadwal piket aktif saat ini.
                </p>
            </div>
        @else
            @if (session('success'))
                <div class="rounded-2xl bg-emerald-100 text-emerald-700 p-4 font-bold">
                    {{ session('success') }}
                </div>
            @endif

            <section class="grid gap-4">
                @forelse ($schedules as $schedule)
                    @php
                        $attendance = $attendances[$schedule->id] ?? null;

                        $statusLabel = [
                            'present' => 'Hadir',
                            'late' => 'Terlambat',
                            'permit' => 'Izin',
                            'sick' => 'Sakit',
                            'absent' => 'Alpa',
                        ][$attendance?->attendance_status] ?? 'Belum Diabsen';

                        $statusClass = match($attendance?->attendance_status) {
                            'present' => 'bg-emerald-100 text-emerald-700',
                            'late' => 'bg-amber-100 text-amber-700',
                            'permit' => 'bg-blue-100 text-blue-700',
                            'sick' => 'bg-violet-100 text-violet-700',
                            'absent' => 'bg-red-100 text-red-700',
                            default => 'bg-slate-100 text-slate-500',
                        };
                    @endphp

                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-bold text-blue-600">
                                    {{ substr($schedule->start_time, 0, 5) }}
                                    -
                                    {{ substr($schedule->end_time, 0, 5) }}
                                </p>

                                <h3 class="text-xl font-black text-slate-900 mt-1">
                                    {{ $schedule->subject->name }}
                                </h3>

                                <p class="text-sm text-slate-500">
                                    {{ $schedule->class_name }} • {{ $schedule->hours_count }} JP
                                </p>

                                <p class="text-sm text-slate-500 mt-1">
                                    Guru: <span class="font-bold">{{ $schedule->teacher->name }}</span>
                                </p>
                            </div>

                            <span class="px-3 py-1.5 rounded-full text-xs font-black {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        @if ($attendance)
                            <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs text-slate-500">Dicatat</p>
                                <p class="font-bold text-slate-700">
                                    {{ $attendance->recorded_at ? \Carbon\Carbon::parse($attendance->recorded_at)->format('H:i') : '-' }}
                                    oleh {{ $attendance->recordedByTeacher?->name ?? '-' }}
                                </p>
                                <p class="text-sm text-slate-500 mt-1">
                                    Honor: Rp{{ number_format($attendance->teaching_honor, 0, ',', '.') }}
                                </p>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 mt-4">
                            <button wire:click="markAttendance({{ $schedule->id }}, 'present')"
                                class="px-4 py-3 rounded-2xl bg-emerald-100 text-emerald-700 font-black">
                                Hadir
                            </button>

                            <button wire:click="markAttendance({{ $schedule->id }}, 'late')"
                                class="px-4 py-3 rounded-2xl bg-amber-100 text-amber-700 font-black">
                                Terlambat
                            </button>

                            <button wire:click="markAttendance({{ $schedule->id }}, 'permit')"
                                class="px-4 py-3 rounded-2xl bg-blue-100 text-blue-700 font-black">
                                Izin
                            </button>

                            <button wire:click="markAttendance({{ $schedule->id }}, 'sick')"
                                class="px-4 py-3 rounded-2xl bg-violet-100 text-violet-700 font-black">
                                Sakit
                            </button>

                            <button wire:click="markAttendance({{ $schedule->id }}, 'absent')"
                                class="px-4 py-3 rounded-2xl bg-red-100 text-red-700 font-black">
                                Alpa
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-3xl p-10 text-center text-slate-500">
                        Tidak ada jadwal mengajar hari ini.
                    </div>
                @endforelse
            </section>
        @endif
    </div>

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</div>
