#!/usr/bin/env bash
set -euo pipefail

if [ ! -f artisan ]; then
    echo "ERROR: Jalankan script ini dari folder utama project Laravel (folder yang berisi artisan)."
    exit 1
fi

BACKUP_DIR=".fix-backups/views-$(date +%Y%m%d-%H%M%S)"

FILES=(
    "app/Livewire/DailyAttendances/Index.php"
    "app/Livewire/MonthlyHonors/Index.php"
    "resources/views/layouts/app.blade.php"
    "resources/views/livewire/daily-attendances/index.blade.php"
    "resources/views/livewire/monthly-honors/index.blade.php"
    "resources/views/livewire/kiosk/index.blade.php"
    "resources/views/livewire/face-attendance/index.blade.php"
    "resources/views/livewire/face-enrollment/index.blade.php"
    "resources/views/livewire/subject-attendances/index.blade.php"
)

echo "Membuat backup file lama..."
for file in "${FILES[@]}"; do
    if [ ! -f "$file" ]; then
        echo "ERROR: File tidak ditemukan: $file"
        exit 1
    fi

    mkdir -p "$BACKUP_DIR/$(dirname "$file")"
    cp "$file" "$BACKUP_DIR/$file"
done

php <<'PHP_PATCHER'
<?php

declare(strict_types=1);

function readFileStrict(string $file): string
{
    $content = file_get_contents($file);

    if ($content === false) {
        throw new RuntimeException("Gagal membaca file: {$file}");
    }

    return $content;
}

function writeFileStrict(string $file, string $content): void
{
    if (file_put_contents($file, $content) === false) {
        throw new RuntimeException("Gagal menulis file: {$file}");
    }
}

function replaceExact(
    string $file,
    string $search,
    string $replace,
    string $label,
    ?string $alreadyNeedle = null
): void {
    $content = readFileStrict($file);

    if ($alreadyNeedle !== null && str_contains($content, $alreadyNeedle)) {
        echo "SKIP: {$label} sudah diterapkan.\n";
        return;
    }

    if (! str_contains($content, $search)) {
        throw new RuntimeException(
            "Pola tidak ditemukan saat memperbaiki {$label} pada {$file}. " .
            "Kemungkinan isi file sudah berbeda. Backup tidak dihapus."
        );
    }

    $content = str_replace($search, $replace, $content, $count);

    if ($count < 1) {
        throw new RuntimeException("Tidak ada perubahan untuk {$label}.");
    }

    writeFileStrict($file, $content);
    echo "OK: {$label}\n";
}

function replaceRegex(
    string $file,
    string $pattern,
    string $replace,
    string $label,
    ?string $alreadyNeedle = null
): void {
    $content = readFileStrict($file);

    if ($alreadyNeedle !== null && str_contains($content, $alreadyNeedle)) {
        echo "SKIP: {$label} sudah diterapkan.\n";
        return;
    }

    $count = 0;
    $updated = preg_replace_callback(
        $pattern,
        static function () use ($replace): string {
            return $replace;
        },
        $content,
        1,
        $count
    );

    if ($updated === null) {
        throw new RuntimeException("Regex error saat memperbaiki {$label}.");
    }

    if ($count !== 1) {
        throw new RuntimeException(
            "Pola tidak ditemukan atau tidak tunggal saat memperbaiki {$label} pada {$file}."
        );
    }

    writeFileStrict($file, $updated);
    echo "OK: {$label}\n";
}

/* ================================================================
 | 1. Backend Absensi Harian: kirim pengaturan transport ke view
 * ================================================================ */

$dailyComponent = 'app/Livewire/DailyAttendances/Index.php';

replaceExact(
    $dailyComponent,
    "use App\\Models\\Teacher;\n",
    "use App\\Models\\Teacher;\nuse App\\Models\\TransportSetting;\n",
    'import TransportSetting pada Absensi Harian',
    'use App\\Models\\TransportSetting;'
);

replaceExact(
    $dailyComponent,
    <<<'SEARCH'
                'teachers' => Teacher::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),
SEARCH,
    <<<'REPLACE'
                'teachers' => Teacher::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),
                'transportSetting' => TransportSetting::query()
                    ->where('is_active', true)
                    ->latest('id')
                    ->first(),
REPLACE,
    'data pengaturan transport pada view Absensi Harian',
    "'transportSetting' => TransportSetting::query()"
);

/* ================================================================
 | 2. Backend Rekap Honor: validasi dan transaksi bayar massal
 * ================================================================ */

$monthlyComponent = 'app/Livewire/MonthlyHonors/Index.php';

replaceExact(
    $monthlyComponent,
    "use Illuminate\\Support\\Facades\\Artisan;\n",
    "use Illuminate\\Support\\Facades\\Artisan;\nuse Illuminate\\Support\\Facades\\DB;\n",
    'import DB pada Rekap Honor',
    'use Illuminate\\Support\\Facades\\DB;'
);

