<div>
    <x-slot name="header">
        Jadwal Petugas Dhuha
    </x-slot>

    <div class="space-y-5">
        <section class="rounded-[2rem] bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-blue-100 text-sm font-bold">Master Kegiatan</p>
                    <h1 class="text-2xl font-black">Jadwal Petugas Dhuha</h1>
                    <p class="text-blue-50 mt-1 text-sm">
                        Tentukan guru yang bertugas membuat laporan sholat dhuha.
                    </p>
                </div>

                <button wire:click="create"
                    class="px-5 py-3 rounded-2xl bg-white text-blue-700 font-black shadow">
                    + Tambah Jadwal
                </button>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl bg-emerald-100 text-emerald-700 p-4 font-bold">
                {{ session('success') }}
            </div>
        @endif

        <section class="grid gap-4">
            @forelse ($schedules as $schedule)
                <div class="bg-white rounded-[2rem] p-5 shadow-sm border border-slate-100">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-black text-blue-600 uppercase">
                                {{ $schedule->day }}
                            </p>

                            <h3 class="text-lg font-black text-slate-900 mt-1">
                                {{ $schedule->teacher?->name }}
                            </h3>

                            <p class="text-sm text-slate-500 mt-1">
                                {{ $schedule->institution?->name ?? 'Semua Lembaga' }}
                            </p>

                            <span class="inline-flex mt-3 px-3 py-1 rounded-full text-xs font-black
                                {{ $schedule->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $schedule->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-2">
                            <button wire:click="edit({{ $schedule->id }})"
                                class="px-4 py-2 rounded-xl bg-blue-50 text-blue-700 font-black">
                                Edit
                            </button>

                            <button wire:click="delete({{ $schedule->id }})"
                                wire:confirm="Yakin hapus jadwal dhuha ini?"
                                class="px-4 py-2 rounded-xl bg-red-50 text-red-600 font-black">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-[2rem] p-10 text-center text-slate-500">
                    Belum ada jadwal petugas dhuha.
                </div>
            @endforelse

            {{ $schedules->links() }}
        </section>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-lg bg-white rounded-t-[2rem] sm:rounded-[2rem] shadow-2xl p-6">
                <h3 class="text-xl font-black mb-1">
                    {{ $editingId ? 'Edit Jadwal Dhuha' : 'Tambah Jadwal Dhuha' }}
                </h3>

                <p class="text-sm text-slate-500 mb-5">
                    Pilih guru yang bertugas mengisi laporan dhuha.
                </p>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="text-sm font-bold">Guru Petugas</label>
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
                        <label class="text-sm font-bold">Lembaga</label>
                        <select wire:model="institution_id"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua Lembaga</option>
                            @foreach ($institutions as $institution)
                                <option value="{{ $institution->id }}">{{ $institution->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-bold">Hari</label>
                        <select wire:model="day"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Hari</option>
                            @foreach ($days as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>
                        @error('day') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
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
                            class="flex-1 px-5 py-3 rounded-2xl bg-blue-600 text-white font-black shadow">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
