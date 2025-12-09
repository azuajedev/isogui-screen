<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Administración de Mockups - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        // Configuración global de la aplicación
        window.Idogui = {
            user: @json(auth()->user()),
            apiUrl: '{{ url('/api') }}',
            assetUrl: '{{ asset('') }}',
        };
    </script>
</head>
<body>
    <div id="app">
        <admin-mockups></admin-mockups>
    </div>
</body>
</html>
