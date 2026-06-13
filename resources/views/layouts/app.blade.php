<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Absensi Assaidiyyah') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="min-h-screen flex">

        <aside class="hidden lg:flex w-72 flex-col bg-gradient-to-b from-blue-700 via-blue-600 to-sky-500 text-white p-6">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center">
                    <i data-lucide="scan-face" class="w-7 h-7"></i>
                </div>

                <div>
                    <h1 class="font-black text-lg">Assaidiyyah</h1>
                    <p class="text-xs text-blue-100">Face Attendance</p>
                </div>
            </div>

            <nav class="space-y-2 overflow-y-auto pr-1">

                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('dashboard') ? 'bg-white/15' : '' }}">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        Dashboard
                    </a>

                    <a href="{{ route('face-attendance.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('face-attendance.index') ? 'bg-white/15' : '' }}">
                        <i data-lucide="scan-face" class="w-5 h-5"></i>
                        Absensi Wajah
                    </a>

                    <a href="{{ route('additional-honors.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('additional-honors.index') ? 'bg-white/15' : '' }}">
                            <i data-lucide="badge-dollar-sign" class="w-5 h-5"></i>
                            Tambahan Honor
                    </a>

                    <a href="{{ route('face-enrollment.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('face-enrollment.index') ? 'bg-white/15' : '' }}">
                        <i data-lucide="scan-line" class="w-5 h-5"></i>
                        Registrasi Wajah
                    </a>

                    <a href="{{ route('teachers.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('teachers.index') ? 'bg-white/15' : '' }}">
                        <i data-lucide="users" class="w-5 h-5"></i>
                        Data Guru
                    </a>

                    <a href="{{ route('subjects.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('subjects.index') ? 'bg-white/15' : '' }}">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                        Mata Pelajaran
                    </a>

                    <a href="{{ route('daily-attendances.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('daily-attendances.index') ? 'bg-white/15' : '' }}">
                        <i data-lucide="calendar-check" class="w-5 h-5"></i>
                        Absensi Harian
                    </a>

                    <a href="{{ route('teaching-schedules.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('teaching-schedules.index') ? 'bg-white/15' : '' }}">
                        <i data-lucide="calendar-days" class="w-5 h-5"></i>
                        Jadwal Mengajar
                    </a>

                    <a href="{{ route('subject-attendances.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('subject-attendances.index') ? 'bg-white/15' : '' }}">
                        <i data-lucide="book-check" class="w-5 h-5"></i>
                        Absensi Mapel
                    </a>

                    <a href="{{ route('monthly-honors.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('monthly-honors.index') ? 'bg-white/15' : '' }}">
                        <i data-lucide="wallet" class="w-5 h-5"></i>
                        Rekap Honor
                    </a>

                    <a href="{{ route('picket-schedules.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('picket-schedules.index') ? 'bg-white/15' : '' }}">
                        <i data-lucide="calendar-check" class="w-5 h-5"></i>
                        Jadwal Piket
                    </a>

                    <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('users.index') ? 'bg-white/15' : '' }}">
                        <i data-lucide="user-cog" class="w-5 h-5"></i>
                        Users
                    </a>
                @endif

                @if(auth()->user()->role === 'guru')
                    <a href="{{ route('teacher.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('teacher.dashboard') ? 'bg-white/15' : '' }}">
                        <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                        Portal Guru
                    </a>

                    <a href="{{ route('teacher.attendances') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('teacher.attendances') ? 'bg-white/15' : '' }}">
                        <i data-lucide="calendar-check" class="w-5 h-5"></i>
                        Absensi Saya
                    </a>

                    <a href="{{ route('teacher.honors') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('teacher.honors') ? 'bg-white/15' : '' }}">
                        <i data-lucide="wallet" class="w-5 h-5"></i>
                        Honor Saya
                    </a>

                    <a href="{{ route('teacher.schedules') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('teacher.schedules') ? 'bg-white/15' : '' }}">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                        Jadwal Saya
                    </a>

                    @if(auth()->user()->teacher?->is_picket_officer)
                        <a href="{{ route('picket-reports.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('picket-reports.create') ? 'bg-white/15' : '' }}">
                            <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                            Laporan Piket
                        </a>

                       <a href="{{ route('picket-subject-attendances.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('picket-subject-attendances.index') ? 'bg-white/15' : '' }}">
                            <i data-lucide="book-check" class="w-5 h-5"></i>
                            Absensi Mapel Piket
                        </a>
                    @endif
                @endif

                <a href="{{ route('kiosk.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('kiosk.index') ? 'bg-white/15' : '' }}">
                    <i data-lucide="monitor" class="w-5 h-5"></i>
                    Kiosk Mode
                </a>

            </nav>
        </aside>

        <main class="flex-1 min-w-0">
            <header class="sticky top-0 z-20 bg-white/80 backdrop-blur-xl border-b border-slate-200">
                <div class="px-4 lg:px-8 py-4 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black text-slate-900">
                            {{ $header ?? 'Dashboard' }}
                        </h2>
                        <p class="text-sm text-slate-500">
                            Sistem Absensi Wajah Guru Assaidiyyah
                        </p>
                    </div>

                    <div x-data="{ open:false }" class="relative">
                        <button
                            @click="open = !open"
                            class="flex items-center gap-3 rounded-2xl px-3 py-2 hover:bg-slate-100 transition">

                            <div class="hidden sm:block text-right">
                                <p class="font-bold text-slate-900">
                                    {{ auth()->user()->name }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ auth()->user()->email }}
                                </p>
                            </div>

                            <div class="w-11 h-11 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center">
                                <i data-lucide="user-round" class="w-5 h-5"></i>
                            </div>
                        </button>

                        <div
                            x-show="open"
                            @click.outside="open=false"
                            x-transition
                            class="absolute right-0 mt-2 w-64 rounded-3xl bg-white shadow-2xl border border-slate-100 overflow-hidden">

                            <div class="px-4 py-4 bg-slate-50">
                                <p class="font-black text-slate-900">
                                    {{ auth()->user()->name }}
                                </p>

                                <p class="text-sm text-slate-500">
                                    {{ auth()->user()->email }}
                                </p>

                                <span class="inline-block mt-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-black">
                                    {{ strtoupper(auth()->user()->role) }}
                                </span>
                            </div>

                            @if(auth()->user()->role === 'guru')
                                <a href="{{ route('teacher.dashboard') }}"
                                    class="flex items-center gap-3 px-4 py-4 hover:bg-slate-50 font-bold text-slate-700">
                                    <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                                    Portal Guru
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <button
                                    type="submit"
                                    class="w-full flex items-center gap-3 px-4 py-4 text-red-600 hover:bg-red-50 font-black">

                                    <i data-lucide="log-out" class="w-5 h-5"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <div class="p-4 lg:p-8 pb-24">
                {{ $slot }}
            </div>

            <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-30 bg-white border-t border-slate-200 px-3 py-2">
                @if(auth()->user()->role === 'admin')
                    <div class="grid grid-cols-5 gap-1 text-xs">
                        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-slate-500' }}">
                            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                            Home
                        </a>

                        <a href="{{ route('daily-attendances.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('daily-attendances.index') ? 'text-blue-600' : 'text-slate-500' }}">
                            <i data-lucide="calendar-check" class="w-5 h-5"></i>
                            Absen
                        </a>

                        <a href="{{ route('teachers.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('teachers.index') ? 'text-blue-600' : 'text-slate-500' }}">
                            <i data-lucide="users" class="w-5 h-5"></i>
                            Guru
                        </a>

                        <a href="{{ route('monthly-honors.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('monthly-honors.index') ? 'text-blue-600' : 'text-slate-500' }}">
                            <i data-lucide="wallet" class="w-5 h-5"></i>
                            Honor
                        </a>

                        <a href="{{ route('users.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('users.index') ? 'text-blue-600' : 'text-slate-500' }}">
                            <i data-lucide="user-cog" class="w-5 h-5"></i>
                            Users
                        </a>
                    </div>
                @endif

                @if(auth()->user()->role === 'guru')
                    <div class="grid grid-cols-5 gap-1 text-xs">
                        <a href="{{ route('teacher.dashboard') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('teacher.dashboard') ? 'text-blue-600' : 'text-slate-500' }}">
                            <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                            Home
                        </a>

                        <a href="{{ route('teacher.attendances') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('teacher.attendances') ? 'text-blue-600' : 'text-slate-500' }}">
                            <i data-lucide="calendar-check" class="w-5 h-5"></i>
                            Absen
                        </a>

                        <a href="{{ route('teacher.honors') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('teacher.honors') ? 'text-blue-600' : 'text-slate-500' }}">
                            <i data-lucide="wallet" class="w-5 h-5"></i>
                            Honor
                        </a>

                        <a href="{{ route('teacher.schedules') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('teacher.schedules') ? 'text-blue-600' : 'text-slate-500' }}">
                            <i data-lucide="book-open" class="w-5 h-5"></i>
                            Jadwal
                        </a>

                        @if(auth()->user()->teacher?->is_picket_officer)
                            <a href="{{ route('picket-reports.create') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('picket-reports.create') ? 'text-blue-600' : 'text-slate-500' }}">
                                <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                                Piket
                            </a>
                            <a href="{{ route('picket-subject-attendances.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-white/10 {{ request()->routeIs('picket-subject-attendances.index') ? 'bg-white/15' : '' }}">
                                    <i data-lucide="book-check" class="w-5 h-5"></i>
                                    Absensi Mapel Piket
                                </a>
                        @else
                            <a href="{{ route('kiosk.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('kiosk.index') ? 'text-blue-600' : 'text-slate-500' }}">
                                <i data-lucide="monitor" class="w-5 h-5"></i>
                                Kiosk
                            </a>
                        @endif
                    </div>
                @endif
            </nav>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</body>
</html>
