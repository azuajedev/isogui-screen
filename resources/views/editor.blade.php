<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Idogui Screen') }} - Editor de Mockups</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Configuración global -->
    <script>
        window.Idogui = {
            user: @json(auth()->user()),
            apiUrl: '{{ url('/api') }}',
            storageUrl: '{{ url('/storage') }}',
            csrfToken: '{{ csrf_token() }}'
        };

        // Verificar autenticación al cargar la página
        if (!window.Idogui.user) {
            window.location.href = '/login';
        }
    </script>
</head>
<body class="antialiased">
    <div id="app">
        <mockup-editor></mockup-editor>
    </div>
</body>
</html>