replaceExact(
    $monthlyComponent,
    <<<'SEARCH'
            session()->flash(
                'success',
                'Pilih minimal satu rekap honor.'
            );
SEARCH,
    <<<'REPLACE'
            session()->flash(
                'error',
                'Pilih minimal satu rekap honor.'
            );
REPLACE,
    'jenis pesan saat belum memilih honor',
    "'error',\n                'Pilih minimal satu rekap honor.'"
);

replaceRegex(
    $monthlyComponent,
    '~\n\s*public function saveBulkPayment\(\): void\s*\{.*?\n\s*\}\s*\n\s*public function deleteRecap\(\): void~s',
    <<<'REPLACE'

    public function saveBulkPayment(): void
    {
        $data = $this->validate([
            'selectedHonors' => ['required', 'array', 'min:1'],
            'selectedHonors.*' => ['integer', 'exists:monthly_honors,id'],
            'bulk_payment_date' => ['required', 'date'],
            'bulk_payment_method' => ['required', 'in:cash,transfer,qris'],
            'bulk_reference_number' => ['nullable', 'string', 'max:255'],
            'bulk_payment_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $honorIds = collect($data['selectedHonors'])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $processed = 0;

        DB::transaction(function () use ($honorIds, $data, &$processed): void {
            $honors = MonthlyHonor::query()
                ->whereIn('id', $honorIds)
                ->lockForUpdate()
                ->get();

            foreach ($honors as $honor) {
                $alreadyPaid = (int) $honor->payments()->sum('amount');
                $remaining = max((int) $honor->grand_total - $alreadyPaid, 0);

                if ($remaining <= 0) {
                    continue;
                }

                HonorPayment::create([
                    'monthly_honor_id' => $honor->id,
                    'payment_date' => $data['bulk_payment_date'],
                    'amount' => $remaining,
                    'payment_method' => $data['bulk_payment_method'],
                    'reference_number' => $data['bulk_reference_number'] ?: null,
                    'note' => $data['bulk_payment_note'] ?: null,
                ]);

                $honor->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);

                $processed++;
            }
        });

        $this->selectedHonors = [];
        $this->showBulkPaymentModal = false;
        $this->bulk_payment_date = '';
        $this->bulk_payment_method = 'cash';
        $this->bulk_reference_number = '';
        $this->bulk_payment_note = '';
        $this->resetValidation();

        session()->flash(
            $processed > 0 ? 'success' : 'error',
            $processed > 0
                ? "Pembayaran massal berhasil diproses untuk {$processed} rekap honor."
                : 'Semua honor yang dipilih sudah lunas atau tidak memiliki sisa pembayaran.'
        );
    }

    public function deleteRecap(): void
REPLACE,
    'logika pembayaran honor massal',
    "DB::transaction(function () use (\$honorIds"
);

/* ================================================================
 | 3. View Absensi Harian
 * ================================================================ */

$dailyView = 'resources/views/livewire/daily-attendances/index.blade.php';

replaceExact(
    $dailyView,
    '<p class="text-blue-50 mt-2">Jam 06:45 - 07:15 mendapat transport Rp10.000.</p>',
    <<<'REPLACE'
@if ($transportSetting)
                        <p class="text-blue-50 mt-2">
                            Transport diberikan setelah absen masuk
                            {{ substr((string) $transportSetting->check_in_start, 0, 5) }}–{{ substr((string) $transportSetting->check_in_end, 0, 5) }}
                            dan absen pulang
                            {{ substr((string) $transportSetting->check_out_start, 0, 5) }}–{{ substr((string) $transportSetting->check_out_end, 0, 5) }}.
                            Nominal Rp{{ number_format($transportSetting->amount, 0, ',', '.') }}.
                        </p>
                    @else
                        <p class="text-blue-50 mt-2">
                            Pengaturan transport belum aktif. Absensi tetap dicatat tanpa transport.
                        </p>
                    @endif
REPLACE,
    'informasi transport dinamis',
    '@if ($transportSetting)'
);

replaceExact(
    $dailyView,
    <<<'SEARCH'
        </section>

        <section class="grid lg:grid-cols-3 gap-4">
SEARCH,
    <<<'REPLACE'
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 font-bold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4 font-bold text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <section class="grid lg:grid-cols-3 gap-4">
REPLACE,
    'notifikasi sukses dan error Absensi Harian',
    "session('error')"
);

replaceExact(
    $dailyView,
    <<<'SEARCH'
        <section class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full">
SEARCH,
    <<<'REPLACE'
        <section class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px]">
REPLACE,
    'tabel responsif Absensi Harian',
    'min-w-[1100px]'
);

