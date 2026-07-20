<div>
    <x-slot name="header">
        Absensi Mata Pelajaran
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-blue-100 text-sm">Honor Mengajar</p>
                    <h1 class="text-2xl lg:text-3xl font-extrabold">Absensi Per Mata Pelajaran</h1>
                    <p class="text-blue-50 mt-2">Mencatat kehadiran mengajar dan menghitung honor per JP.</p>
                </div>
                <a href="{{ route('subject-attendances.pdf', $date) }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-red-500 text-white font-bold shadow">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    Cetak PDF
                </a>
                <button wire:click="exportExcel"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-emerald-500 text-white font-bold shadow">
                    <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                    Export Excel
                </button>
                <button wire:click="create"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-white text-blue-700 font-bold shadow">
                    <i data-lucide="book-check" class="w-5 h-5"></i>
                    Absen Mapel
                </button>
            </div>
        </section>

        <section class="grid lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="relative">
                    <i data-lucide="search" class="w-5 h-5 absolute left-4 top-3.5 text-slate-400"></i>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Cari guru, mapel, atau kelas..."
                        class="w-full pl-12 pr-4 py-3 rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <input wire:model.live="date" type="date"
                    class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
            </div>
        </section>

        <section class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-slate-50 text-left text-sm text-slate-500">
                    <tr>
                        <th class="p-5">Guru</th>
                        <th class="p-5">Mapel</th>
                        <th class="p-5">Kelas</th>
                        <th class="p-5">Tanggal</th>
                        <th class="p-5">JP</th>
                        <th class="p-5">Honor</th>
                        <th class="p-5 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($attendances as $attendance)
                        <tr>
                            <td class="p-5 font-bold">{{ $attendance->teacher->name }}</td>
                            <td class="p-5">{{ $attendance->subject->name }}</td>
                            <td class="p-5">{{ $attendance->class_name }}</td>
                            <td class="p-5">{{ $attendance->teaching_date }}</td>
                            <td class="p-5">{{ $attendance->hours_count }}</td>
                            <td class="p-5 font-bold">
                                Rp{{ number_format($attendance->teaching_honor, 0, ',', '.') }}
                            </td>
                            <td class="p-5 text-right">
                                <button wire:click="delete({{ $attendance->id }})"
                                    wire:confirm="Yakin hapus absensi mapel ini?"
                                    class="p-2.5 rounded-xl bg-red-50 text-red-600">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500">
                                Belum ada absensi mata pelajaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        {{ $attendances->links() }}
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-lg bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl p-6">
                <h3 class="text-xl font-extrabold mb-2">Absensi Mata Pelajaran</h3>
                <p class="text-sm text-slate-500 mb-5">Pilih jadwal mengajar yang sedang berlangsung.</p>

                <select wire:model="teaching_schedule_id"
                    class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Pilih Jadwal</option>
                    @foreach ($schedules as $schedule)
                        <option value="{{ $schedule->id }}">
                            {{ $schedule->teacher->name }} -
                            {{ $schedule->subject->name }} -
                            {{ $schedule->class_name }} -
                            {{ $schedule->day }}
                            {{ substr($schedule->start_time, 0, 5) }}
                        </option>
                    @endforeach
                </select>

                @error('teaching_schedule_id')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror

                <div class="flex gap-3 pt-5">
                    <button wire:click="$set('showModal', false)"
                        class="flex-1 px-5 py-3 rounded-2xl bg-slate-100 font-bold">
                        Batal
                    </button>

                    <button wire:click="save"
                        class="flex-1 px-5 py-3 rounded-2xl bg-blue-600 text-white font-bold shadow">
                        Simpan Absen
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>