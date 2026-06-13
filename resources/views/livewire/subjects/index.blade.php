<div>
    <x-slot name="header">
        Mata Pelajaran
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-blue-100 text-sm">Master Data</p>
                    <h1 class="text-2xl lg:text-3xl font-extrabold">Mata Pelajaran</h1>
                    <p class="text-blue-50 mt-2">Kelola daftar mata pelajaran untuk jadwal dan honor guru.</p>
                </div>

                <button wire:click="create"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-white text-blue-700 font-bold shadow">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    Tambah Mapel
                </button>
            </div>
        </section>

        <section class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
            <div class="relative">
                <i data-lucide="search" class="w-5 h-5 absolute left-4 top-3.5 text-slate-400"></i>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Cari mata pelajaran..."
                    class="w-full pl-12 pr-4 py-3 rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
            </div>
        </section>

        <section class="grid gap-4 lg:hidden">
            @forelse ($subjects as $subject)
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center">
                                <i data-lucide="book-open" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h3 class="font-bold">{{ $subject->name }}</h3>
                                <p class="text-sm text-slate-500">ID: {{ $subject->id }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex gap-2">
                        <button wire:click="edit({{ $subject->id }})"
                            class="flex-1 px-4 py-2.5 rounded-2xl bg-blue-50 text-blue-700 font-bold">
                            Edit
                        </button>

                        <button wire:click="delete({{ $subject->id }})"
                            wire:confirm="Yakin hapus mapel ini?"
                            class="flex-1 px-4 py-2.5 rounded-2xl bg-red-50 text-red-600 font-bold">
                            Hapus
                        </button>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl p-8 text-center text-slate-500">
                    Belum ada mata pelajaran.
                </div>
            @endforelse
        </section>

        <section class="hidden lg:block bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-slate-50 text-left text-sm text-slate-500">
                    <tr>
                        <th class="p-5">Mata Pelajaran</th>
                        <th class="p-5">Tanggal Dibuat</th>
                        <th class="p-5 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($subjects as $subject)
                        <tr>
                            <td class="p-5 font-bold">{{ $subject->name }}</td>
                            <td class="p-5 text-slate-500">{{ $subject->created_at->format('d M Y') }}</td>
                            <td class="p-5">
                                <div class="flex justify-end gap-2">
                                    <button wire:click="edit({{ $subject->id }})"
                                        class="p-2.5 rounded-xl bg-blue-50 text-blue-700">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>

                                    <button wire:click="delete({{ $subject->id }})"
                                        wire:confirm="Yakin hapus mapel ini?"
                                        class="p-2.5 rounded-xl bg-red-50 text-red-600">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-8 text-center text-slate-500">
                                Belum ada mata pelajaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        {{ $subjects->links() }}
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-lg bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-xl font-extrabold">
                            {{ $editingId ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran' }}
                        </h3>
                        <p class="text-sm text-slate-500">Masukkan nama mata pelajaran.</p>
                    </div>

                    <button wire:click="$set('showModal', false)" class="p-2 rounded-xl bg-slate-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="text-sm font-semibold">Nama Mata Pelajaran</label>
                        <input wire:model="name" type="text"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3 pt-3">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="flex-1 px-5 py-3 rounded-2xl bg-slate-100 font-bold">
                            Batal
                        </button>

                        <button type="submit"
                            class="flex-1 px-5 py-3 rounded-2xl bg-blue-600 text-white font-bold shadow">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>