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

    <main class="relative z-10 min-h-screen px-4 py-5 flex flex-col max-w-5xl mx-auto">
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

        <section class="relative flex-1 rounded-[2rem] overflow-hidden bg-slate-950 shadow-2xl border border-white/80 min-h-[540px] md:min-h-[620px]">
            <video id="camera" autoplay playsinline muted
                class="absolute inset-0 w-full h-full object-cover"></video>

            <canvas id="faceOverlay"
                class="absolute inset-0 w-full h-full pointer-events-none z-20"></canvas>

            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-slate-950/20 z-10"></div>

            <div class="absolute top-4 left-4 right-4 z-30 flex items-center justify-between">
                <div class="rounded-full bg-black/35 backdrop-blur-xl px-4 py-2 text-white text-xs font-black border border-white/10">
                    LIVE CAMERA
                </div>

                <div id="cameraStatus"
                    class="rounded-full bg-emerald-500/20 backdrop-blur-xl px-4 py-2 text-emerald-100 text-xs font-black border border-emerald-300/20">
                    SIAP SCAN
                </div>
            </div>

            <div class="absolute bottom-5 left-5 right-5 z-30">
                <div class="rounded-[1.5rem] bg-black/35 backdrop-blur-xl border border-white/10 px-5 py-4 text-white">
                    <p class="text-xs font-black tracking-[0.25em] text-blue-200 uppercase">
                        Face Recognition
                    </p>

                    <h3 id="detectedTeacher" class="mt-1 text-xl font-black">
                        Menunggu Kamera
                    </h3>

                    <p id="matchScore" class="text-sm text-white/70">
                        Tekan tombol kamera untuk mulai scan.
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

                setScanText('Kamera Aktif', 'Scan realtime berjalan...');
                setCameraStatus('REALTIME');

                startRealtimeScan();
            } catch (error) {
                console.error(error);
                showPremiumPopup('warning', 'Kamera Gagal', 'Kamera tidak bisa diakses atau model wajah belum tersedia.', 'Error');
                setScanText('Kamera Gagal', 'Pastikan izin kamera aktif dan folder /models tersedia.');
                setCameraStatus('ERROR');
            }
        }

        function startRealtimeScan() {
            if (scanInterval) clearInterval(scanInterval);

            scanInterval = setInterval(async () => {
                await recognizeFaceRealtime();
            }, 800);
        }

        function stopRealtimeScan() {
            if (scanInterval) {
                clearInterval(scanInterval);
                scanInterval = null;
            }

            if (attendanceVideo && attendanceVideo.srcObject) {
                attendanceVideo.srcObject.getTracks().forEach(track => track.stop());
                attendanceVideo.srcObject = null;
            }

            clearFaceOverlay();
            setScanText('Scan Dihentikan', 'Tekan Kamera untuk mulai lagi.');
            setCameraStatus('STOP');
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
                clearFaceOverlay();
                setScanText('Wajah Belum Terdeteksi', 'Pastikan wajah jelas dan cahaya cukup.');
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
                        console.error('Descriptor guru rusak:', teacher.name, error);
                        return;
                    }
                }

                const savedDescriptor = new Float32Array(descriptorData);
                const distance = faceapi.euclideanDistance(detection.descriptor, savedDescriptor);

                if (distance < bestDistance) {
                    bestDistance = distance;
                    bestMatch = teacher;
                }
            });

            if (!bestMatch || bestDistance > 0.45) {
                drawFaceOverlay(detection, null, null);
                setScanText('Tidak Dikenali', 'Wajah terdeteksi, tapi belum cocok dengan data guru.');
                setCameraStatus('BELUM COCOK');
                return;
            }

            drawFaceOverlay(detection, bestMatch.name, bestDistance);

            const now = Date.now();
            const cooldownMs = 2000;

            if (lastDetectedTeacherId === bestMatch.id && now - lastDetectedAt < cooldownMs) {
                setScanText(bestMatch.name, 'Cooldown aktif, absensi tidak dobel.');
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

            const resizedDetection = faceapi.resizeResults(detection, displaySize);
            const box = resizedDetection.detection.box;

            const isDetected = !!name;

            ctx.lineWidth = 4;
            ctx.strokeStyle = isDetected ? '#22c55e' : '#ffffff';
            ctx.shadowColor = isDetected ? 'rgba(34,197,94,0.9)' : 'rgba(255,255,255,0.5)';
            ctx.shadowBlur = 18;
            ctx.strokeRect(box.x, box.y, box.width, box.height);

            if (isDetected) {
                const label = distance
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
        }

        function clearFaceOverlay() {
            const canvas = document.getElementById('faceOverlay');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
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
