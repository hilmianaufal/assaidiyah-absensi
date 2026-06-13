<div class="min-h-screen bg-[#eef6ff] text-slate-900 overflow-hidden">
    <div class="fixed inset-0 pointer-events-none">
        <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-blue-400/30 blur-3xl"></div>
        <div class="absolute top-1/2 -left-24 w-72 h-72 rounded-full bg-sky-300/30 blur-3xl"></div>
    </div>

    <div id="premiumPopup"
        class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-5">
        <div class="w-full max-w-sm rounded-[2rem] bg-white p-6 shadow-2xl text-center">
            <div id="popupIcon"
                class="mx-auto mb-4 w-20 h-20 rounded-full flex items-center justify-center bg-emerald-100 text-emerald-600">
                <i data-lucide="check-circle-2" class="w-10 h-10"></i>
            </div>

            <p id="popupBadge"
                class="inline-flex mb-3 px-4 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-700">
                Berhasil
            </p>

            <h2 id="popupTitle" class="text-2xl font-black text-slate-900">
                Absensi Berhasil
            </h2>

            <p id="popupMessage" class="mt-2 text-sm text-slate-500">
                Data absensi berhasil disimpan.
            </p>
        </div>
    </div>

    <main class="relative z-10 min-h-screen px-4 py-5 flex flex-col">
        <header class="mb-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-blue-500">
                        Assaidiyyah
                    </p>
                    <h1 class="text-2xl font-black tracking-tight text-slate-950">
                        Scan Wajah Guru
                    </h1>
                    <p class="text-sm text-slate-500">
                        Absensi masuk & pulang realtime
                    </p>
                </div>

                <div class="w-14 h-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <i data-lucide="scan-face" class="w-7 h-7"></i>
                </div>
            </div>
        </header>

        <section class="mb-4 grid grid-cols-2 gap-3">
            <button wire:click="setMode('check_in')" type="button"
                class="rounded-3xl px-4 py-4 font-black text-sm shadow-sm border transition
                {{ $mode === 'check_in'
                    ? 'bg-blue-600 text-white border-blue-600 shadow-blue-500/30'
                    : 'bg-white text-slate-500 border-white' }}">
                <div class="flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-5 h-5"></i>
                    MASUK
                </div>
            </button>

            <button wire:click="setMode('check_out')" type="button"
                class="rounded-3xl px-4 py-4 font-black text-sm shadow-sm border transition
                {{ $mode === 'check_out'
                    ? 'bg-sky-600 text-white border-sky-600 shadow-sky-500/30'
                    : 'bg-white text-slate-500 border-white' }}">
                <div class="flex items-center justify-center gap-2">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                    PULANG
                </div>
            </button>
        </section>

        <section class="relative flex-1 rounded-[2.2rem] overflow-hidden bg-slate-950 shadow-2xl border-[6px] border-white min-h-[430px]">
            <video id="camera" autoplay playsinline muted
                class="absolute inset-0 w-full h-full object-cover"></video>

            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/10 to-slate-950/10"></div>

            <div class="absolute top-5 left-5 right-5 flex items-center justify-between">
                <div class="rounded-full bg-white/15 backdrop-blur-xl px-4 py-2 text-white text-xs font-bold border border-white/20">
                    LIVE CAMERA
                </div>

                <div class="rounded-full bg-emerald-400/20 backdrop-blur-xl px-4 py-2 text-emerald-100 text-xs font-bold border border-emerald-300/20">
                    REALTIME
                </div>
            </div>

            <div
                class="absolute left-1/2 top-[43%] w-[210px] h-[270px] -translate-x-1/2 -translate-y-1/2 border-[5px] border-white rounded-full shadow-[0_0_50px_rgba(59,130,246,0.8)]">
            </div>

            <div class="absolute left-1/2 top-[43%] w-[250px] h-[310px] -translate-x-1/2 -translate-y-1/2 rounded-full border border-blue-300/40"></div>

            <div class="absolute bottom-0 left-0 right-0 p-5">
                <div class="rounded-[2rem] bg-white/15 backdrop-blur-2xl border border-white/20 p-5 text-white">
<p class="text-[10px] font-bold text-blue-100 uppercase tracking-[0.15em]">
    Guru Terdeteksi
</p>

<h2 id="detectedTeacher" class="mt-1 text-xl font-black leading-tight">
    Menunggu Wajah
</h2>

<p id="matchScore" class="mt-1 text-xs text-blue-100">
    Aktifkan kamera untuk mulai scan
