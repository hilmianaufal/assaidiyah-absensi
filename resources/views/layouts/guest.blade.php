<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Absensi Assaidiyyah') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="min-h-screen bg-[#eef6ff] text-slate-900">
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-32 -right-24 h-80 w-80 rounded-full bg-blue-400/30 blur-3xl"></div>
        <div class="absolute top-1/3 -left-24 h-80 w-80 rounded-full bg-sky-300/30 blur-3xl"></div>
        <div class="absolute bottom-0 right-1/4 h-72 w-72 rounded-full bg-indigo-300/20 blur-3xl"></div>
    </div>

    <main class="relative z-10 min-h-screen flex items-center justify-center px-4 py-8">
        {{ $slot }}
    </main>

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</body>
</html>