replaceExact(
    $dailyView,
    <<<'SEARCH'
            </table>
        </section>

        {{ $attendances->links() }}
SEARCH,
    <<<'REPLACE'
                </table>
            </div>
        </section>

        {{ $attendances->links() }}
REPLACE,
    'penutup tabel responsif Absensi Harian',
    "</table>\n            </div>\n        </section>\n\n        {{ \$attendances->links() }}"
);

replaceExact(
    $dailyView,
    <<<'SEARCH'
                                <button
                                    type="button"
                                    onclick="showAttendancePhotos(
                                        '{{ $attendance->check_in_photo ? asset('storage/' . $attendance->check_in_photo) : '' }}',
                                        '{{ $attendance->check_out_photo ? asset('storage/' . $attendance->check_out_photo) : '' }}',
                                        '{{ $attendance->teacher->name }}'
                                    )"
                                    class="px-4 py-2 rounded-xl bg-blue-50 text-blue-700 font-bold">
SEARCH,
    <<<'REPLACE'
                                <button
                                    type="button"
                                    data-check-in-url="{{ $attendance->check_in_photo ? asset('storage/' . $attendance->check_in_photo) : '' }}"
                                    data-check-out-url="{{ $attendance->check_out_photo ? asset('storage/' . $attendance->check_out_photo) : '' }}"
                                    data-teacher-name="{{ $attendance->teacher?->name ?? '-' }}"
                                    onclick="showAttendancePhotosFromButton(this)"
                                    class="px-4 py-2 rounded-xl bg-blue-50 text-blue-700 font-bold">
REPLACE,
    'data foto aman dari tanda petik nama guru',
    'showAttendancePhotosFromButton(this)'
);

replaceExact(
    $dailyView,
    '<td colspan="8" class="p-8 text-center text-slate-500">',
    '<td colspan="7" class="p-8 text-center text-slate-500">',
    'jumlah colspan Absensi Harian',
    '<td colspan="7" class="p-8 text-center text-slate-500">'
);

