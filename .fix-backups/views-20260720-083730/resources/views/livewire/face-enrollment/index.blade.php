<div>
    <x-slot name="header">
        Registrasi Wajah
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <p class="text-blue-100 text-sm">Face Enrollment</p>
            <h1 class="text-2xl lg:text-3xl font-extrabold">Registrasi Wajah Guru</h1>
            <p class="text-blue-50 mt-2">
                Ambil data wajah guru sebagai dasar pengenalan saat absensi.
            </p>
        </section>

        <section class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="relative overflow-hidden rounded-3xl bg-slate-950 aspect-video flex items-center justify-center text-white">
                    <video id="enrollCamera" autoplay playsinline muted
                        class="absolute inset-0 w-full h-full object-cover"></video>

                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-slate-950/30 pointer-events-none"></div>

                    <div class="absolute top-4 left-4 right-4 flex items-center justify-between gap-3">
                        <div class="rounded-2xl bg-emerald-500/90 px-4 py-2 font-black text-sm shadow">
                            AI Face Recognition
                        </div>

                        <div id="teacherOverlay"
                            class="rounded-2xl bg-white/15 backdrop-blur-xl px-4 py-2 font-bold text-sm">
                            Pilih guru terlebih dahulu
                        </div>
                    </div>

                    <div class="absolute bottom-4 left-4 right-4 rounded-2xl bg-white/10 backdrop-blur-xl p-4">
                        <p class="font-bold">Kamera bersih tanpa marker</p>
                        <p id="cameraHint" class="text-sm text-blue-100">
                            Pastikan wajah terang, tidak blur, dan menghadap depan.
                        </p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-3 mt-5">
                    <button type="button" onclick="startEnrollCamera()"
                        class="w-full px-5 py-3 rounded-2xl bg-blue-600 text-white font-bold shadow">
                        Aktifkan Kamera
                    </button>

                    <button type="button" onclick="captureFaceDescriptor()"
                        class="w-full px-5 py-3 rounded-2xl bg-sky-100 text-sky-700 font-bold">
                        Ambil Data Wajah
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <h3 class="text-xl font-extrabold mb-2">Pilih Guru</h3>
                <p class="text-sm text-slate-500 mb-5">
                    Nama guru akan muncul langsung di dalam kamera.
                </p>

                <form wire:submit="saveFace" class="space-y-4">
                    <select wire:model="teacher_id" id="teacherSelect"
                        class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih Guru</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}">
                                {{ $teacher->name }}
                                {{ $teacher->face_descriptor ? '(Sudah Terdaftar)' : '(Belum)' }}
                            </option>
                        @endforeach
                    </select>

                    @error('teacher_id')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    @error('descriptor')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="rounded-2xl bg-blue-50 p-4">
                        <p class="text-sm text-slate-500">Status Pengambilan</p>
                        <p id="faceStatus" class="font-bold text-blue-700">
                            Belum mengambil data wajah
                        </p>
                    </div>

                    <button type="submit"
                        class="w-full px-5 py-3 rounded-2xl bg-blue-600 text-white font-bold shadow">
                        Simpan Data Wajah
                    </button>
                </form>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let enrollVideo = null;
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

        document.addEventListener('change', function (e) {
            if (e.target.id !== 'teacherSelect') return;

            const selected = e.target.options[e.target.selectedIndex]?.text || 'Pilih guru terlebih dahulu';

            document.getElementById('teacherOverlay').innerText = selected;
        });

        async function loadFaceModels() {
            if (enrollModelsLoaded) return;

            document.getElementById('faceStatus').innerText = 'Memuat model wajah...';

            await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('/models');

            enrollModelsLoaded = true;
            document.getElementById('faceStatus').innerText = 'Model wajah siap';
        }

        async function startEnrollCamera() {
            enrollVideo = document.getElementById('enrollCamera');

            try {
                await loadFaceModels();

                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                });

                enrollVideo.srcObject = stream;

                document.getElementById('faceStatus').innerText = 'Kamera aktif';
                document.getElementById('cameraHint').innerText = 'Silakan ambil data wajah guru.';

                speakAI('Kamera aktif. Silakan ambil data wajah.');

                Swal.fire({
                    icon: 'success',
                    title: 'Kamera aktif',
                    timer: 1200,
                    showConfirmButton: false
                });
            } catch (error) {
                console.error(error);

                Swal.fire({
                    icon: 'error',
                    title: 'Kamera gagal aktif',
                    text: 'Periksa izin kamera atau file model wajah.'
                });
            }
        }

        async function captureFaceDescriptor() {
            if (captureCooldown) return;

            captureCooldown = true;
            setTimeout(() => {
                captureCooldown = false;
            }, 1000);

            if (!enrollVideo) {
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
                    text: 'Nama guru wajib dipilih sebelum ambil data wajah.'
                });
                return;
            }

            document.getElementById('faceStatus').innerText = 'AI sedang mendeteksi wajah...';
            document.getElementById('cameraHint').innerText = 'AI sedang membaca pola wajah.';

            const detection = await faceapi
                .detectSingleFace(enrollVideo, new faceapi.TinyFaceDetectorOptions({
                    inputSize: 416,
                    scoreThreshold: 0.6
                }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!detection) {
                document.getElementById('faceStatus').innerText = 'Wajah tidak terdeteksi';
                document.getElementById('cameraHint').innerText = 'Pastikan wajah terang dan menghadap kamera.';

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

            const teacherName = teacherSelect.options[teacherSelect.selectedIndex].text
                .replace('(Sudah Terdaftar)', '')
                .replace('(Belum)', '')
                .trim();

            document.getElementById('teacherOverlay').innerText = 'Terdeteksi: ' + teacherName;
            document.getElementById('faceStatus').innerText = 'Data wajah berhasil diambil';
            document.getElementById('cameraHint').innerText = 'Klik Simpan Data Wajah untuk menyimpan.';

            speakAI('Data wajah ' + teacherName + ' berhasil direkam.');

            Swal.fire({
                icon: 'success',
                title: 'Wajah berhasil direkam',
                text: teacherName,
                timer: 1500,
                showConfirmButton: false
            });
        }
    </script>
</div>
