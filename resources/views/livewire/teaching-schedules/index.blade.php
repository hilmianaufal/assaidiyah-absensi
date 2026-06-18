<div>
    <x-slot name="header">
        Jadwal Mengajar
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-blue-100 text-sm">Akademik Multi Lembaga</p>
                    <h1 class="text-2xl lg:text-3xl font-extrabold">Jadwal Mengajar</h1>
                    <p class="text-blue-50 mt-2">
                        Kelola jadwal mengajar berdasarkan lembaga MTs, SMK, dan MA.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button wire:click="createBulk"
                        class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-amber-500 text-white font-bold shadow">
                        <i data-lucide="table-properties" class="w-5 h-5"></i>
                        Jadwal Massal
                    </button>

                    <button wire:click="create"
                        class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-white text-blue-700 font-bold shadow">
                        <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                        Tambah Jadwal
                    </button>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl bg-emerald-100 text-emerald-700 p-4 font-bold">
                {{ session('success') }}
            </div>
        @endif

        <section class="grid lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="relative">
                    <i data-lucide="search" class="w-5 h-5 absolute left-4 top-3.5 text-slate-400"></i>
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Cari lembaga, guru, mapel, atau kelas..."
                        class="w-full pl-12 pr-4 py-3 rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <select wire:model.live="day"
                    class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Hari</option>
                    @foreach ($days as $item)
                        <option value="{{ $item }}">{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </section>

        <section class="grid gap-4 lg:hidden">
            @forelse ($schedules as $schedule)
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-black text-blue-600">
                                {{ $schedule->institution?->name ?? 'Belum ada lembaga' }}
                            </p>

                            <p class="text-sm text-slate-500 mt-1">
                                {{ $schedule->day }}
                            </p>

                            <h3 class="font-bold text-lg">
                                {{ $schedule->subject->name }}
                            </h3>

                            <p class="text-sm text-slate-500">
                                {{ $schedule->teacher->name }}
                            </p>
                        </div>

                        <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center">
                            <i data-lucide="calendar-days" class="w-6 h-6"></i>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="text-xs text-slate-500">Kelas</p>
                            <p class="font-semibold">{{ $schedule->class_name }}</p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="text-xs text-slate-500">Jam</p>
                            <p class="font-semibold">
                                {{ substr($schedule->start_time, 0, 5) }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="text-xs text-slate-500">JP</p>
                            <p class="font-semibold">{{ $schedule->hours_count }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex gap-2">
                        <button wire:click="edit({{ $schedule->id }})"
                            class="flex-1 px-4 py-2.5 rounded-2xl bg-blue-50 text-blue-700 font-bold">
                            Edit
                        </button>

                        <button wire:click="delete({{ $schedule->id }})"
                            wire:confirm="Yakin hapus jadwal ini?"
                            class="flex-1 px-4 py-2.5 rounded-2xl bg-red-50 text-red-600 font-bold">
                            Hapus
                        </button>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl p-8 text-center text-slate-500">
                    Belum ada jadwal mengajar.
                </div>
            @endforelse
        </section>

        <section class="hidden lg:block bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-slate-50 text-left text-sm text-slate-500">
                    <tr>
                        <th class="p-5">Lembaga</th>
                        <th class="p-5">Hari</th>
                        <th class="p-5">Guru</th>
                        <th class="p-5">Mata Pelajaran</th>
                        <th class="p-5">Kelas</th>
                        <th class="p-5">Jam</th>
                        <th class="p-5">JP</th>
                        <th class="p-5 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($schedules as $schedule)
                        <tr>
                            <td class="p-5">
                                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-black">
                                    {{ $schedule->institution?->name ?? '-' }}
                                </span>
                            </td>
                            <td class="p-5 font-bold">{{ $schedule->day }}</td>
                            <td class="p-5">{{ $schedule->teacher->name }}</td>
                            <td class="p-5">{{ $schedule->subject->name }}</td>
                            <td class="p-5">{{ $schedule->class_name }}</td>
                            <td class="p-5">
                                {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                            </td>
                            <td class="p-5 font-semibold">{{ $schedule->hours_count }}</td>
                            <td class="p-5">
                                <div class="flex justify-end gap-2">
                                    <button wire:click="edit({{ $schedule->id }})"
                                        class="p-2.5 rounded-xl bg-blue-50 text-blue-700">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>

                                    <button wire:click="delete({{ $schedule->id }})"
                                        wire:confirm="Yakin hapus jadwal ini?"
                                        class="p-2.5 rounded-xl bg-red-50 text-red-600">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500">
                                Belum ada jadwal mengajar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        {{ $schedules->links() }}
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-2xl bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-xl font-extrabold">
                            {{ $editingId ? 'Edit Jadwal' : 'Tambah Jadwal' }}
                        </h3>
                        <p class="text-sm text-slate-500">
                            Atur lembaga, guru, mata pelajaran, kelas, dan waktu mengajar.
                        </p>
                    </div>

                    <button wire:click="$set('showModal', false)" class="p-2 rounded-xl bg-slate-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="text-sm font-semibold">Lembaga</label>
                        <select wire:model="institution_id"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Lembaga</option>
                            @foreach ($institutions as $institution)
                                <option value="{{ $institution->id }}">
                                    {{ $institution->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('institution_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold">Guru</label>
                            <select wire:model="teacher_id"
                                class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Guru</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                            @error('teacher_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Mata Pelajaran</label>
                            <select wire:model="subject_id"
                                class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Mapel</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold">Hari</label>
                            <select wire:model="day"
                                class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Hari</option>
                                @foreach ($days as $item)
                                    <option value="{{ $item }}">{{ $item }}</option>
                                @endforeach
                            </select>
                            @error('day') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Kelas</label>
                            <input wire:model="class_name" type="text" placeholder="Contoh: X IPA 1 / XI SMK"
                                class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                            @error('class_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-semibold">Jam Mulai</label>
                            <input wire:model="start_time" type="time"
                                class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                            @error('start_time') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Jam Selesai</label>
                            <input wire:model="end_time" type="time"
                                class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                            @error('end_time') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Jumlah JP</label>
                            <input wire:model="hours_count" type="number" min="1"
                                class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                            @error('hours_count') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex gap-3 pt-3">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="flex-1 px-5 py-3 rounded-2xl bg-slate-100 font-bold">
                            Batal
                        </button>

                        <button type="submit"
                            class="flex-1 px-5 py-3 rounded-2xl bg-blue-600 text-white font-bold shadow">
                            Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif


    @if ($showBulkModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-6xl bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl p-6 max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-2xl font-black">
                        Jadwal Mengajar Massal
                    </h3>

                    <p class="text-sm text-slate-500">
                        Input banyak jadwal sekaligus untuk satu guru.
                    </p>
                </div>

                <button wire:click="$set('showBulkModal', false)"
                    class="px-4 py-2 rounded-xl bg-slate-100">
                    Tutup
                </button>
            </div>

            <div class="grid md:grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="text-sm font-bold">Guru</label>

                    <select wire:model="bulk_teacher_id"
                        class="mt-1 w-full rounded-2xl border-slate-200">
                        <option value="">Pilih Guru</option>

                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-bold">Hari</label>

                    <select wire:model="bulk_day"
                        class="mt-1 w-full rounded-2xl border-slate-200">
                        <option value="">Pilih Hari</option>

                        @foreach($days as $item)
                            <option value="{{ $item }}">
                                {{ $item }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="p-3 text-left">Lembaga</th>
                            <th class="p-3 text-left">Mapel</th>
                            <th class="p-3 text-left">Kelas</th>
                            <th class="p-3 text-left">Mulai</th>
                            <th class="p-3 text-left">Selesai</th>
                            <th class="p-3 text-left">JP</th>
                            <th class="p-3"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($scheduleRows as $index => $row)
                            <tr>
                                <td class="p-2">
                                    <select wire:model="scheduleRows.{{ $index }}.institution_id"
                                        class="w-full rounded-xl border-slate-200">

                                        <option value="">Lembaga</option>

                                        @foreach($institutions as $institution)
                                            <option value="{{ $institution->id }}">
                                                {{ $institution->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="p-2">
                                    <select wire:model="scheduleRows.{{ $index }}.subject_id"
                                        class="w-full rounded-xl border-slate-200">

                                        <option value="">Mapel</option>

                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}">
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="p-2">
                                    <input
                                        wire:model="scheduleRows.{{ $index }}.class_name"
                                        class="w-full rounded-xl border-slate-200">
                                </td>

                                <td class="p-2">
                                    <input
                                        type="time"
                                        wire:model="scheduleRows.{{ $index }}.start_time"
                                        class="w-full rounded-xl border-slate-200">
                                </td>

                                <td class="p-2">
                                    <input
                                        type="time"
                                        wire:model="scheduleRows.{{ $index }}.end_time"
                                        class="w-full rounded-xl border-slate-200">
                                </td>

                                <td class="p-2">
                                    <input
                                        type="number"
                                        min="1"
                                        wire:model="scheduleRows.{{ $index }}.hours_count"
                                        class="w-full rounded-xl border-slate-200">
                                </td>

                                <td class="p-2">
                                    <button
                                        type="button"
                                        wire:click="removeRow({{ $index }})"
                                        class="px-3 py-2 rounded-xl bg-red-50 text-red-600">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between mt-6">
                <button
                    type="button"
                    wire:click="addRow"
                    class="px-5 py-3 rounded-2xl bg-slate-100 font-bold">
                    + Tambah Baris
                </button>

                <button
                    type="button"
                    wire:click="saveBulk"
                    class="px-6 py-3 rounded-2xl bg-blue-600 text-white font-bold">
                    Simpan Semua Jadwal
                </button>
            </div>
        </div>
    </div>
@endif
</div>
