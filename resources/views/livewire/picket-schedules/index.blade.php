<div>
    <x-slot name="header">
        Jadwal Piket Guru
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <p class="text-blue-100 text-sm">Manajemen Piket</p>
                    <h1 class="text-2xl lg:text-3xl font-extrabold">Jadwal Piket Guru</h1>
                    <p class="text-blue-50 mt-2">Hanya guru yang ditandai sebagai petugas piket yang bisa dipilih.</p>
                </div>

                <button wire:click="create"
                    class="px-5 py-3 rounded-2xl bg-white text-blue-700 font-black shadow">
                    + Tambah Jadwal
                </button>
            </div>
        </section>

        <section class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Cari guru piket..."
                class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </section>

        <section class="grid gap-4">
            @forelse ($schedules as $schedule)
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-slate-500">{{ $schedule->day }}</p>
                        <h3 class="text-lg font-black text-slate-900">{{ $schedule->teacher->name }}</h3>
                        <p class="text-sm text-slate-500">
                            {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                        </p>
                        <p class="text-xs font-black text-blue-600">
                            {{ $schedule->institution?->name }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($schedule->is_active)
                            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-black">Aktif</span>
                        @else
                            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-xs font-black">Nonaktif</span>
                        @endif

                        <button wire:click="edit({{ $schedule->id }})"
                            class="px-4 py-2 rounded-2xl bg-blue-50 text-blue-700 font-black">
                            Edit
                        </button>

                        <button wire:click="delete({{ $schedule->id }})"
                            wire:confirm="Yakin hapus jadwal piket ini?"
                            class="px-4 py-2 rounded-2xl bg-red-50 text-red-600 font-black">
                            Hapus
                        </button>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl p-10 text-center text-slate-500">
                    Belum ada jadwal piket.
                </div>
            @endforelse
        </section>

        {{ $schedules->links() }}
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-lg bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl p-6">
                <h3 class="text-xl font-black mb-5">
                    {{ $editingId ? 'Edit Jadwal Piket' : 'Tambah Jadwal Piket' }}
                </h3>

                <form wire:submit="save" class="space-y-4">
                    <select wire:model="teacher_id"
                        class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih Guru Piket</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>

                    <div>
                        <label class="text-sm font-bold">
                            Lembaga
                        </label>

                        <select wire:model="institution_id"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">

                            <option value="">
                                Pilih Lembaga
                            </option>

                            @foreach($institutions as $institution)
                                <option value="{{ $institution->id }}">
                                    {{ $institution->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <select wire:model="day"
                        class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih Hari</option>
                        @foreach ($days as $item)
                            <option value="{{ $item }}">{{ $item }}</option>
                        @endforeach
                    </select>

                    <div class="grid grid-cols-2 gap-3">
                        <input wire:model="start_time" type="time"
                            class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">

                        <input wire:model="end_time" type="time"
                            class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <label class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4">
                        <input wire:model="is_active" type="checkbox"
                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="font-bold">Jadwal aktif</span>
                    </label>

                    <div class="flex gap-3 pt-3">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="flex-1 px-5 py-3 rounded-2xl bg-slate-100 font-black">
                            Batal
                        </button>

                        <button type="submit"
                            class="flex-1 px-5 py-3 rounded-2xl bg-blue-600 text-white font-black">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