replaceExact(
    $dailyView,
    <<<'SEARCH'
<script>
    function showAttendancePhotos(checkInUrl, checkOutUrl, teacherName) {
SEARCH,
    <<<'REPLACE'
<script>
    function showAttendancePhotosFromButton(button) {
        showAttendancePhotos(
            button.dataset.checkInUrl || '',
            button.dataset.checkOutUrl || '',
            button.dataset.teacherName || '-'
        );
    }

    function showAttendancePhotos(checkInUrl, checkOutUrl, teacherName) {
REPLACE,
    'helper modal foto yang aman',
    'function showAttendancePhotosFromButton(button)'
);

/* ================================================================
 | 4. View Rekap Honor Bulanan
 * ================================================================ */

$monthlyView = 'resources/views/livewire/monthly-honors/index.blade.php';

replaceExact(
    $monthlyView,
    <<<'SEARCH'
        @if (session('success'))
            <div class="rounded-2xl bg-emerald-100 text-emerald-700 p-4 font-bold">
                {{ session('success') }}
            </div>
        @endif
SEARCH,
    <<<'REPLACE'
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
REPLACE,
    'pesan error Rekap Honor',
    "session('error')"
);

replaceExact(
    $monthlyView,
    <<<'SEARCH'
    @endif

    <script>
        if (window.lucide) {
SEARCH,
    <<<'REPLACE'
    @endif

    @if ($showBulkPaymentModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/50 p-4 backdrop-blur-sm sm:items-center">
            <div class="w-full max-w-lg rounded-t-3xl bg-white p-6 shadow-2xl sm:rounded-3xl">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-emerald-600">Pembayaran Massal</p>
                        <h3 class="text-xl font-black text-slate-900">Bayar Honor Terpilih</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ count($selectedHonors) }} rekap dipilih. Sistem membayar seluruh sisa honor masing-masing guru.
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="$set('showBulkPaymentModal', false)"
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600"
                    >
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>

                <form wire:submit="saveBulkPayment" class="space-y-4">
                    <div>
                        <label class="text-sm font-bold text-slate-700">Tanggal Bayar</label>
                        <input
                            wire:model="bulk_payment_date"
                            type="date"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500"
                        >
                        @error('bulk_payment_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold text-slate-700">Metode Pembayaran</label>
                        <select
                            wire:model="bulk_payment_method"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer</option>
                            <option value="qris">QRIS</option>
                        </select>
                        @error('bulk_payment_method')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold text-slate-700">Nomor Referensi</label>
                        <input
                            wire:model="bulk_reference_number"
                            type="text"
                            placeholder="Opsional"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500"
                        >
                        @error('bulk_reference_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold text-slate-700">Catatan</label>
                        <textarea
                            wire:model="bulk_payment_note"
                            rows="3"
                            placeholder="Opsional"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500"
                        ></textarea>
                        @error('bulk_payment_note')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @error('selectedHonors')
                        <div class="rounded-2xl bg-red-50 p-3 text-sm font-bold text-red-700">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="flex gap-3 pt-2">
                        <button
                            type="button"
                            wire:click="$set('showBulkPaymentModal', false)"
                            class="flex-1 rounded-2xl bg-slate-100 px-5 py-3 font-black text-slate-700"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="saveBulkPayment"
                            class="flex-1 rounded-2xl bg-emerald-600 px-5 py-3 font-black text-white shadow disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="saveBulkPayment">Bayar Sekarang</span>
                            <span wire:loading wire:target="saveBulkPayment">Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script>
        if (window.lucide) {
REPLACE,
    'modal pembayaran honor massal',
    'Bayar Honor Terpilih'
);

/* ================================================================
 | 5. View Kiosk: scan lock, cooldown, kamera, dan suara transport
 * ================================================================ */

$kioskView = 'resources/views/livewire/kiosk/index.blade.php';

replaceRegex(
    $kioskView,
    '~\n    <script>\s*const registeredTeachers = @js\(.*?</script>\s*</div>\s*$~s',
    <<<'REPLACE'

    <script>
        const registeredTeachers = @js(
            $teachers->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'descriptor' => $teacher->face_descriptor,
                ];
            })->values()
        );

        let attendanceVideo = null;
        let attendanceStream = null;
        let modelsLoaded = false;
        let scanInterval = null;
        let scanBusy = false;
        let lastDetectedTeacherId = null;
        let lastDetectedAt = 0;
        let popupTimer = null;

        async function loadAttendanceModels() {
            if (modelsLoaded) return;

            setScanText('Memuat Model', 'Mohon tunggu sebentar...');
            setCameraStatus('LOADING');

            await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('/models');

            modelsLoaded = true;
            setCameraStatus('MODEL SIAP');
        }

        async function startCamera() {
            attendanceVideo = document.getElementById('camera');

            if (!registeredTeachers.length) {
                showPremiumPopup(
                    'warning',
                    'Belum Ada Data Wajah',
                    'Silakan registrasi wajah guru terlebih dahulu.',
                    'Perhatian'
                );
                speakText('Belum ada data wajah guru yang diregistrasi.');
                return;
            }

            try {
                if (scanInterval) {
                    clearInterval(scanInterval);
                    scanInterval = null;
                }

                stopCameraTracks();
                await loadAttendanceModels();

                attendanceStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                });

                attendanceVideo.srcObject = attendanceStream;
                await attendanceVideo.play();

                setScanText('Kamera Aktif', 'Scan realtime berjalan...');
                setCameraStatus('REALTIME');
                startRealtimeScan();
            } catch (error) {
                console.error('KIOSK CAMERA ERROR:', error);
                stopCameraTracks();

                showPremiumPopup(
                    'warning',
                    'Kamera Gagal',
                    'Kamera tidak bisa diakses atau model wajah belum tersedia.',
                    'Error'
                );

                setScanText(
                    'Kamera Gagal',
                    'Pastikan izin kamera aktif dan folder /models tersedia.'
                );
                setCameraStatus('ERROR');
            }
        }

        function startRealtimeScan() {
            if (scanInterval) {
                clearInterval(scanInterval);
            }

            scanInterval = setInterval(async () => {
                await recognizeFaceRealtime();
            }, 1500);
        }

        function stopCameraTracks() {
            if (attendanceStream) {
                attendanceStream.getTracks().forEach((track) => track.stop());
                attendanceStream = null;
            }

            if (attendanceVideo?.srcObject) {
                attendanceVideo.srcObject
                    .getTracks()
                    .forEach((track) => track.stop());
                attendanceVideo.srcObject = null;
            }
        }

        function stopRealtimeScan() {
            if (scanInterval) {
                clearInterval(scanInterval);
                scanInterval = null;
            }

            scanBusy = false;
            stopCameraTracks();
            clearFaceOverlay();
            setScanText('Scan Dihentikan', 'Tekan Kamera untuk mulai lagi.');
            setCameraStatus('STOP');
        }

        async function recognizeFaceRealtime() {
            if (scanBusy) return;
            if (!attendanceVideo || attendanceVideo.readyState !== 4) return;

            scanBusy = true;

            try {
                const detection = await faceapi
                    .detectSingleFace(
                        attendanceVideo,
                        new faceapi.TinyFaceDetectorOptions({
                            inputSize: 416,
                            scoreThreshold: 0.6
                        })
                    )
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detection) {
                    clearFaceOverlay();
                    setScanText(
                        'Wajah Belum Terdeteksi',
                        'Pastikan wajah jelas dan cahaya cukup.'
                    );
                    setCameraStatus('MENCARI WAJAH');
                    return;
                }

                let bestMatch = null;
                let bestDistance = 999;

                registeredTeachers.forEach((teacher) => {
                    if (!teacher.descriptor) return;

                    let descriptorData = teacher.descriptor;

                    if (typeof descriptorData === 'string') {
                        try {
                            descriptorData = JSON.parse(descriptorData);
                        } catch (error) {
                            console.error(
                                'Descriptor guru rusak:',
                                teacher.name,
                                error
                            );
                            return;
                        }
                    }

                    const savedDescriptor = new Float32Array(descriptorData);
                    const distance = faceapi.euclideanDistance(
                        detection.descriptor,
                        savedDescriptor
                    );

                    if (distance < bestDistance) {
                        bestDistance = distance;
                        bestMatch = teacher;
                    }
                });

                if (!bestMatch || bestDistance > 0.45) {
                    drawFaceOverlay(detection, null, null);
                    setScanText(
                        'Tidak Dikenali',
                        'Wajah terdeteksi, tetapi belum cocok dengan data guru.'
                    );
                    setCameraStatus('BELUM COCOK');
                    return;
                }

                drawFaceOverlay(detection, bestMatch.name, bestDistance);

                const now = Date.now();
                const cooldownMs = 20000;

                if (
                    lastDetectedTeacherId === bestMatch.id &&
                    now - lastDetectedAt < cooldownMs
                ) {
                    const remainingSeconds = Math.ceil(
                        (cooldownMs - (now - lastDetectedAt)) / 1000
                    );

                    setScanText(
                        bestMatch.name,
                        'Cooldown aktif. Tunggu ' + remainingSeconds + ' detik.'
                    );
                    setCameraStatus('COOLDOWN');
                    return;
                }

                lastDetectedTeacherId = bestMatch.id;
                lastDetectedAt = now;

                setScanText(
                    bestMatch.name,
                    'Kecocokan: ' + ((1 - bestDistance) * 100).toFixed(2) + '%'
                );
                setCameraStatus('TERDETEKSI');

                const photoBase64 = captureCameraPhoto();

                const result = await @this.call(
                    'saveAttendanceByTeacherId',
                    bestMatch.id,
                    photoBase64
                );

                handleAttendanceResult(result);
            } catch (error) {
                console.error('KIOSK RECOGNITION ERROR:', error);
                lastDetectedTeacherId = null;
                lastDetectedAt = 0;
                setScanText('Terjadi Kesalahan', 'Silakan arahkan wajah kembali.');
                setCameraStatus('ERROR');
            } finally {
                scanBusy = false;
            }
        }

        function handleAttendanceResult(result) {
            if (!result) return;

            if (result.status === 'success' && result.type === 'check_in') {
                speakText(
                    'Selamat datang ' + result.name +
                    '. Absensi masuk berhasil. Transport menunggu absensi pulang.'
                );

                showPremiumPopup(
                    'success',
                    'Absen Masuk Berhasil',
                    result.message,
                    'Masuk'
                );
                return;
            }

            if (result.status === 'success' && result.type === 'check_out') {
                const transportText = Number(result.transport || 0) > 0
                    ? 'Anda mendapatkan transport sebesar ' +
                        formatRupiah(result.transport) + '.'
                    : 'Anda tidak mendapatkan transport.';

                speakText(
                    'Terima kasih ' + result.name +
                    '. Absensi pulang berhasil. ' + transportText
                );

                showPremiumPopup(
                    'checkout',
                    'Absen Pulang Berhasil',
                    result.message,
                    'Pulang'
                );
                return;
            }

            if (result.status === 'already') {
                speakText(result.message);
                showPremiumPopup(
                    'already',
                    'Sudah Melakukan Absensi',
                    result.message,
                    'Duplikat Dicegah'
                );
                return;
            }

            if (result.status === 'no_check_in') {
                speakText(result.message);
                showPremiumPopup(
                    'warning',
                    'Belum Absen Masuk',
                    result.message,
                    'Perhatian'
                );
            }
        }

        function drawFaceOverlay(detection, name = null, distance = null) {
            const canvas = document.getElementById('faceOverlay');
            const video = document.getElementById('camera');

            if (!canvas || !video || !detection) return;

            const displaySize = {
                width: video.clientWidth,
                height: video.clientHeight
            };

            canvas.width = displaySize.width;
            canvas.height = displaySize.height;

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            const resizedDetection = faceapi.resizeResults(
                detection,
                displaySize
            );
            const box = resizedDetection.detection.box;
            const isDetected = Boolean(name);

            ctx.lineWidth = 4;
            ctx.strokeStyle = isDetected ? '#22c55e' : '#ffffff';
            ctx.shadowColor = isDetected
                ? 'rgba(34,197,94,0.9)'
                : 'rgba(255,255,255,0.5)';
            ctx.shadowBlur = 18;
            ctx.strokeRect(box.x, box.y, box.width, box.height);

            if (!isDetected) return;

            const label = distance !== null
                ? `${name} • ${((1 - distance) * 100).toFixed(1)}%`
                : name;

            const labelWidth = Math.max(190, label.length * 9);
            const labelX = box.x;
            const labelY = Math.max(12, box.y - 44);

            ctx.shadowBlur = 0;
            ctx.fillStyle = 'rgba(34,197,94,0.96)';

            if (ctx.roundRect) {
                ctx.beginPath();
                ctx.roundRect(labelX, labelY, labelWidth, 36, 14);
                ctx.fill();
            } else {
                ctx.fillRect(labelX, labelY, labelWidth, 36);
            }

            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 15px Arial';
            ctx.fillText(label, labelX + 14, labelY + 23);
        }

        function clearFaceOverlay() {
            const canvas = document.getElementById('faceOverlay');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        function captureCameraPhoto() {
            const video = document.getElementById('camera');

            if (!video || video.readyState !== 4 || !video.videoWidth) {
                return null;
            }

            const maxWidth = 720;
            const scale = Math.min(1, maxWidth / video.videoWidth);
            const canvas = document.createElement('canvas');

            canvas.width = Math.round(video.videoWidth * scale);
            canvas.height = Math.round(video.videoHeight * scale);

            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            return canvas.toDataURL('image/jpeg', 0.82);
        }

        function setScanText(title, message) {
            const detectedTeacher = document.getElementById('detectedTeacher');
            const matchScore = document.getElementById('matchScore');

            if (detectedTeacher) detectedTeacher.innerText = title;
            if (matchScore) matchScore.innerText = message;
        }

        function setCameraStatus(text) {
            const cameraStatus = document.getElementById('cameraStatus');
            if (cameraStatus) cameraStatus.innerText = text;
        }

        function formatRupiah(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(Number(value || 0));
        }

        function speakText(text) {
            if (!window.speechSynthesis) return;

            const speech = new SpeechSynthesisUtterance(text);
            speech.lang = 'id-ID';
            speech.rate = 0.95;
            speech.pitch = 1;

            window.speechSynthesis.cancel();
            window.speechSynthesis.speak(speech);
        }

        function showPremiumPopup(type, title, message, badge) {
            const popup = document.getElementById('premiumPopup');
            const icon = document.getElementById('popupIcon');
            const popupBadge = document.getElementById('popupBadge');
            const popupTitle = document.getElementById('popupTitle');
            const popupMessage = document.getElementById('popupMessage');

            popupTitle.innerText = title;
            popupMessage.innerText = message;
            popupBadge.innerText = badge;

            const styles = {
                success: {
                    iconClass: 'bg-emerald-100 text-emerald-600',
                    badgeClass: 'bg-emerald-100 text-emerald-700',
                    icon: 'check-circle-2'
                },
                checkout: {
                    iconClass: 'bg-sky-100 text-sky-600',
                    badgeClass: 'bg-sky-100 text-sky-700',
                    icon: 'log-out'
                },
                already: {
                    iconClass: 'bg-amber-100 text-amber-600',
                    badgeClass: 'bg-amber-100 text-amber-700',
                    icon: 'shield-alert'
                },
                warning: {
                    iconClass: 'bg-red-100 text-red-600',
                    badgeClass: 'bg-red-100 text-red-700',
                    icon: 'alert-triangle'
                }
            };

            const selected = styles[type] || styles.warning;

            icon.className =
                'mx-auto mb-4 w-20 h-20 rounded-full flex items-center justify-center ' +
                selected.iconClass;
            icon.innerHTML =
                `<i data-lucide="${selected.icon}" class="w-10 h-10"></i>`;
            popupBadge.className =
                'inline-flex mb-3 px-4 py-1 rounded-full text-xs font-black ' +
                selected.badgeClass;

            popup.classList.remove('hidden');

            if (window.lucide) {
                lucide.createIcons();
            }

            if (popupTimer) {
                clearTimeout(popupTimer);
            }

            popupTimer = setTimeout(hidePremiumPopup, 4500);
        }

        function hidePremiumPopup() {
            const popup = document.getElementById('premiumPopup');
            popup?.classList.add('hidden');

            if (popupTimer) {
                clearTimeout(popupTimer);
                popupTimer = null;
            }
        }

        window.addEventListener('beforeunload', stopCameraTracks);
        document.addEventListener('livewire:navigating', stopRealtimeScan);
    </script>
</div>
REPLACE,
    'stabilitas scan dan suara Kiosk',
    'let scanBusy = false;'
);

