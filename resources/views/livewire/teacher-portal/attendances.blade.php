<div>
    <x-slot name="header">
        Absensi Saya
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <p class="text-blue-100 text-sm">Portal Guru</p>
            <h1 class="text-2xl lg:text-3xl font-extrabold">Absensi Saya</h1>
            <p class="text-blue-50 mt-2">
                Riwayat absensi masuk, pulang, transport, dan bukti foto.
            </p>
        </section>

        <section class="grid grid-cols-2 gap-4">
            <select wire:model.live="month"
                class="rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                @foreach (range(1, 12) as $m)
                    <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
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

        <section class="grid gap-4">
            @forelse ($attendances as $attendance)
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">
                                {{ \Carbon\Carbon::parse($attendance->attendance_date)->translatedFormat('l, d F Y') }}
                            </p>
                            <h3 class="text-lg font-black text-slate-900">
                                {{ $attendance->check_in_status === 'ontime' ? 'Tepat Waktu' : 'Terlambat / Luar Waktu' }}
                            </h3>
                        </div>

                        <div class="text-right">
                            <p class="text-sm text-slate-500">Transport</p>
                            <p class="font-black text-blue-700">
                                Rp{{ number_format($attendance->transport_amount, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs text-slate-500">Jam Masuk</p>
                            <p class="font-black">
                                {{ $attendance->check_in_time ? substr($attendance->check_in_time, 0, 5) : '-' }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs text-slate-500">Jam Pulang</p>
                            <p class="font-black">
                                {{ $attendance->check_out_time ? substr($attendance->check_out_time, 0, 5) : '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <div>
                            <p class="text-xs font-bold text-slate-500 mb-2">Foto Masuk</p>
                            @if ($attendance->check_in_photo)
                                <img src="{{ asset('storage/' . $attendance->check_in_photo) }}"
                                    class="w-full aspect-video object-cover rounded-2xl bg-slate-100">
                            @else
                                <div class="aspect-video rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 text-sm">
                                    Tidak ada
                                </div>
                            @endif
                        </div>

                        <div>
                            <p class="text-xs font-bold text-slate-500 mb-2">Foto Pulang</p>
                            @if ($attendance->check_out_photo)
                                <img src="{{ asset('storage/' . $attendance->check_out_photo) }}"
                                    class="w-full aspect-video object-cover rounded-2xl bg-slate-100">
                            @else
                                <div class="aspect-video rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 text-sm">
                                    Tidak ada
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl p-10 text-center text-slate-500">
                    Belum ada data absensi pada periode ini.
                </div>
            @endforelse

            {{ $attendances->links() }}
        </section>
    </div>
</div>