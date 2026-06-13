<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Absensi Assaidiyyah') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    {{ $slot }}




    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</body>
</html>