/* ================================================================
 | 6. View Face Attendance: kirim foto bukti
 * ================================================================ */

$faceAttendanceView = 'resources/views/livewire/face-attendance/index.blade.php';

replaceExact(
    $faceAttendanceView,
    <<<'SEARCH'
                const result = await @this.call(
                    'saveAttendanceByTeacherId',
                    bestMatch.id
                );
SEARCH,
    <<<'REPLACE'
                const photoBase64 = captureAttendancePhoto();

                const result = await @this.call(
                    'saveAttendanceByTeacherId',
                    bestMatch.id,
                    photoBase64
                );
REPLACE,
    'pengiriman foto dari Face Attendance',
    'const photoBase64 = captureAttendancePhoto();'
);

replaceExact(
    $faceAttendanceView,
    <<<'SEARCH'
        function formatRupiah(value) {
SEARCH,
    <<<'REPLACE'
        function captureAttendancePhoto() {
            const video = document.getElementById('camera');

            if (!video || video.readyState !== 4 || !video.videoWidth) {
                return null;
            }

            const maxWidth = 720;
            const scale = Math.min(1, maxWidth / video.videoWidth);
            const canvas = document.createElement('canvas');

            canvas.width = Math.round(video.videoWidth * scale);
            canvas.height = Math.round(video.videoHeight * scale);

            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            return canvas.toDataURL('image/jpeg', 0.82);
        }

        function formatRupiah(value) {
REPLACE,
    'fungsi capture foto Face Attendance',
    'function captureAttendancePhoto()'
);

/* ================================================================
 | 7. View Face Enrollment: hentikan stream kamera dengan benar
 * ================================================================ */

$faceEnrollmentView = 'resources/views/livewire/face-enrollment/index.blade.php';

replaceExact(
    $faceEnrollmentView,
    <<<'SEARCH'
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
SEARCH,
    <<<'REPLACE'
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 font-bold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
REPLACE,
    'pesan sukses Registrasi Wajah',
    "session('success')"
);

replaceRegex(
    $faceEnrollmentView,
    '~<script>\s*let enrollVideo = null;.*?</script>\s*</div>\s*$~s',
    <<<'REPLACE'
<script>
        let enrollVideo = null;
        let enrollStream = null;
        let enrollModelsLoaded = false;
        let captureCooldown = false;

        function speakAI(text) {
            if (!window.speechSynthesis) return;

            window.speechSynthesis.cancel();

            const speech = new SpeechSynthesisUtterance(text);
            speech.lang = 'id-ID';
            speech.rate = 1;
            speech.pitch = 1;

            window.speechSynthesis.speak(speech);
        }

        document.addEventListener('change', function (event) {
            if (event.target.id !== 'teacherSelect') return;

            const selected = event.target.options[
                event.target.selectedIndex
            ]?.text || 'Pilih guru terlebih dahulu';

            document.getElementById('teacherOverlay').innerText = selected;
        });

        async function loadFaceModels() {
            if (enrollModelsLoaded) return;

            document.getElementById('faceStatus').innerText =
                'Memuat model wajah...';

            await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('/models');

            enrollModelsLoaded = true;
            document.getElementById('faceStatus').innerText =
                'Model wajah siap';
        }

        function stopEnrollCamera() {
            if (enrollStream) {
                enrollStream.getTracks().forEach((track) => track.stop());
                enrollStream = null;
            }

            if (enrollVideo?.srcObject) {
                enrollVideo.srcObject
                    .getTracks()
                    .forEach((track) => track.stop());
                enrollVideo.srcObject = null;
            }
        }

        async function startEnrollCamera() {
            enrollVideo = document.getElementById('enrollCamera');

            try {
                stopEnrollCamera();
                await loadFaceModels();

                enrollStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                });

                enrollVideo.srcObject = enrollStream;
                await enrollVideo.play();

                document.getElementById('faceStatus').innerText =
                    'Kamera aktif';
                document.getElementById('cameraHint').innerText =
                    'Silakan ambil data wajah guru.';

                speakAI('Kamera aktif. Silakan ambil data wajah.');

                Swal.fire({
                    icon: 'success',
                    title: 'Kamera aktif',
                    timer: 1200,
                    showConfirmButton: false
                });
            } catch (error) {
                console.error('ENROLL CAMERA ERROR:', error);
                stopEnrollCamera();

                document.getElementById('faceStatus').innerText =
                    'Kamera gagal aktif';

                Swal.fire({
                    icon: 'error',
                    title: 'Kamera gagal aktif',
                    text: 'Periksa izin kamera atau file model wajah.'
                });
            }
        }

        async function captureFaceDescriptor() {
            if (captureCooldown) return;

            if (
                !enrollVideo ||
                !enrollVideo.srcObject ||
                enrollVideo.readyState !== 4
            ) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Kamera belum aktif',
                    text: 'Aktifkan kamera terlebih dahulu.'
                });
                return;
            }

            const teacherSelect = document.getElementById('teacherSelect');

            if (!teacherSelect.value) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih guru dulu',
                    text: 'Nama guru wajib dipilih sebelum mengambil data wajah.'
                });
                return;
            }

            captureCooldown = true;

            try {
                document.getElementById('faceStatus').innerText =
                    'AI sedang mendeteksi wajah...';
                document.getElementById('cameraHint').innerText =
                    'AI sedang membaca pola wajah.';

                const detection = await faceapi
                    .detectSingleFace(
                        enrollVideo,
                        new faceapi.TinyFaceDetectorOptions({
                            inputSize: 416,
                            scoreThreshold: 0.6
                        })
                    )
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detection) {
                    document.getElementById('faceStatus').innerText =
                        'Wajah tidak terdeteksi';
                    document.getElementById('cameraHint').innerText =
                        'Pastikan wajah terang dan menghadap kamera.';

                    speakAI('Wajah tidak terdeteksi. Silakan coba lagi.');

                    Swal.fire({
                        icon: 'error',
                        title: 'Wajah tidak terdeteksi',
                        text: 'Pastikan wajah jelas, terang, dan menghadap depan.'
                    });
                    return;
                }

                const descriptor = Array.from(detection.descriptor);
                @this.set('descriptor', JSON.stringify(descriptor));

                const teacherName = teacherSelect.options[
                    teacherSelect.selectedIndex
                ].text
                    .replace('(Sudah Terdaftar)', '')
                    .replace('(Belum)', '')
                    .trim();

                document.getElementById('teacherOverlay').innerText =
                    'Terdeteksi: ' + teacherName;
                document.getElementById('faceStatus').innerText =
                    'Data wajah berhasil diambil';
                document.getElementById('cameraHint').innerText =
                    'Klik Simpan Data Wajah untuk menyimpan.';

                speakAI('Data wajah ' + teacherName + ' berhasil direkam.');

                Swal.fire({
                    icon: 'success',
                    title: 'Wajah berhasil direkam',
                    text: teacherName,
                    timer: 1500,
                    showConfirmButton: false
                });
            } catch (error) {
                console.error('FACE CAPTURE ERROR:', error);

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal membaca wajah',
                    text: 'Silakan ulangi proses pengambilan data wajah.'
                });
            } finally {
                setTimeout(() => {
                    captureCooldown = false;
                }, 1000);
            }
        }

        window.addEventListener('beforeunload', stopEnrollCamera);
        document.addEventListener('livewire:navigating', stopEnrollCamera);
    </script>
