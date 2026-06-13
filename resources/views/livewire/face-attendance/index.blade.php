<div>
    <x-slot name="header">
        Absensi Wajah Realtime
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <p class="text-blue-100 text-sm">Realtime Face Recognition</p>
            <h1 class="text-2xl lg:text-3xl font-extrabold">Absensi Wajah Guru</h1>
            <p class="text-blue-50 mt-2">
                Kamera otomatis mengenali wajah, mencatat jam masuk dan jam pulang, menampilkan popup, log, dan suara AI.
            </p>
        </section>

        <div id="premiumPopup"
            class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4">
            <div class="w-full max-w-md rounded-[2rem] bg-white p-6 shadow-2xl border border-white/60 text-center">
                <div id="popupIcon"
                    class="mx-auto mb-4 w-20 h-20 rounded-full flex items-center justify-center bg-emerald-100 text-emerald-600">
                    <i data-lucide="check-circle-2" class="w-10 h-10"></i>
                </div>

                <p id="popupBadge"
                    class="inline-flex mb-3 px-4 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                    Berhasil
                </p>

                <h2 id="popupTitle" class="text-2xl font-extrabold text-slate-900">
                    Absensi Berhasil
                </h2>

                <p id="popupMessage" class="mt-2 text-slate-500">
                    Data absensi berhasil disimpan.
                </p>

                <button type="button" onclick="hidePremiumPopup()"
                    class="mt-6 w-full px-5 py-3 rounded-2xl bg-blue-600 text-white font-bold shadow">
                    Oke
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <button wire:click="setMode('check_in')" type="button"
                class="px-5 py-3 rounded-2xl font-bold {{ $mode === 'check_in' ? 'bg-blue-600 text-white shadow' : 'bg-white text-slate-600 border border-slate-200' }}">
                Absen Masuk
            </button>

            <button wire:click="setMode('check_out')" type="button"
                class="px-5 py-3 rounded-2xl font-bold {{ $mode === 'check_out' ? 'bg-sky-600 text-white shadow' : 'bg-white text-slate-600 border border-slate-200' }}">
                Absen Pulang
            </button>
        </div>

        <section class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="relative overflow-hidden rounded-3xl bg-slate-950 aspect-video text-white">
                    <video id="camera" autoplay playsinline muted
                        class="absolute inset-0 w-full h-full object-cover"></video>

                    <div class="absolute inset-0 pointer-events-none">
                        <div class="absolute inset-8 border-2 border-blue-400/70 rounded-[2rem]"></div>
                        <div class="absolute left-1/2 top-1/2 w-48 h-64 -translate-x-1/2 -translate-y-1/2 border-4 border-white/80 rounded-full shadow-2xl"></div>
                    </div>

                    <div
                        class="absolute left-4 right-4 bottom-4 rounded-3xl bg-white/15 backdrop-blur-xl p-4 border border-white/20">
                        <p class="text-sm text-blue-100">Guru Terdeteksi</p>
                        <h2 id="detectedTeacher" class="text-2xl font-extrabold">
                            Menunggu wajah...
                        </h2>
                        <p id="matchScore" class="text-sm text-blue-100">
                            Aktifkan kamera untuk mulai scan realtime.
                        </p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-3 mt-5">
                    <button type="button" onclick="startCamera()"
                        class="w-full px-5 py-3 rounded-2xl bg-blue-600 text-white font-bold shadow">
                        Aktifkan Kamera
                    </button>

                    <button type="button" onclick="stopRealtimeScan()"
                        class="w-full px-5 py-3 rounded-2xl bg-red-100 text-red-700 font-bold">
                        Stop Scan
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <h3 class="text-xl font-extrabold mb-4">Log Absensi</h3>

                <div class="space-y-3">
                    @forelse ($logs as $log)
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <div class="flex items-center justify-between">
                                <p class="font-bold">{{ $log['name'] }}</p>
                                <span class="text-xs text-slate-500">
                                    {{ $log['time'] }}
                                </span>
                            </div>

                            <p class="text-sm text-slate-500 mt-1">
                                {{ $log['type'] }} -
                                Rp{{ number_format($log['transport'], 0, ',', '.') }}
                            </p>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-slate-50 p-5 text-center text-slate-500">
                            Belum ada log absensi.
                        </div>
                    @endforelse
                </div>
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
        let modelsLoaded = false;
        let scanInterval = null;
        let lastDetectedTeacherId = null;
        let lastDetectedAt = 0;

        async function loadAttendanceModels() {
            if (modelsLoaded) return;

            await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('/models');

            modelsLoaded = true;
        }

        async function startCamera() {
            attendanceVideo = document.getElementById('camera');

            if (!registeredTeachers.length) {
                alert('Belum ada guru yang wajahnya diregistrasi.');
                return;
            }

            try {
                await loadAttendanceModels();

                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: {
                            ideal: 1280
                        },
                        height: {
                            ideal: 720
                        }
                    },
                    audio: false
                });

                attendanceVideo.srcObject = stream;

                document.getElementById('detectedTeacher').innerText = 'Kamera aktif';
                document.getElementById('matchScore').innerText = 'Scan realtime berjalan...';

                startRealtimeScan();
            } catch (error) {
                console.error(error);
                alert('Kamera tidak bisa diakses atau model wajah belum tersedia.');
            }
        }

        function startRealtimeScan() {
            if (scanInterval) clearInterval(scanInterval);

            scanInterval = setInterval(async () => {
                await recognizeFaceRealtime();
            }, 1500);
        }

        function stopRealtimeScan() {
            if (scanInterval) {
                clearInterval(scanInterval);
                scanInterval = null;
            }

            document.getElementById('detectedTeacher').innerText = 'Scan dihentikan';
            document.getElementById('matchScore').innerText = 'Tekan Aktifkan Kamera untuk mulai lagi.';
        }

        async function recognizeFaceRealtime() {
            if (!attendanceVideo || attendanceVideo.readyState !== 4) return;

            const detection = await faceapi
                .detectSingleFace(attendanceVideo, new faceapi.TinyFaceDetectorOptions({
                    inputSize: 416,
                    scoreThreshold: 0.6
                }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!detection) {
                document.getElementById('detectedTeacher').innerText = 'Wajah belum terdeteksi';
                document.getElementById('matchScore').innerText = 'Pastikan wajah jelas dan pencahayaan cukup.';
                return;
            }

            let bestMatch = null;
            let bestDistance = 999;

            registeredTeachers.forEach((teacher) => {
                if (!teacher.descriptor) return;

                const savedDescriptor = new Float32Array(teacher.descriptor);
                const distance = faceapi.euclideanDistance(detection.descriptor, savedDescriptor);

                if (distance < bestDistance) {
                    bestDistance = distance;
                    bestMatch = teacher;
                }
            });

            if (!bestMatch || bestDistance > 0.45) {
                document.getElementById('detectedTeacher').innerText = 'Tidak dikenali';
                document.getElementById('matchScore').innerText =
                    'Skor terlalu rendah: ' + bestDistance.toFixed(3);
                return;
            }

            const now = Date.now();
            const cooldownMs = 20000;

            if (
                lastDetectedTeacherId === bestMatch.id &&
                now - lastDetectedAt < cooldownMs
            ) {
                document.getElementById('detectedTeacher').innerText = bestMatch.name;
                document.getElementById('matchScore').innerText = 'Sudah terdeteksi, cooldown aktif...';
                return;
            }

            lastDetectedTeacherId = bestMatch.id;
            lastDetectedAt = now;

            document.getElementById('detectedTeacher').innerText = bestMatch.name;
            document.getElementById('matchScore').innerText =
                'Kecocokan: ' + ((1 - bestDistance) * 100).toFixed(2) + '%';

            const result = await @this.call('saveAttendanceByTeacherId', bestMatch.id);

            if (result.status === 'success' && result.type === 'check_in') {
                const transportText = result.transport > 0 ?
                    'Anda mendapatkan transport sepuluh ribu rupiah.' :
                    'Anda tidak mendapatkan transport.';

                speakText(
                    'Selamat datang ' +
                    result.name +
                    '. Absensi masuk berhasil. ' +
                    transportText
                );

                showPremiumPopup(
                    'success',
                    'Absen Masuk Berhasil',
                    result.message,
                    'Masuk'
                );
            }

            if (result.status === 'success' && result.type === 'check_out') {
                speakText(
                    'Terima kasih ' +
                    result.name +
                    '. Absensi pulang berhasil. Sampai jumpa.'
                );

                showPremiumPopup(
                    'checkout',
                    'Absen Pulang Berhasil',
                    result.message,
                    'Pulang'
                );
            }

            if (result.status === 'already') {
                speakText(
                    result.name +
                    ' sudah melakukan absensi ' +
                    (result.type === 'check_in' ? 'masuk' : 'pulang') +
                    ' hari ini.'
                );

                showPremiumPopup(
                    'already',
                    'Sudah Absen',
                    result.message,
                    'Duplikat Dicegah'
                );
            }

            if (result.status === 'no_check_in') {
                speakText(result.name + ' belum absen masuk hari ini.');

                showPremiumPopup(
                    'warning',
                    'Belum Absen Masuk',
                    result.message,
                    'Perhatian'
                );
            }
        }

        function speakText(text) {
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
                    'mx-auto mb-4 w-20 h-20 rounded-full flex items-center justify-center bg-emerald-100 text-emerald-600';
                icon.innerHTML = '<i data-lucide="check-circle-2" class="w-10 h-10"></i>';

                popupBadge.className =
                    'inline-flex mb-3 px-4 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700';
            }

            if (type === 'checkout') {
                icon.className =
                    'mx-auto mb-4 w-20 h-20 rounded-full flex items-center justify-center bg-sky-100 text-sky-600';
                icon.innerHTML = '<i data-lucide="log-out" class="w-10 h-10"></i>';

                popupBadge.className =
                    'inline-flex mb-3 px-4 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-700';
            }

            if (type === 'already') {
                icon.className =
                    'mx-auto mb-4 w-20 h-20 rounded-full flex items-center justify-center bg-amber-100 text-amber-600';
                icon.innerHTML = '<i data-lucide="shield-alert" class="w-10 h-10"></i>';

                popupBadge.className =
                    'inline-flex mb-3 px-4 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700';
            }

            if (type === 'warning') {
                icon.className =
                    'mx-auto mb-4 w-20 h-20 rounded-full flex items-center justify-center bg-red-100 text-red-600';
                icon.innerHTML = '<i data-lucide="alert-triangle" class="w-10 h-10"></i>';

                popupBadge.className =
                    'inline-flex mb-3 px-4 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700';
            }

            popup.classList.remove('hidden');

            if (window.lucide) {
                lucide.createIcons();
            }

            setTimeout(() => {
                hidePremiumPopup();
            }, 4500);
        }

        function hidePremiumPopup() {
            document.getElementById('premiumPopup').classList.add('hidden');
        }
    </script>
</div>