<div>
    <x-slot name="header">
        Tambahan Honor
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm text-blue-100">Honor Tambahan Multi Lembaga</p>
                    <h1 class="text-2xl font-extrabold lg:text-3xl">Tambahan Honor Guru</h1>
                    <p class="mt-2 text-blue-50">
                        Input honor selain mengajar berdasarkan lembaga, seperti pembina pramuka, wali kelas, OSIS, dan tugas tambahan lainnya.
                    </p>
                </div>

                <button
                    type="button"
                    wire:click="create"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 font-black text-blue-700 shadow"
                >
                    <i data-lucide="plus-circle" class="h-5 w-5"></i>
                    Tambah Honor
                </button>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl bg-emerald-100 p-4 font-bold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <section class="grid gap-4 lg:grid-cols-4">
            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm lg:col-span-2">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-4 top-3.5 h-5 w-5 text-slate-400"></i>
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Cari guru, lembaga, atau jenis honor..."
                        class="w-full rounded-2xl border-slate-200 py-3 pl-12 pr-4 focus:border-blue-500 focus:ring-blue-500"
                    >
                </div>
            </div>

            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <select
                    wire:model.live="month"
                    class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                >
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}">
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <select
                    wire:model.live="year"
                    class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                >
                    @foreach (range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </section>

        <section class="grid gap-4">
            @forelse ($additionalHonors as $honor)
                <div
                    wire:key="additional-honor-{{ $honor->id }}"
                    class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm"
                >
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-700">
                                <i data-lucide="badge-dollar-sign" class="h-6 w-6"></i>
                            </div>

                            <div>
                                <p class="text-xs font-black text-blue-600">
                                    {{ $honor->institution?->name ?? '-' }}
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $honor->teacher?->name ?? '-' }}
                                </p>

                                <h3 class="text-lg font-black text-slate-900">
                                    {{ $honor->title }}
                                </h3>

                                <p class="text-sm text-slate-500">
                                    {{ \Carbon\Carbon::create()->month($honor->month)->translatedFormat('F') }}
                                    {{ $honor->year }}

                                    @if ($honor->note)
                                        • {{ $honor->note }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 lg:justify-end">
                            <div class="text-right">
                                <p class="text-xs text-slate-500">Nominal</p>
                                <p class="text-xl font-black text-blue-700">
                                    Rp{{ number_format($honor->amount, 0, ',', '.') }}
                                </p>
                            </div>

                            <button
                                type="button"
                                wire:click="edit({{ $honor->id }})"
                                class="rounded-2xl bg-blue-50 px-4 py-2.5 font-black text-blue-700"
                            >
                                Edit
                            </button>

                            <button
                                type="button"
                                wire:click="delete({{ $honor->id }})"
                                wire:confirm="Yakin hapus tambahan honor ini?"
                                class="rounded-2xl bg-red-50 px-4 py-2.5 font-black text-red-600"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-3xl bg-white p-10 text-center text-slate-500">
                    Belum ada tambahan honor pada periode ini.
                </div>
            @endforelse
        </section>

        {{ $additionalHonors->links() }}
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/50 p-4 backdrop-blur-sm sm:items-center">
            <div class="w-full max-w-lg rounded-t-3xl bg-white p-6 shadow-2xl sm:rounded-3xl">
                <h3 class="mb-1 text-xl font-black">
                    {{ $editingId ? 'Edit Tambahan Honor' : 'Tambah Honor Guru' }}
                </h3>

                <p class="mb-5 text-sm text-slate-500">
                    Tambahkan honor tugas tambahan guru berdasarkan lembaga.
                </p>

                <form wire:submit.prevent="save" class="space-y-4">
                    <div>
                        <label class="text-sm font-bold">Lembaga</label>

                        <select
                            wire:model.live="institution_id"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">Pilih Lembaga</option>

                            @foreach ($institutions as $institution)
                                <option value="{{ $institution->id }}">
                                    {{ $institution->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('institution_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold">Guru</label>

                        <select
                            wire:model.live="teacher_id"
                            wire:key="teacher-select-{{ $institution_id ?? 'empty' }}-{{ $teacher_id ?? 'none' }}"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                        >
                            @if (! $institution_id)
                                <option value="">Pilih lembaga dulu</option>
                            @else
                                <option value="">Pilih Guru</option>

                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>

                        @error('teacher_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold">Jenis Honor</label>

                        <input
                            wire:model.live="title"
                            type="text"
                            placeholder="Contoh: Pembina Pramuka"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm font-bold">Bulan</label>

                            <select
                                wire:model.live="month"
                                class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}">
                                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-bold">Tahun</label>

                            <input
                                wire:model.live="year"
                                type="number"
                                class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-bold">Nominal</label>

                        <input
                            wire:model.live="amount"
                            type="number"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold">Keterangan</label>

                        <textarea
                            wire:model.live="note"
                            rows="3"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                        ></textarea>
                    </div>

                    <div class="flex gap-3 pt-3">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="flex-1 rounded-2xl bg-slate-100 px-5 py-3 font-black"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="flex-1 rounded-2xl bg-blue-600 px-5 py-3 font-black text-white shadow disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="save">
                                Simpan
                            </span>

                            <span wire:loading wire:target="save">
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @script
        <script>
            $wire.on('refresh-lucide', () => {
                if (window.lucide) {
                    lucide.createIcons();
                }
            });

            if (window.lucide) {
                lucide.createIcons();
            }
        </script>
    @endscript
</div>
