<div>
    <x-slot name="header">
        Tambahan Honor
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-blue-100 text-sm">Honor Tambahan</p>
                    <h1 class="text-2xl lg:text-3xl font-extrabold">Tambahan Honor Guru</h1>
                    <p class="text-blue-50 mt-2">
                        Input honor selain mengajar, seperti pembina pramuka, wali kelas, OSIS, dan tugas tambahan lainnya.
                    </p>
                </div>

                <button wire:click="create"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-white text-blue-700 font-black shadow">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    Tambah Honor
                </button>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl bg-emerald-100 text-emerald-700 p-4 font-bold">
                {{ session('success') }}
            </div>
        @endif

        <section class="grid lg:grid-cols-4 gap-4">
            <div class="lg:col-span-2 bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="relative">
                    <i data-lucide="search" class="w-5 h-5 absolute left-4 top-3.5 text-slate-400"></i>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Cari guru atau jenis honor..."
                        class="w-full pl-12 pr-4 py-3 rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <select wire:model.live="month"
                    class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}">
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <select wire:model.live="year"
                    class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    @foreach (range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </section>

        <section class="grid gap-4">
            @forelse ($additionalHonors as $honor)
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center">
                                <i data-lucide="badge-dollar-sign" class="w-6 h-6"></i>
                            </div>

                            <div>
                                <p class="text-sm text-slate-500">
                                    {{ $honor->teacher->name }}
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

                        <div class="flex items-center justify-between lg:justify-end gap-3">
                            <div class="text-right">
                                <p class="text-xs text-slate-500">Nominal</p>
                                <p class="text-xl font-black text-blue-700">
                                    Rp{{ number_format($honor->amount, 0, ',', '.') }}
                                </p>
                            </div>

                            <button wire:click="edit({{ $honor->id }})"
                                class="px-4 py-2.5 rounded-2xl bg-blue-50 text-blue-700 font-black">
                                Edit
                            </button>

                            <button wire:click="delete({{ $honor->id }})"
                                wire:confirm="Yakin hapus tambahan honor ini?"
                                class="px-4 py-2.5 rounded-2xl bg-red-50 text-red-600 font-black">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl p-10 text-center text-slate-500">
                    Belum ada tambahan honor pada periode ini.
                </div>
            @endforelse
        </section>

        {{ $additionalHonors->links() }}
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-lg bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl p-6">
                <h3 class="text-xl font-black mb-1">
                    {{ $editingId ? 'Edit Tambahan Honor' : 'Tambah Honor Guru' }}
                </h3>

                <p class="text-sm text-slate-500 mb-5">
                    Tambahkan honor tugas tambahan guru.
                </p>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="text-sm font-bold">Guru</label>
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
                        <label class="text-sm font-bold">Jenis Honor</label>
                        <input wire:model="title" type="text"
                            placeholder="Contoh: Pembina Pramuka"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        @error('title') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm font-bold">Bulan</label>
                            <select wire:model="month"
                                class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}">
                                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-bold">Tahun</label>
                            <input wire:model="year" type="number"
                                class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-bold">Nominal</label>
                        <input wire:model="amount" type="number"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        @error('amount') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold">Keterangan</label>
                        <textarea wire:model="note" rows="3"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

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

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</div>
