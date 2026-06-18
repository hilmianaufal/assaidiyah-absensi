<div>
    <x-slot name="header">
        Pengaturan Transport
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <p class="text-blue-100 text-sm">Keuangan Guru</p>
            <h1 class="text-2xl lg:text-3xl font-extrabold">Pengaturan Transport</h1>
            <p class="text-blue-50 mt-2">
                Atur jam absen masuk, jam absen pulang, dan nominal transport guru.
            </p>
        </section>

        @if (session('success'))
            <div class="rounded-2xl bg-emerald-100 text-emerald-700 p-4 font-bold">
                {{ session('success') }}
            </div>
        @endif

        <section class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
            <form wire:submit="save" class="space-y-5">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-bold">Jam Masuk Mulai</label>
                        <input wire:model="check_in_start" type="time"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        @error('check_in_start') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold">Jam Masuk Selesai</label>
                        <input wire:model="check_in_end" type="time"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        @error('check_in_end') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold">Jam Pulang Mulai</label>
                        <input wire:model="check_out_start" type="time"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        @error('check_out_start') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold">Jam Pulang Selesai</label>
                        <input wire:model="check_out_end" type="time"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        @error('check_out_end') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="text-sm font-bold">Nominal Transport</label>
                    <input wire:model="amount" type="number"
                        class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    @error('amount') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4">
                    <input wire:model="is_active" type="checkbox"
                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="font-bold">Transport aktif</span>
                </label>

                <button type="submit"
                    class="w-full px-5 py-3 rounded-2xl bg-blue-600 text-white font-black shadow">
                    Simpan Pengaturan
                </button>
            </form>
        </section>
    </div>
</div>