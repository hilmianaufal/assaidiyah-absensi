<div>
    <x-slot name="header">
        Absensi Wajah Realtime
    </x-slot>

    {{-- POPUP HASIL ABSENSI --}}
    <div
        id="premiumPopup"
        class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/70 p-5 backdrop-blur-md"
    >
        <div class="w-full max-w-md rounded-[2rem] border border-white/60 bg-white p-7 text-center shadow-2xl">
            <div
                id="popupIcon"
                class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"
            >
                <i data-lucide="check-circle-2" class="h-10 w-10"></i>
            </div>

            <p
                id="popupBadge"
                class="mb-3 inline-flex rounded-full bg-emerald-100 px-4 py-1 text-xs font-bold text-emerald-700"
            >
                Berhasil
            </p>

            <h2
                id="popupTitle"
                class="text-2xl font-black text-slate-900"
            >
                Absensi Berhasil
            </h2>

            <p
                id="popupMessage"
                class="mt-3 leading-7 text-slate-500"
            >
                Data absensi berhasil disimpan.
            </p>

            <button
                type="button"
                onclick="hidePremiumPopup()"
                class="mt-6 w-full rounded-2xl bg-blue-600 px-5 py-3.5 font-bold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700"
            >
                Oke
            </button>
        </div>
    </div>

    <div class="space-y-5 pb-8">

        {{-- HERO --}}
        <section
            class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl shadow-blue-200/40"
        >
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur">
                    <i data-lucide="scan-face" class="h-8 w-8"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-blue-100">
                        Realtime Face Recognition
                    </p>

                    <h1 class="mt-1 text-2xl font-black sm:text-3xl">
                        Absensi Wajah Guru
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-blue-50 sm:text-base">
                        Kamera mengenali wajah secara otomatis, mencatat jam masuk
                        dan pulang, serta menghitung transport guru.
                    </p>
                </div>
            </div>
        </section>

        {{-- PILIH MODE --}}
        <section class="rounded-[1.75rem] border border-slate-100 bg-white p-3 shadow-sm">
            <div class="grid grid-cols-2 gap-3">
                <button
                    wire:click="setMode('check_in')"
                    type="button"
                    class="flex items-center justify-center gap-2 rounded-2xl px-4 py-4 text-sm font-black transition sm:text-base
                        {{ $mode === 'check_in'
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-200'
                            : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
                >
                    <i data-lucide="log-in" class="h-5 w-5"></i>
                    Absen Masuk
                </button>

                <button
                    wire:click="setMode('check_out')"
                    type="button"
                    class="flex items-center justify-center gap-2 rounded-2xl px-4 py-4 text-sm font-black transition sm:text-base
                        {{ $mode === 'check_out'
                            ? 'bg-sky-600 text-white shadow-lg shadow-sky-200'
                            : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
                >
                    <i data-lucide="log-out" class="h-5 w-5"></i>
                    Absen Pulang
                </button>
            </div>
        </section>

        {{-- KAMERA DAN STATUS --}}
        <section class="grid gap-5 xl:grid-cols-[1.6fr_0.8fr]">

            {{-- KAMERA --}}
            <div class="rounded-[2rem] border border-slate-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-500">
                            Kamera Realtime
                        </p>

                        <h2 class="mt-1 text-xl font-black text-slate-900">
                            Arahkan Wajah ke Kamera
                        </h2>
                    </div>

                    <div
                        class="flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700"
                    >
                        <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-emerald-500"></span>
                        Siap Scan
                    </div>
                </div>

                {{--
                    Area kamera dibuat tinggi di HP.
                    Tidak ada teks status yang menutupi wajah.
                --}}
                <div
                    class="relative aspect-[3/4] w-full overflow-hidden rounded-[1.75rem] bg-slate-950 sm:aspect-[4/3] lg:aspect-video"
                >
                    <video
                        id="camera"
                        autoplay
                        playsinline
                        muted
                        class="absolute inset-0 h-full w-full -scale-x-100 object-cover"
                    ></video>

                    {{-- Overlay hanya garis panduan wajah --}}
                    <div class="pointer-events-none absolute inset-0">
                        <div class="absolute inset-4 rounded-[1.5rem] border border-white/15 sm:inset-6"></div>

                        <div
                            class="absolute left-1/2 top-1/2 h-[52%] w-[58%] -translate-x-1/2 -translate-y-1/2 rounded-[45%] border-[3px] border-white/90 shadow-[0_0_0_9999px_rgba(15,23,42,0.18)] sm:h-[58%] sm:w-[36%]"
                        ></div>

                        {{-- Sudut scanner --}}
                        <div class="absolute left-6 top-6 h-10 w-10 border-l-4 border-t-4 border-blue-400 sm:left-8 sm:top-8"></div>

                        <div class="absolute right-6 top-6 h-10 w-10 border-r-4 border-t-4 border-blue-400 sm:right-8 sm:top-8"></div>

                        <div class="absolute bottom-6 left-6 h-10 w-10 border-b-4 border-l-4 border-blue-400 sm:bottom-8 sm:left-8"></div>

                        <div class="absolute bottom-6 right-6 h-10 w-10 border-b-4 border-r-4 border-blue-400 sm:bottom-8 sm:right-8"></div>
                    </div>
                </div>

                {{-- TOMBOL KAMERA --}}
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <button
                        type="button"
                        onclick="startCamera()"
                        class="flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-4 font-black text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700"
                    >
                        <i data-lucide="camera" class="h-5 w-5"></i>
                        Aktifkan Kamera
                    </button>

                    <button
                        type="button"
                        onclick="stopRealtimeScan()"
                        class="flex w-full items-center justify-center gap-2 rounded-2xl bg-red-50 px-5 py-4 font-black text-red-700 transition hover:bg-red-100"
                    >
                        <i data-lucide="square" class="h-5 w-5"></i>
                        Stop Scan
                    </button>
                </div>
            </div>

            {{-- STATUS DI LUAR KAMERA --}}
            <div class="space-y-5">
                <div class="rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                            <i data-lucide="user-round-check" class="h-6 w-6"></i>
                        </div>

                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">
                                Hasil Pendeteksian
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-500">
                                Informasi tampil di luar kamera
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 rounded-[1.5rem] bg-slate-50 p-5">
                        <p class="text-sm font-semibold text-slate-500">
                            Guru Terdeteksi
                        </p>

                        <h2
                            id="detectedTeacher"
                            class="mt-2 break-words text-2xl font-black leading-tight text-slate-900"
                        >
                            Menunggu wajah...
                        </h2>

                        <p
                            id="matchScore"
                            class="mt-3 text-sm font-semibold leading-6 text-slate-500"
                        >
                            Aktifkan kamera untuk mulai scan realtime.
                        </p>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-blue-50 p-4">
                            <p class="text-xs font-bold text-blue-500">
                                Mode Aktif
                            </p>

                            <p class="mt-1 font-black text-blue-900">
                                {{ $mode === 'check_in' ? 'Masuk' : 'Pulang' }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-emerald-50 p-4">
                            <p class="text-xs font-bold text-emerald-500">
                                Kecepatan Scan
                            </p>

                            <p class="mt-1 font-black text-emerald-900">
                                1,5 Detik
                            </p>
                        </div>
                    </div>
                </div>

                {{-- PETUNJUK --}}
                <div class="rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                            <i data-lucide="lightbulb" class="h-5 w-5"></i>
                        </div>

                        <h3 class="font-black text-slate-900">
                            Tips Pendeteksian
                        </h3>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="flex gap-3 rounded-2xl bg-slate-50 p-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-black text-blue-700">
                                1
                            </span>

                            <p class="text-sm font-semibold leading-6 text-slate-600">
                                Posisi wajah berada di tengah bingkai kamera.
                            </p>
                        </div>

                        <div class="flex gap-3 rounded-2xl bg-slate-50 p-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-black text-blue-700">
                                2
                            </span>

                            <p class="text-sm font-semibold leading-6 text-slate-600">
                                Pastikan pencahayaan cukup dan wajah tidak blur.
                            </p>
                        </div>

                        <div class="flex gap-3 rounded-2xl bg-slate-50 p-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-black text-blue-700">
                                3
                            </span>

                            <p class="text-sm font-semibold leading-6 text-slate-600">
                                Hindari lebih dari satu wajah di depan kamera.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- LOG ABSENSI --}}
        <section class="rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-sky-500">
                        Aktivitas Terbaru
                    </p>

                    <h3 class="mt-1 text-xl font-black text-slate-900">
                        Log Absensi
                    </h3>
                </div>

                <div class="rounded-full bg-slate-100 px-3 py-2 text-xs font-bold text-slate-500">
                    Maksimal 10 data
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($logs as $log)
                    <div class="rounded-[1.4rem] border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl
                                    {{ $log['type'] === 'Masuk'
                                        ? 'bg-blue-100 text-blue-600'
                                        : 'bg-sky-100 text-sky-600' }}"
                                >
                                    <i
                                        data-lucide="{{ $log['type'] === 'Masuk' ? 'log-in' : 'log-out' }}"
                                        class="h-5 w-5"
                                    ></i>
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate font-black text-slate-900">
                                        {{ $log['name'] }}
                                    </p>

                                    <p class="mt-1 text-sm font-semibold text-slate-500">
                                        Absen {{ $log['type'] }}
                                    </p>
                                </div>
                            </div>

                            <span class="shrink-0 text-xs font-bold text-slate-400">
                                {{ substr($log['time'], 0, 5) }}
                            </span>
                        </div>

                        <div class="mt-4 flex items-center justify-between rounded-xl bg-white px-3 py-2">
                            <span class="text-xs font-semibold text-slate-500">
                                Transport
                            </span>

                            <span class="text-sm font-black text-emerald-600">
                                Rp{{ number_format($log['transport'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-[1.5rem] bg-slate-50 p-8 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                            <i data-lucide="clipboard-list" class="h-7 w-7"></i>
                        </div>

                        <p class="mt-4 font-black text-slate-700">
                            Belum ada log absensi
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-500">
                            Data akan muncul setelah wajah berhasil dikenali.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

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
        let lastDetectedTeacherId = null;
        let lastDetectedAt = 0;
        let scanBusy = false;

        async function loadAttendanceModels() {
            if (modelsLoaded) return;

            document.getElementById('detectedTeacher').innerText =
                'Memuat model wajah...';

            document.getElementById('matchScore').innerText =
                'Mohon tunggu beberapa saat.';

            await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('/models');

            modelsLoaded = true;
        }

        async function startCamera() {
            attendanceVideo = document.getElementById('camera');

            if (!registeredTeachers.length) {
                showPremiumPopup(
                    'warning',
                    'Belum Ada Data Wajah',
                    'Belum ada guru yang wajahnya diregistrasi.',
                    'Perhatian'
                );

                return;
            }

            try {
                stopCameraTracks();

                await loadAttendanceModels();

                attendanceStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: {
                            ideal: 1280
                        },
                        height: {
                            ideal: 960
                        }
                    },
                    audio: false
                });

                attendanceVideo.srcObject = attendanceStream;

                await attendanceVideo.play();

                document.getElementById('detectedTeacher').innerText =
                    'Kamera aktif';

                document.getElementById('matchScore').innerText =
                    'Arahkan wajah ke tengah bingkai kamera.';

                startRealtimeScan();
            } catch (error) {
                console.error('CAMERA ERROR:', error);

                document.getElementById('detectedTeacher').innerText =
                    'Kamera gagal aktif';

                document.getElementById('matchScore').innerText =
                    'Periksa izin kamera pada perangkat.';

                showPremiumPopup(
                    'warning',
                    'Kamera Tidak Bisa Diakses',
                    'Periksa izin kamera atau file model pengenalan wajah.',
                    'Kamera Gagal'
                );
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
            if (!attendanceStream) return;

            attendanceStream.getTracks().forEach((track) => {
                track.stop();
            });

            attendanceStream = null;

            if (attendanceVideo) {
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

            document.getElementById('detectedTeacher').innerText =
                'Scan dihentikan';

            document.getElementById('matchScore').innerText =
                'Tekan Aktifkan Kamera untuk mulai kembali.';
        }

        async function recognizeFaceRealtime() {
            if (scanBusy) return;

            if (
                !attendanceVideo ||
                attendanceVideo.readyState !== 4
            ) {
                return;
            }

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
                    document.getElementById('detectedTeacher').innerText =
                        'Wajah belum terdeteksi';

                    document.getElementById('matchScore').innerText =
                        'Pastikan wajah jelas dan pencahayaan cukup.';

                    return;
                }

                let bestMatch = null;
                let bestDistance = 999;

                registeredTeachers.forEach((teacher) => {
                    if (!teacher.descriptor) return;

                    const savedDescriptor =
                        new Float32Array(teacher.descriptor);

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
                    document.getElementById('detectedTeacher').innerText =
                        'Wajah tidak dikenali';

                    document.getElementById('matchScore').innerText =
                        'Nilai jarak wajah: ' + bestDistance.toFixed(3);

                    return;
                }

                const now = Date.now();
                const cooldownMs = 20000;

                if (
                    lastDetectedTeacherId === bestMatch.id &&
                    now - lastDetectedAt < cooldownMs
                ) {
                    document.getElementById('detectedTeacher').innerText =
                        bestMatch.name;

                    document.getElementById('matchScore').innerText =
                        'Wajah sudah terdeteksi. Cooldown masih aktif.';

                    return;
                }

                lastDetectedTeacherId = bestMatch.id;
                lastDetectedAt = now;

                const matchPercentage =
                    ((1 - bestDistance) * 100).toFixed(2);

                document.getElementById('detectedTeacher').innerText =
                    bestMatch.name;

                document.getElementById('matchScore').innerText =
                    'Tingkat kecocokan: ' + matchPercentage + '%';

                const result = await @this.call(
                    'saveAttendanceByTeacherId',
                    bestMatch.id
                );

                handleAttendanceResult(result);
            } catch (error) {
                console.error('RECOGNITION ERROR:', error);

                document.getElementById('matchScore').innerText =
                    'Terjadi kesalahan saat mendeteksi wajah.';
            } finally {
                scanBusy = false;
            }
        }

        function handleAttendanceResult(result) {
            if (result.status === 'success' && result.type === 'check_in') {
                speakText(
                    'Selamat datang ' +
                    result.name +
                    '. Absensi masuk berhasil.'
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
                const transportText = result.transport > 0
                    ? 'Anda mendapatkan transport sebesar ' +
                        formatRupiah(result.transport) + '.'
                    : 'Anda tidak mendapatkan transport.';

                speakText(
                    'Terima kasih ' +
                    result.name +
                    '. Absensi pulang berhasil. ' +
                    transportText
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

        function formatRupiah(value) {
            return new Intl.NumberFormat(
                'id-ID',
                {
                    style: 'currency',
                    currency: 'IDR',
                    maximumFractionDigits: 0
                }
            ).format(value);
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

            if (type === 'success') {
                icon.className =
                    'mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 text-emerald-600';

                icon.innerHTML =
                    '<i data-lucide="check-circle-2" class="h-10 w-10"></i>';

                popupBadge.className =
                    'mb-3 inline-flex rounded-full bg-emerald-100 px-4 py-1 text-xs font-bold text-emerald-700';
            }

            if (type === 'checkout') {
                icon.className =
                    'mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-sky-100 text-sky-600';

                icon.innerHTML =
                    '<i data-lucide="log-out" class="h-10 w-10"></i>';

                popupBadge.className =
                    'mb-3 inline-flex rounded-full bg-sky-100 px-4 py-1 text-xs font-bold text-sky-700';
            }

            if (type === 'already') {
                icon.className =
                    'mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-amber-100 text-amber-600';

                icon.innerHTML =
                    '<i data-lucide="shield-alert" class="h-10 w-10"></i>';

                popupBadge.className =
                    'mb-3 inline-flex rounded-full bg-amber-100 px-4 py-1 text-xs font-bold text-amber-700';
            }

            if (type === 'warning') {
                icon.className =
                    'mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-red-100 text-red-600';

                icon.innerHTML =
                    '<i data-lucide="alert-triangle" class="h-10 w-10"></i>';

                popupBadge.className =
                    'mb-3 inline-flex rounded-full bg-red-100 px-4 py-1 text-xs font-bold text-red-700';
            }

            popup.classList.remove('hidden');
            popup.classList.add('flex');

            if (window.lucide) {
                lucide.createIcons();
            }

            setTimeout(() => {
                hidePremiumPopup();
            }, 4500);
        }

        function hidePremiumPopup() {
            const popup = document.getElementById('premiumPopup');

            popup.classList.add('hidden');
            popup.classList.remove('flex');
        }

        window.addEventListener('beforeunload', () => {
            stopCameraTracks();
        });

        document.addEventListener('livewire:navigating', () => {
            stopRealtimeScan();
        });
    </script>
</div>