</p>
                </div>
            </div>
        </section>

        <section class="mt-4 grid grid-cols-2 gap-3">
            <button onclick="startCamera()" type="button"
                class="rounded-3xl bg-blue-600 text-white py-4 font-black shadow-xl shadow-blue-500/30">
                <div class="flex items-center justify-center gap-2">
                    <i data-lucide="camera" class="w-5 h-5"></i>
                    Kamera
                </div>
            </button>

            <button onclick="stopRealtimeScan()" type="button"
                class="rounded-3xl bg-white text-red-600 py-4 font-black shadow-sm">
                <div class="flex items-center justify-center gap-2">
                    <i data-lucide="pause-circle" class="w-5 h-5"></i>
                    Stop
                </div>
            </button>
        </section>
    </main>

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

            document.getElementById('detectedTeacher').innerText = 'Memuat Model';
            document.getElementById('matchScore').innerText = 'Mohon tunggu sebentar...';

            await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('/models');

            modelsLoaded = true;
        }

        async function startCamera() {
            attendanceVideo = document.getElementById('camera');

            if (!registeredTeachers.length) {
                showPremiumPopup('warning', 'Belum Ada Data Wajah', 'Silakan registrasi wajah guru terlebih dahulu.', 'Perhatian');
                speakText('Belum ada data wajah guru yang diregistrasi.');
                return;
            }

            try {
                await loadAttendanceModels();

                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                });

                attendanceVideo.srcObject = stream;

                document.getElementById('detectedTeacher').innerText = 'Kamera Aktif';
                document.getElementById('matchScore').innerText = 'Scan realtime berjalan...';

                startRealtimeScan();
            } catch (error) {
                console.error(error);
                showPremiumPopup('warning', 'Kamera Gagal', 'Kamera tidak bisa diakses atau model wajah belum tersedia.', 'Error');
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

            document.getElementById('detectedTeacher').innerText = 'Scan Dihentikan';
            document.getElementById('matchScore').innerText = 'Tekan Kamera untuk mulai lagi.';
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
                document.getElementById('detectedTeacher').innerText = 'Wajah Belum Terdeteksi';
                document.getElementById('matchScore').innerText = 'Pastikan wajah jelas dan cahaya cukup.';
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
                document.getElementById('detectedTeacher').innerText = 'Tidak Dikenali';
                document.getElementById('matchScore').innerText = 'Skor terlalu rendah: ' + bestDistance.toFixed(3);
                return;
            }

            const now = Date.now();
            const cooldownMs = 20000;

            if (lastDetectedTeacherId === bestMatch.id && now - lastDetectedAt < cooldownMs) {
                document.getElementById('detectedTeacher').innerText = bestMatch.name;
                document.getElementById('matchScore').innerText = 'Cooldown aktif, absensi tidak dobel.';
                return;
            }

            lastDetectedTeacherId = bestMatch.id;
            lastDetectedAt = now;

            document.getElementById('detectedTeacher').innerText = bestMatch.name;
            document.getElementById('matchScore').innerText =
                'Kecocokan: ' + ((1 - bestDistance) * 100).toFixed(2) + '%';

            const photoBase64 = captureCameraPhoto();

            const result = await @this.call(
                'saveAttendanceByTeacherId',
                bestMatch.id,
                photoBase64
            );

            if (result.status === 'success' && result.type === 'check_in') {
                const transportText = result.transport > 0
                    ? 'Anda mendapatkan transport sepuluh ribu rupiah.'
                    : 'Anda tidak mendapatkan transport.';

                speakText('Selamat datang ' + result.name + '. Absensi masuk berhasil. ' + transportText);
                showPremiumPopup('success', 'Absen Masuk Berhasil', result.name + ' - ' + result.message, 'Masuk');
            }

            if (result.status === 'success' && result.type === 'check_out') {
                speakText('Terima kasih ' + result.name + '. Absensi pulang berhasil. Sampai jumpa.');
                showPremiumPopup('checkout', 'Absen Pulang Berhasil', result.name + ' - ' + result.message, 'Pulang');
            }

            if (result.status === 'already') {
                speakText(result.name + ' sudah melakukan absensi hari ini.');
                showPremiumPopup('already', 'Sudah Absen', result.name + ' - ' + result.message, 'Duplikat Dicegah');
            }

            if (result.status === 'no_check_in') {
                speakText(result.name + ' belum absen masuk hari ini.');
                showPremiumPopup('warning', 'Belum Absen Masuk', result.name + ' - ' + result.message, 'Perhatian');
            }
        }

        function captureCameraPhoto() {
            const video = document.getElementById('camera');

            if (!video || video.readyState !== 4) return null;

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            return canvas.toDataURL('image/jpeg', 0.85);
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
                icon.className = 'mx-auto mb-4 w-20 h-20 rounded-full flex items-center justify-center bg-emerald-100 text-emerald-600';
                icon.innerHTML = '<i data-lucide="check-circle-2" class="w-10 h-10"></i>';
                popupBadge.className = 'inline-flex mb-3 px-4 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-700';
            }

            if (type === 'checkout') {
                icon.className = 'mx-auto mb-4 w-20 h-20 rounded-full flex items-center justify-center bg-sky-100 text-sky-600';
                icon.innerHTML = '<i data-lucide="log-out" class="w-10 h-10"></i>';
                popupBadge.className = 'inline-flex mb-3 px-4 py-1 rounded-full text-xs font-black bg-sky-100 text-sky-700';
            }

            if (type === 'already') {
                icon.className = 'mx-auto mb-4 w-20 h-20 rounded-full flex items-center justify-center bg-amber-100 text-amber-600';
                icon.innerHTML = '<i data-lucide="shield-alert" class="w-10 h-10"></i>';
                popupBadge.className = 'inline-flex mb-3 px-4 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-700';
            }

            if (type === 'warning') {
                icon.className = 'mx-auto mb-4 w-20 h-20 rounded-full flex items-center justify-center bg-red-100 text-red-600';
                icon.innerHTML = '<i data-lucide="alert-triangle" class="w-10 h-10"></i>';
                popupBadge.className = 'inline-flex mb-3 px-4 py-1 rounded-full text-xs font-black bg-red-100 text-red-700';
            }

            popup.classList.remove('hidden');

            if (window.lucide) {
                lucide.createIcons();
            }

            setTimeout(() => hidePremiumPopup(), 4500);
        }

        function hidePremiumPopup() {
            document.getElementById('premiumPopup').classList.add('hidden');
        }
    </script>
</div>