</div>
REPLACE,
    'pembersihan stream Registrasi Wajah',
    'let enrollStream = null;'
);

/* ================================================================
 | 8. Tabel Absensi Mapel responsif
 * ================================================================ */

$subjectView = 'resources/views/livewire/subject-attendances/index.blade.php';

replaceExact(
    $subjectView,
    <<<'SEARCH'
        <section class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full">
SEARCH,
    <<<'REPLACE'
        <section class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
REPLACE,
    'tabel responsif Absensi Mapel',
    'min-w-[900px]'
);

replaceExact(
    $subjectView,
    <<<'SEARCH'
            </table>
        </section>

        {{ $attendances->links() }}
SEARCH,
    <<<'REPLACE'
                </table>
            </div>
        </section>

        {{ $attendances->links() }}
REPLACE,
    'penutup tabel responsif Absensi Mapel',
    "</table>\n            </div>\n        </section>\n\n        {{ \$attendances->links() }}"
);

/* ================================================================
 | 9. face-api.js hanya dimuat di halaman yang memerlukan
 * ================================================================ */

$layoutView = 'resources/views/layouts/app.blade.php';

replaceExact(
    $layoutView,
    '<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>',
    <<<'REPLACE'
@if (request()->routeIs('face-attendance.*', 'face-enrollment.*'))
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
@endif
REPLACE,
    'pemuatan face-api.js secara kondisional',
    "request()->routeIs('face-attendance.*', 'face-enrollment.*')"
);

echo "Semua patch file berhasil diterapkan.\n";
PHP_PATCHER

echo "Memeriksa sintaks PHP..."
php -l app/Livewire/DailyAttendances/Index.php
php -l app/Livewire/MonthlyHonors/Index.php

echo "Memeriksa kompilasi Blade..."
php artisan view:clear
php artisan view:cache
php artisan view:clear

echo "Membersihkan cache Laravel..."
php artisan optimize:clear

echo ""
echo "PERBAIKAN VIEW SELESAI."
echo "Backup file lama: $BACKUP_DIR"
echo "Silakan cek perubahan dengan: git diff"
