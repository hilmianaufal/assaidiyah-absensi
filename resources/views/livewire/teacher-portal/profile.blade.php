<div class="space-y-6">

    <div class="rounded-[2rem] bg-gradient-to-r from-blue-600 to-sky-500 p-8 text-white">
        <h1 class="text-4xl font-black">
            Profile Saya
        </h1>

        <p class="opacity-80">
            Kelola akun dan informasi pribadi.
        </p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-100 text-emerald-700 p-4 rounded-2xl">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-[2rem] bg-white p-6 shadow">

        <div class="grid md:grid-cols-2 gap-6">

            <div>
                <label>Nama</label>

                <input
                    wire:model="name"
                    class="w-full mt-2 rounded-xl border-slate-200"
                >
            </div>

            <div>
                <label>Email Login</label>

                <input
                    wire:model="email"
                    class="w-full mt-2 rounded-xl border-slate-200"
                >
            </div>

            <div>
                <label>No HP</label>

                <input
                    wire:model="phone"
                    class="w-full mt-2 rounded-xl border-slate-200"
                >
            </div>

            <div>
                <label>Foto Profile</label>

                <input
                    type="file"
                    wire:model="photo"
                    class="w-full mt-2"
                >
            </div>

            <div class="md:col-span-2">
                <label>Alamat</label>

                <textarea
                    wire:model="address"
                    rows="3"
                    class="w-full mt-2 rounded-xl border-slate-200"></textarea>
            </div>

            <div class="md:col-span-2">
                <label>Bio Singkat</label>

                <textarea
                    wire:model="bio"
                    rows="4"
                    class="w-full mt-2 rounded-xl border-slate-200"></textarea>
            </div>

        </div>
    </div>

    <div class="rounded-[2rem] bg-white p-6 shadow">

        <h2 class="text-xl font-black mb-4">
            Ganti Password
        </h2>

        <div class="grid md:grid-cols-2 gap-6">

            <div>
                <label>Password Baru</label>

                <input
                    type="password"
                    wire:model="newPassword"
                    class="w-full mt-2 rounded-xl border-slate-200">
            </div>

            <div>
                <label>Konfirmasi Password</label>

                <input
                    type="password"
                    wire:model="newPasswordConfirmation"
                    class="w-full mt-2 rounded-xl border-slate-200">
            </div>

        </div>
    </div>

    <button
        wire:click="save"
        class="px-8 py-4 rounded-2xl bg-blue-600 text-white font-black">

        Simpan Perubahan
    </button>

</div>
