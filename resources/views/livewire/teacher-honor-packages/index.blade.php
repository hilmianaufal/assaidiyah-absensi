<div>
    <x-slot name="header">
        Paket Honor Guru
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-blue-100 text-sm">Master Honor</p>
                    <h1 class="text-2xl lg:text-3xl font-extrabold">Paket Honor Guru</h1>
                    <p class="text-blue-50 mt-2">
                        Atur honor bulanan berdasarkan paket jam mengajar mingguan dan potongan ketidakhadiran.
                    </p>
                </div>

                <button wire:click="create"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-white text-blue-700 font-black shadow">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    Tambah Paket
                </button>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl bg-emerald-100 text-emerald-700 p-4 font-bold">
                {{ session('success') }}
            </div>
        @endif

        <section class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
            <div class="relative">
                <i data-lucide="search" class="w-5 h-5 absolute left-4 top-3.5 text-slate-400"></i>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Cari guru..."
                    class="w-full pl-12 pr-4 py-3 rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
            </div>
        </section>

        <section class="grid gap-4">
            @forelse ($packages as $package)
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center">
                                <i data-lucide="badge-dollar-sign" class="w-6 h-6"></i>
                            </div>

                            <div>
                                <p class="text-sm text-slate-500">Guru</p>
                                <h3 class="text-lg font-black text-slate-900">
                                    {{ $package->teacher->name }}
                                </h3>

                                <p class="text-sm text-slate-500 mt-1">
                                    {{ $package->weekly_hours }} JP/minggu ×
                                    Rp{{ number_format($package->package_rate, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs text-slate-500">Honor Bulanan</p>
                                <p class="font-black text-slate-900">
                                    Rp{{ number_format($package->monthly_honor, 0, ',', '.') }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-red-50 p-4">
                                <p class="text-xs text-red-500">Potongan / JP</p>
                                <p class="font-black text-red-700">
                                    Rp{{ number_format($package->deduction_per_hour, 0, ',', '.') }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs text-slate-500">Status</p>
                                <p class="font-black {{ $package->is_active ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $package->is_active ? 'Aktif' : 'Nonaktif' }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button wire:click="edit({{ $package->id }})"
                                    class="px-4 py-2.5 rounded-2xl bg-blue-50 text-blue-700 font-black">
                                    Edit
                                </button>

                                <button wire:click="delete({{ $package->id }})"
                                    wire:confirm="Yakin hapus paket honor ini?"
                                    class="px-4 py-2.5 rounded-2xl bg-red-50 text-red-600 font-black">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl p-10 text-center text-slate-500">
                    Belum ada paket honor guru.
                </div>
            @endforelse
        </section>

        {{ $packages->links() }}
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-xl bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl p-6">
                <h3 class="text-xl font-black mb-1">
                    {{ $editingId ? 'Edit Paket Honor' : 'Tambah Paket Honor' }}
                </h3>

                <p class="text-sm text-slate-500 mb-5">
                    Contoh: 9 JP/minggu × Rp35.000 = Rp315.000/bulan.
                </p>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="text-sm font-bold">Guru</label>
                        <select wire:model="teacherId"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Guru</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}">
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('teacherId')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm font-bold">JP / Minggu</label>
                            <input wire:model.live="weeklyHours" type="number"
                                class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                            @error('weeklyHours')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-bold">Tarif Paket / JP</label>
                            <input wire:model.live="packageRate" type="number"
                                class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                            @error('packageRate')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-blue-50 p-4">
                            <p class="text-xs text-blue-600 font-bold">Honor Bulanan</p>
                            <p class="text-xl font-black text-blue-700">
                                Rp{{ number_format($monthlyHonor, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-red-50 p-4">
                            <p class="text-xs text-red-600 font-bold">Potongan / JP</p>
                            <p class="text-xl font-black text-red-700">
                                Rp{{ number_format($deductionPerHour, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    <label class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4">
                        <input wire:model="isActive" type="checkbox"
                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="font-bold">Paket aktif</span>
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

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</div>
