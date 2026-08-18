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

                <form onsubmit="event.preventDefault(); saveFaceHttp();" class="space-y-4">
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

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 font-bold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let enrollVideo = null;
        let enrollStream = null;
        let enrollModelsLoaded = false;
        let captureCooldown = false;
        let capturedDescriptor = null;

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

                capturedDescriptor = descriptor;

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


        async function saveCapturedFace() {
            const teacherSelect =
                document.getElementById('teacherSelect');

            if (!teacherSelect || !teacherSelect.value) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih guru dulu',
                    text: 'Silakan pilih guru terlebih dahulu.'
                });

                return;
            }

            if (
                !Array.isArray(capturedDescriptor) ||
                capturedDescriptor.length !== 128
            ) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data wajah belum diambil',
                    text: 'Klik Ambil Data Wajah terlebih dahulu.'
                });

                return;
            }

            const teacherId = Number(
                teacherSelect.value
            );

            const statusElement =
                document.getElementById('faceStatus');

            statusElement.innerText =
                'Menyimpan data wajah...';

            try {
                const result = await @this.call(
                    'saveFaceDescriptor',
                    teacherId,
                    capturedDescriptor
                );

                if (
                    !result ||
                    result.status !== 'success'
                ) {
                    throw new Error(
                        result?.message ||
                        'Gagal menyimpan data wajah.'
                    );
                }

                /*
                 * Setelah Livewire render, ambil ulang
                 * element select dari DOM.
                 */
                const freshSelect =
                    document.getElementById(
                        'teacherSelect'
                    );

                if (freshSelect) {
                    freshSelect.value =
                        String(result.teacher_id);

                    const option = Array.from(
                        freshSelect.options
                    ).find(
                        item =>
                            item.value ===
                            String(result.teacher_id)
                    );

                    if (option) {
                        option.textContent =
                            result.name +
                            ' (Sudah Terdaftar)';
                    }
                }

                const freshStatus =
                    document.getElementById(
                        'faceStatus'
                    );

                if (freshStatus) {
                    freshStatus.innerText =
                        '✓ Wajah sudah terdaftar';

                    freshStatus.classList.remove(
                        'text-blue-700'
                    );

                    freshStatus.classList.add(
                        'text-emerald-600'
                    );
                }

                const overlay =
                    document.getElementById(
                        'teacherOverlay'
                    );

                if (overlay) {
                    overlay.innerText =
                        'Terdaftar: ' + result.name;
                }

                capturedDescriptor = null;

                speakAI(
                    'Data wajah ' +
                    result.name +
                    ' berhasil disimpan.'
                );

                Swal.fire({
                    icon: 'success',
                    title: 'Registrasi Berhasil',
                    text:
                        result.name +
                        ' sudah terdaftar.',
                    timer: 1800,
                    showConfirmButton: false
                });

            } catch (error) {
                console.error(
                    'SAVE FACE ERROR:',
                    error
                );

                const freshStatus =
                    document.getElementById(
                        'faceStatus'
                    );

                if (freshStatus) {
                    freshStatus.innerText =
                        'Gagal menyimpan data wajah';
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal menyimpan',
                    text:
                        error.message ||
                        'Terjadi kesalahan.'
                });
            }
        }

        
        async function saveFaceHttp() {
            const teacherSelect =
                document.getElementById('teacherSelect');

            if (
                !teacherSelect ||
                !teacherSelect.value
            ) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Guru belum dipilih',
                    text: 'Silakan pilih guru terlebih dahulu.'
                });

                return;
            }

            if (
                !Array.isArray(capturedDescriptor) ||
                capturedDescriptor.length !== 128
            ) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data wajah belum tersedia',
                    text: 'Klik Ambil Data Wajah terlebih dahulu.'
                });

                return;
            }

            const status =
                document.getElementById('faceStatus');

            if (status) {
                status.innerText =
                    'Menyimpan data wajah...';
            }

            try {
                const response = await fetch(
                    '{{ route("face-enrollment.save") }}',
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN':
                                '{{ csrf_token() }}'
                        },

                        body: JSON.stringify({
                            teacher_id:
                                Number(
                                    teacherSelect.value
                                ),

                            descriptor:
                                capturedDescriptor
                        })
                    }
                );

                const result =
                    await response.json();

                if (!response.ok) {
                    console.error(
                        'FACE SAVE RESPONSE:',
                        result
                    );

                    throw new Error(
                        result.message ||
                        'Gagal menyimpan data wajah.'
                    );
                }

                if (
                    result.status !== 'success' ||
                    result.descriptor_count !== 128
                ) {
                    throw new Error(
                        result.message ||
                        'Descriptor tidak valid.'
                    );
                }

                if (status) {
                    status.innerText =
                        '✓ Wajah sudah terdaftar';

                    status.classList.remove(
                        'text-blue-700'
                    );

                    status.classList.add(
                        'text-emerald-600'
                    );
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Registrasi Berhasil',
                    text:
                        result.teacher_name +
                        ' berhasil diregistrasi.',
                    timer: 1500,
                    showConfirmButton: false
                });

                /*
                 * Reload supaya dropdown membaca langsung
                 * kondisi face_descriptor dari database.
                 */
                setTimeout(() => {
                    window.location.reload();
                }, 700);

            } catch (error) {
                console.error(
                    'FACE SAVE ERROR:',
                    error
                );

                if (status) {
                    status.innerText =
                        'Gagal menyimpan wajah';
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text:
                        error.message ||
                        'Terjadi kesalahan.'
                });
            }
        }

window.addEventListener('beforeunload', stopEnrollCamera);
        document.addEventListener('livewire:navigating', stopEnrollCamera);
    </script>
</div>