<div>
    <x-slot name="header">
        Absensi Harian
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-blue-100 text-sm">Transport Pagi</p>
                    <h1 class="text-2xl lg:text-3xl font-extrabold">Absensi Harian Guru</h1>
                    <p class="text-blue-50 mt-2">Jam 06:45 - 07:15 mendapat transport Rp10.000.</p>
                </div>
                <a href="{{ route('daily-attendances.pdf', $date) }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-red-500 text-white font-bold shadow">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    Cetak PDF
                </a>
                <button wire:click="exportExcel"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-emerald-500 text-white font-bold shadow">
                    <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                    Export Excel
                </button>
                <button wire:click="openAttendance"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-white text-blue-700 font-bold shadow">
                    <i data-lucide="scan-face" class="w-5 h-5"></i>
                    Absen Sekarang
                </button>
            </div>
        </section>

        <section class="grid lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="relative">
                    <i data-lucide="search" class="w-5 h-5 absolute left-4 top-3.5 text-slate-400"></i>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Cari nama guru..."
                        class="w-full pl-12 pr-4 py-3 rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <input wire:model.live="date" type="date"
                    class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
            </div>
        </section>

        <section class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-slate-50 text-left text-sm text-slate-500">
                    <tr>
                        <th class="p-5">Guru</th>
                        <th class="p-5">Tanggal</th>
                        <th class="p-5">Jam</th>
                        <th class="p-5">Transport</th>
                        <th class="p-5">Keterangan</th>
                        <th class="p-5">Foto Bukti</th>
                        <th class="p-5 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($attendances as $attendance)
                        <tr>
                            <td class="p-5 font-bold">{{ $attendance->teacher->name }}</td>
                            <td class="p-5">{{ $attendance->attendance_date }}</td>
                            <td class="p-5">
                                {{ $attendance->attendance_time ? substr($attendance->attendance_time, 0, 5) : '-' }}
                            </td>
                            <td class="p-5 font-bold">
                                Rp{{ number_format($attendance->transport_amount, 0, ',', '.') }}
                            </td>
                            <td class="p-5 text-slate-500">{{ $attendance->note }}</td>
                            <td class="p-5">
                                <button
                                    type="button"
                                    onclick="showAttendancePhotos(
                                        '{{ $attendance->check_in_photo ? asset('storage/' . $attendance->check_in_photo) : '' }}',
                                        '{{ $attendance->check_out_photo ? asset('storage/' . $attendance->check_out_photo) : '' }}',
                                        '{{ $attendance->teacher->name }}'
                                    )"
                                    class="px-4 py-2 rounded-xl bg-blue-50 text-blue-700 font-bold">
                                    Lihat Foto
                                </button>
                            </td>
                            <td class="p-5 text-right">
                                <button wire:click="delete({{ $attendance->id }})"
                                    wire:confirm="Yakin hapus absensi ini?"
                                    class="p-2.5 rounded-xl bg-red-50 text-red-600">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500">
                                Belum ada absensi hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        {{ $attendances->links() }}
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-lg bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl p-6">
                <h3 class="text-xl font-extrabold mb-2">Absensi Guru</h3>
                <p class="text-sm text-slate-500 mb-5">Sementara pilih guru manual. Nanti bagian ini kita hubungkan ke kamera wajah.</p>

                <select wire:model="teacher_id"
                    class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Pilih Guru</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                    @endforeach
                </select>

                @error('teacher_id')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror

                <div class="flex gap-3 pt-5">
                    <button wire:click="$set('showModal', false)"
                        class="flex-1 px-5 py-3 rounded-2xl bg-slate-100 font-bold">
                        Batal
                    </button>

                    <button wire:click="saveAttendance"
                        class="flex-1 px-5 py-3 rounded-2xl bg-blue-600 text-white font-bold shadow">
                        Simpan Absen
                    </button>
                </div>
            </div>
        </div>
    @endif
    <div id="photoModal"
    class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4">
    <div class="w-full max-w-4xl rounded-[2rem] bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between mb-5">
            <div>
                <p class="text-sm text-slate-500">Bukti Absensi</p>
                <h2 id="photoTeacherName" class="text-2xl font-extrabold text-slate-900">
                    Nama Guru
                </h2>
            </div>

            <button type="button" onclick="hideAttendancePhotos()"
                class="w-10 h-10 rounded-2xl bg-slate-100 flex items-center justify-center">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="grid md:grid-cols-2 gap-5">
            <div class="rounded-3xl bg-slate-50 p-4">
                <p class="font-bold text-slate-700 mb-3">Foto Masuk</p>
                <img id="checkInPhoto"
                    src=""
                    class="w-full aspect-video object-cover rounded-2xl bg-slate-200">
                <p id="checkInEmpty" class="hidden mt-3 text-sm text-slate-500">
                    Belum ada foto masuk.
                </p>
            </div>

            <div class="rounded-3xl bg-slate-50 p-4">
                <p class="font-bold text-slate-700 mb-3">Foto Pulang</p>
                <img id="checkOutPhoto"
                    src=""
                    class="w-full aspect-video object-cover rounded-2xl bg-slate-200">
                <p id="checkOutEmpty" class="hidden mt-3 text-sm text-slate-500">
                    Belum ada foto pulang.
                </p>
            </div>
        </div>
    </div>
</div>
</div>
<script>
    function showAttendancePhotos(checkInUrl, checkOutUrl, teacherName) {
        const modal = document.getElementById('photoModal');

        const teacherNameEl = document.getElementById('photoTeacherName');

        const checkInPhoto = document.getElementById('checkInPhoto');
        const checkOutPhoto = document.getElementById('checkOutPhoto');

        const checkInEmpty = document.getElementById('checkInEmpty');
        const checkOutEmpty = document.getElementById('checkOutEmpty');

        teacherNameEl.innerText = teacherName;

        if (checkInUrl) {
            checkInPhoto.src = checkInUrl;
            checkInPhoto.classList.remove('hidden');
            checkInEmpty.classList.add('hidden');
        } else {
            checkInPhoto.src = '';
            checkInPhoto.classList.add('hidden');
            checkInEmpty.classList.remove('hidden');
        }

        if (checkOutUrl) {
            checkOutPhoto.src = checkOutUrl;
            checkOutPhoto.classList.remove('hidden');
            checkOutEmpty.classList.add('hidden');
        } else {
            checkOutPhoto.src = '';
            checkOutPhoto.classList.add('hidden');
            checkOutEmpty.classList.remove('hidden');
        }

        modal.classList.remove('hidden');

        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function hideAttendancePhotos() {
        document.getElementById('photoModal').classList.add('hidden');
    }
</script>