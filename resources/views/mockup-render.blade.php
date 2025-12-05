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

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --background: #0f172a;
            --surface: #1e293b;
            --surface-light: #334155;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --border: #334155;
            --success: #22c55e;
            --warning: #f59e0b;
            --error: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--background);
            color: var(--text-primary);
            min-height: 100vh;
        }

        #app {
            min-height: 100vh;
        }

        /* Loading state */
        .loading-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            gap: 1rem;
        }

        .loading-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid var(--surface-light);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div id="app">
        <!-- Estado de carga mientras Vue se inicializa -->
        <div class="loading-container">
            <div class="loading-spinner"></div>
            <p class="loading-text">Cargando editor de mockups...</p>
        </div>
    </div>

    <!-- Datos iniciales para Vue -->
    <script>
        window.Idogui = {
            user: @json(auth()->user() ?? null),
            csrfToken: '{{ csrf_token() }}',
            apiUrl: '{{ url('/api') }}',
            storageUrl: '{{ asset('storage') }}',
        };
    </script>
</body>
</html>
