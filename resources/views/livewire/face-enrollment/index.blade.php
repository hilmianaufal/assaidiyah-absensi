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

        @if (session('success'))
            <div class="rounded-2xl bg-emerald-100 text-emerald-700 p-4 font-bold">
                {{ session('success') }}
            </div>
        @endif

        <section class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="relative overflow-hidden rounded-3xl bg-slate-950 aspect-video flex items-center justify-center text-white">
                    <video id="enrollCamera" autoplay playsinline muted
                        class="absolute inset-0 w-full h-full object-cover"></video>

                    <div class="absolute inset-0 pointer-events-none">
                        <div class="absolute inset-8 border-2 border-blue-400/70 rounded-[2rem]"></div>
                        <div class="absolute left-1/2 top-1/2 w-48 h-64 -translate-x-1/2 -translate-y-1/2 border-4 border-white/80 rounded-full shadow-2xl"></div>
                    </div>

                    <div class="absolute bottom-4 left-4 right-4 rounded-2xl bg-white/10 backdrop-blur-xl p-4">
                        <p class="font-bold">Arahkan wajah guru ke kamera</p>
                        <p class="text-sm text-blue-100">
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
                    Pilih guru yang akan diregistrasi wajahnya.
                </p>

                <form wire:submit="saveFace" class="space-y-4">
                    <select wire:model="teacher_id"
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

    <script>
        let enrollVideo = null;
        let enrollModelsLoaded = false;

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
                document.getElementById('faceStatus').innerText = 'Kamera aktif, silakan ambil data wajah';
            } catch (error) {
                console.error(error);
                alert('Kamera tidak bisa diakses atau model belum tersedia.');
            }
        }

        async function captureFaceDescriptor() {
            if (!enrollVideo) {
                alert('Aktifkan kamera dulu.');
                return;
            }

            document.getElementById('faceStatus').innerText = 'Mendeteksi wajah...';

            const detection = await faceapi
                .detectSingleFace(enrollVideo, new faceapi.TinyFaceDetectorOptions({
                    inputSize: 416,
                    scoreThreshold: 0.6
                }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!detection) {
                document.getElementById('faceStatus').innerText = 'Wajah tidak terdeteksi';
                alert('Wajah tidak terdeteksi. Pastikan wajah jelas dan terang.');
                return;
            }

            const descriptor = Array.from(detection.descriptor);

            @this.set('descriptor', JSON.stringify(descriptor));

            document.getElementById('faceStatus').innerText = 'Data wajah berhasil diambil';
            alert('Data wajah berhasil diambil. Klik Simpan Data Wajah.');
        }
    </script>
</div>