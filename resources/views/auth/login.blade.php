<x-guest-layout>
    <div class="w-full max-w-md">
        <div class="mb-6 text-center">
            <div class="flex justify-center mb-6">
                <div
                    class="relative flex h-32 w-32 items-center justify-center rounded-[2.5rem]
                        bg-white shadow-2xl shadow-blue-500/20
                        ring-8 ring-white/60">

                    <div
                        class="absolute inset-0 rounded-[2.5rem]
                            bg-gradient-to-br from-blue-600/10 to-sky-400/10">
                    </div>

                    <img src="{{ asset('logo.jpg') }}"
                        alt="Logo Assaidiyyah"
                        class="relative h-20 w-20 object-contain">
                </div>
            </div>

            <h1 class="mt-5 text-3xl font-black tracking-tight text-slate-950">
               Ponpes Assaidiyyah
            </h1>

            <p class="mt-2 text-sm font-semibold text-slate-500">
                Sistem Absensi Guru
            </p>
        </div>

        <div class="rounded-[2rem] border border-white/70 bg-white/90 p-6 shadow-2xl shadow-blue-900/10 backdrop-blur-xl">
            <div class="mb-6">
                <p class="text-sm font-black uppercase tracking-[0.25em] text-blue-600">
                    Welcome Back
                </p>
                <h2 class="mt-2 text-2xl font-black text-slate-950">
                    Login Akun
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Masuk sebagai admin atau guru untuk mengakses sistem.
                </p>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-bold text-slate-700">
                        Email
                    </label>

                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-4 top-3.5 h-5 w-5 text-slate-400"></i>

                        <input id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="nama@email.com"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 font-semibold text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </div>

                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-bold text-slate-700">
                        Password
                    </label>

                    <div class="relative">
                        <i data-lucide="lock-keyhole" class="absolute left-4 top-3.5 h-5 w-5 text-slate-400"></i>

                        <input id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 font-semibold text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </div>

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between">
                    <label for="remember_me" class="inline-flex items-center gap-2">
                        <input id="remember_me"
                            type="checkbox"
                            name="remember"
                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                        <span class="text-sm font-semibold text-slate-500">
                            Ingat saya
                        </span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="text-sm font-bold text-blue-600 hover:text-blue-700">
                            Lupa password?
                        </a>
                    @endif
                </div>

                <button type="submit"
                    class="group flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-700 to-sky-500 px-5 py-3.5 font-black text-white shadow-xl shadow-blue-500/30 transition hover:-translate-y-0.5 hover:shadow-2xl">
                    Masuk Sekarang
                    <i data-lucide="arrow-right" class="h-5 w-5 transition group-hover:translate-x-1"></i>
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs font-semibold text-slate-500">
            © {{ date('Y') }} Assaidiyyah Face Attendance System
        </p>
    </div>
</x-guest-layout>
