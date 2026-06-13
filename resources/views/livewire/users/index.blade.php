<div>
    <x-slot name="header">
        Users
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-blue-100 text-sm">Manajemen Akun</p>
                    <h1 class="text-2xl lg:text-3xl font-extrabold">Users Login</h1>
                    <p class="text-blue-50 mt-2">
                        Tambah akun admin dan akun guru yang terhubung ke data guru.
                    </p>
                </div>

                <button wire:click="create"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-white text-blue-700 font-black shadow">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    Tambah User
                </button>
            </div>
        </section>

        @if (session('error'))
            <div class="rounded-2xl bg-red-100 text-red-700 p-4 font-bold">
                {{ session('error') }}
            </div>
        @endif

        <section class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
            <div class="relative">
                <i data-lucide="search" class="w-5 h-5 absolute left-4 top-3.5 text-slate-400"></i>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Cari nama, email, atau role..."
                    class="w-full pl-12 pr-4 py-3 rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
            </div>
        </section>

        <section class="grid gap-4 lg:hidden">
            @forelse ($users as $user)
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center">
                                <i data-lucide="user-round" class="w-6 h-6"></i>
                            </div>

                            <div>
                                <h3 class="font-black text-slate-900">{{ $user->name }}</h3>
                                <p class="text-sm text-slate-500">{{ $user->email }}</p>
                            </div>
                        </div>

                        @if ($user->role === 'guru')
                            <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-700">
                                Guru
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-black bg-blue-100 text-blue-700">
                                Admin
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs text-slate-500">Terhubung Guru</p>
                        <p class="font-bold">{{ $user->teacher?->name ?? '-' }}</p>
                    </div>

                    <div class="mt-4 flex gap-2">
                        <button wire:click="edit({{ $user->id }})"
                            class="flex-1 px-4 py-2.5 rounded-2xl bg-blue-50 text-blue-700 font-black">
                            Edit
                        </button>

                        <button wire:click="delete({{ $user->id }})"
                            wire:confirm="Yakin hapus user ini?"
                            class="flex-1 px-4 py-2.5 rounded-2xl bg-red-50 text-red-600 font-black">
                            Hapus
                        </button>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl p-10 text-center text-slate-500">
                    Belum ada user.
                </div>
            @endforelse
        </section>

        <section class="hidden lg:block overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <table class="w-full">
                <thead class="bg-slate-50 text-left text-xs font-black uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-6 py-5">User</th>
                        <th class="px-6 py-5">Email</th>
                        <th class="px-6 py-5">Role</th>
                        <th class="px-6 py-5">Guru Terkait</th>
                        <th class="px-6 py-5 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-blue-50/40 transition">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-600 to-sky-400 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                                        <i data-lucide="user-round" class="w-6 h-6"></i>
                                    </div>
                                    <div>
                                        <p class="font-black text-slate-900">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-400">ID #{{ $user->id }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-5 font-bold text-slate-600">
                                {{ $user->email }}
                            </td>

                            <td class="px-6 py-5">
                                @if ($user->role === 'guru')
                                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-black text-emerald-700">
                                        <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                                        Guru
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1.5 text-xs font-black text-blue-700">
                                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                                        Admin
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-5 font-bold text-slate-600">
                                {{ $user->teacher?->name ?? '-' }}
                            </td>

                            <td class="px-6 py-5">
                                <div class="flex justify-end gap-2">
                                    <button wire:click="edit({{ $user->id }})"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-blue-500/20">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                        Edit
                                    </button>

                                    <button wire:click="delete({{ $user->id }})"
                                        wire:confirm="Yakin hapus user ini?"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-red-50 px-4 py-2.5 text-sm font-black text-red-600">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-slate-500">
                                Belum ada user.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        {{ $users->links() }}
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-xl bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-xl font-black">
                            {{ $editingId ? 'Edit User' : 'Tambah User' }}
                        </h3>
                        <p class="text-sm text-slate-500">Kelola akun login sistem.</p>
                    </div>

                    <button wire:click="$set('showModal', false)" class="p-2 rounded-xl bg-slate-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="text-sm font-bold">Nama</label>
                        <input wire:model="name" type="text"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold">Email</label>
                        <input wire:model="email" type="email"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold">
                            Password {{ $editingId ? '(kosongkan jika tidak diganti)' : '' }}
                        </label>
                        <input wire:model="password" type="password"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold">Role</label>
                        <select wire:model.live="role"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                            <option value="admin">Admin</option>
                            <option value="guru">Guru</option>
                        </select>
                    </div>

                    @if ($role === 'guru')
                        <div>
                            <label class="text-sm font-bold">Hubungkan ke Data Guru</label>
                            <select wire:model="teacher_id"
                                class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Guru</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                            @error('teacher_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="flex gap-3 pt-3">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="flex-1 px-5 py-3 rounded-2xl bg-slate-100 font-black">
                            Batal
                        </button>

                        <button type="submit"
                            class="flex-1 px-5 py-3 rounded-2xl bg-blue-600 text-white font-black shadow">
                            Simpan User
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