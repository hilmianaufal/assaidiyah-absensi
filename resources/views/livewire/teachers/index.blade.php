<div class="space-y-6">

    <div class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-500 p-6 text-white shadow-xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-black">
                    Data Guru
                </h1>
                <p class="mt-2 text-blue-100">
                    Kelola data guru Assaidiyyah
                </p>
            </div>

            <button
                type="button"
                wire:click="create"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 font-black text-blue-700 shadow-lg transition hover:bg-blue-50"
            >
                <i data-lucide="user-plus" class="h-5 w-5"></i>
                Tambah Guru
            </button>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
        <div class="relative">
            <i data-lucide="search" class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"></i>

            <input
                wire:model.live="search"
                type="text"
                placeholder="Cari guru..."
                class="w-full rounded-2xl border-slate-200 py-3 pl-12 pr-4 text-sm font-semibold focus:border-blue-500 focus:ring-blue-500"
            >
        </div>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[950px]">
                <thead>
                    <tr class="bg-slate-50 text-left text-xs font-black uppercase tracking-wider text-slate-400">
                        <th class="px-6 py-5">Guru</th>
                        <th class="px-6 py-5">NIP</th>
                        <th class="px-6 py-5">Kontak</th>
                        <th class="px-6 py-5">Honor</th>
                        <th class="px-6 py-5">Lembaga</th>
                        <th class="px-6 py-5">Piket</th>
                        <th class="px-6 py-5">Status</th>
                        <th class="px-6 py-5 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($teachers as $teacher)
                        <tr wire:key="teacher-row-{{ $teacher->id }}" class="transition hover:bg-blue-50/40">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-sky-400 text-white shadow-lg shadow-blue-500/20">
                                        <i data-lucide="user-round" class="h-6 w-6"></i>
                                    </div>

                                    <div>
                                        <p class="font-black text-slate-900">
                                            {{ $teacher->name }}
                                        </p>
                                        <p class="text-xs font-semibold text-slate-400">
                                            ID Guru #{{ $teacher->id }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <span class="inline-flex items-center gap-2 rounded-2xl bg-slate-50 px-3 py-2 text-sm font-bold text-slate-600">
                                    <i data-lucide="badge" class="h-4 w-4 text-slate-400"></i>
                                    {{ $teacher->nip ?: '-' }}
                                </span>
                            </td>

                            <td class="px-6 py-5">
                                <div class="flex items-center gap-2 text-sm font-bold text-slate-600">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                        <i data-lucide="phone" class="h-4 w-4"></i>
                                    </span>
                                    {{ $teacher->phone ?: '-' }}
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                                        <i data-lucide="wallet" class="h-4 w-4"></i>
                                    </span>

                                    <div>
                                        <p class="font-black text-slate-900">
                                            Rp{{ number_format((int) $teacher->hourly_rate, 0, ',', '.') }}
                                        </p>
                                        <p class="text-xs text-slate-400">
                                            per JP
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="flex flex-wrap gap-2">
                                    @forelse ($teacher->institutions as $institution)
                                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-black text-blue-700">
                                            {{ $institution->name }}
                                        </span>
                                    @empty
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500">
                                            Belum ada
                                        </span>
                                    @endforelse
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                @if ($teacher->is_picket_officer)
                                    <span class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1.5 text-xs font-black text-blue-700">
                                        <i data-lucide="shield-check" class="h-4 w-4"></i>
                                        Petugas Piket
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-500">
                                        <i data-lucide="minus-circle" class="h-4 w-4"></i>
                                        Bukan Piket
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-5">
                                @if ($teacher->is_active)
                                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-black text-emerald-700">
                                        <i data-lucide="check-circle-2" class="h-4 w-4"></i>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-2 rounded-full bg-red-100 px-3 py-1.5 text-xs font-black text-red-700">
                                        <i data-lucide="x-circle" class="h-4 w-4"></i>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-5">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        wire:click="edit({{ $teacher->id }})"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-700"
                                    >
                                        <i data-lucide="pencil" class="h-4 w-4"></i>
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="delete({{ $teacher->id }})"
                                        wire:confirm="Yakin hapus guru ini?"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-red-50 px-4 py-2.5 text-sm font-black text-red-600 transition hover:bg-red-100"
                                    >
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-blue-50 text-blue-600">
                                    <i data-lucide="users" class="h-10 w-10"></i>
                                </div>

                                <h3 class="mt-4 text-lg font-black text-slate-900">
                                    Belum ada data guru
                                </h3>

                                <p class="mt-1 text-sm text-slate-500">
                                    Tambahkan data guru untuk mulai menggunakan sistem absensi.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $teachers->links() }}
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/50 p-4 backdrop-blur-sm sm:items-center">
            <div class="w-full max-w-xl rounded-t-3xl bg-white p-6 shadow-2xl sm:rounded-3xl">

                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-black text-slate-900">
                            {{ $editingId ? 'Edit Guru' : 'Tambah Guru' }}
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Lengkapi data guru dengan benar.
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="closeModal"
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 hover:bg-slate-200"
                    >
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <input wire:model="name" type="text" placeholder="Nama Guru"
                        class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    @error('name') <p class="text-xs font-bold text-red-500">{{ $message }}</p> @enderror

                    <input wire:model="nip" type="text" placeholder="NIP"
                        class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">

                    <input wire:model="phone" type="text" placeholder="No HP"
                        class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">

                    <input wire:model="hourly_rate" type="number" placeholder="Honor per JP"
                        class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    @error('hourly_rate') <p class="text-xs font-bold text-red-500">{{ $message }}</p> @enderror

                    <div class="rounded-2xl border border-slate-200 p-4">
                        <p class="mb-3 font-black text-slate-900">
                            Lembaga Mengajar
                        </p>

                        <div class="max-h-44 space-y-2 overflow-y-auto">
                            @forelse ($institutions as $institution)
                                <label wire:key="institution-{{ $institution->id }}" class="flex cursor-pointer items-center gap-3 rounded-xl p-2 hover:bg-slate-50">
                                    <input
                                        type="checkbox"
                                        wire:model="institution_ids"
                                        value="{{ $institution->id }}"
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                    >

                                    <span class="text-sm font-bold text-slate-700">
                                        {{ $institution->name }}
                                    </span>
                                </label>
                            @empty
                                <p class="text-sm text-slate-400">
                                    Belum ada data lembaga.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <label class="flex cursor-pointer items-center gap-3 rounded-2xl bg-slate-50 p-4">
                        <input wire:model="is_active" type="checkbox"
                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="font-bold text-slate-700">
                            Guru aktif
                        </span>
                    </label>

                    <label class="flex cursor-pointer items-center gap-3 rounded-2xl bg-blue-50 p-4">
                        <input wire:model="is_picket_officer" type="checkbox"
                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                        <div>
                            <span class="font-black text-blue-700">
                                Bisa Jadi Guru Piket
                            </span>
                            <p class="text-xs text-slate-500">
                                Guru ini boleh dijadwalkan sebagai petugas piket.
                            </p>
                        </div>
                    </label>

                    <div class="flex gap-3 pt-3">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="flex-1 rounded-2xl bg-slate-100 px-5 py-3 font-black text-slate-600 hover:bg-slate-200"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="flex-1 rounded-2xl bg-blue-600 px-5 py-3 font-black text-white shadow-lg shadow-blue-500/20 hover:bg-blue-700"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
