<div>
    <x-slot name="header">
        Laporan Piket
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <p class="text-blue-100 text-sm">Guru Piket</p>
            <h1 class="text-2xl lg:text-3xl font-extrabold">Laporan Piket Harian</h1>
            <p class="text-blue-50 mt-2">
                Isi siswa yang tidak masuk dan preview format WhatsApp secara realtime.
            </p>
        </section>

        @if (! $isAllowed)
            <div class="rounded-3xl bg-red-50 border border-red-100 p-6 text-red-700">
                <h3 class="text-xl font-black">Akses Ditolak</h3>
                <p class="mt-2 text-sm">
                    Anda bukan petugas piket atau tidak sedang memiliki jadwal piket aktif hari ini.
                </p>
            </div>
        @else
            @if (session('success'))
                <div class="rounded-2xl bg-emerald-100 text-emerald-700 p-4 font-bold">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="rounded-2xl bg-red-100 text-red-700 p-4 font-bold">
                    {{ session('error') }}
                </div>
            @endif

            <section class="grid lg:grid-cols-2 gap-6">
                <div class="space-y-5">
                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                        <p class="text-sm text-slate-500">Guru Piket</p>
                        <h2 class="text-xl font-black text-slate-900">
                            {{ $teacher?->name }}
                        </h2>
                        <p class="text-sm text-slate-500">
                            {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                        </p>
                    </div>

                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                        <label class="text-sm font-bold text-slate-700">
                            Guru yang berhalangan hadir
                        </label>

                        <textarea wire:model.live.debounce.300ms="teacher_absences"
                            rows="4"
                            placeholder="Contoh:&#10;1. Ust Ahmad (izin)&#10;2. Usth Fatimah (sakit)&#10;Jika tidak ada, kosongkan saja."
                            class="mt-2 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-11 h-11 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center">
                                <i data-lucide="user-x" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-slate-900">Tambah Siswa Tidak Masuk</h3>
                                <p class="text-sm text-slate-500">Input manual nama siswa dan statusnya.</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <select wire:model="class_name"
                                class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Kelas</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class }}">{{ $class }}</option>
                                @endforeach
                            </select>

                            <input wire:model="student_name" type="text"
                                placeholder="Nama siswa"
                                class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">

                            <select wire:model="status"
                                class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                                <option value="alpa">Alpa</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="pulang">Pulang</option>
                            </select>

                            <button wire:click="addStudent" type="button"
                                class="w-full px-5 py-3 rounded-2xl bg-blue-600 text-white font-black shadow">
                                + Tambah ke Laporan
                            </button>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                        <h3 class="font-black text-slate-900 mb-4">Daftar Siswa Tidak Masuk</h3>

                        <div class="space-y-3">
                            @forelse ($students as $index => $student)
                                <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 p-4">
                                    <div>
                                        <p class="font-black text-slate-900">
                                            {{ $student['student_name'] }}
                                        </p>
                                        <p class="text-sm text-slate-500">
                                            {{ $student['class_name'] }} - {{ $student['status'] }}
                                        </p>
                                    </div>

                                    <button wire:click="removeStudent({{ $index }})" type="button"
                                        class="px-3 py-2 rounded-xl bg-red-50 text-red-600 font-black">
                                        Hapus
                                    </button>
                                </div>
                            @empty
                                <div class="rounded-2xl bg-slate-50 p-5 text-center text-slate-500">
                                    Belum ada siswa yang ditambahkan.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="sticky top-24 bg-slate-950 rounded-3xl p-5 shadow-xl text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-blue-200 text-sm">Preview WhatsApp</p>
                                <h3 class="text-xl font-black">Pesan Grup Sekolah</h3>
                            </div>

                            <div class="w-11 h-11 rounded-2xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center">
                                <i data-lucide="message-circle" class="w-5 h-5"></i>
                            </div>
                        </div>

                        <pre class="whitespace-pre-wrap text-sm leading-7 text-slate-100 font-sans bg-white/5 rounded-2xl p-4 max-h-[520px] overflow-y-auto">{{ $previewMessage }}</pre>

                        <button wire:click="save" type="button"
                            class="mt-5 w-full px-5 py-3 rounded-2xl bg-emerald-500 text-white font-black shadow">
                            Simpan Laporan
                        </button>

                        <button wire:click="sendWhatsappGroup" type="button"
                            class="mt-3 w-full px-5 py-3 rounded-2xl bg-emerald-500 text-white font-black">
                            Kirim WhatsApp Group
                        </button>

                        <p class="mt-3 text-xs text-slate-400 text-center">
                            Tombol WhatsApp akan kita aktifkan setelah service WA dibuat.
                        </p>
                    </div>
                </div>
            </section>
        @endif
    </div>

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</div>
