<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Idogui Screen') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <svg class="w-10 h-10 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zM4 6h16v2H4V6zm0 12v-8h16v8H4z"/>
                        </svg>
                        <h1 class="text-2xl font-bold text-gray-900">Idogui Screen</h1>
                    </div>
                    <nav class="flex items-center space-x-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-gray-900 transition">Dashboard</a>
                            <a href="{{ route('editor') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Editor</a>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-700 hover:text-gray-900 transition">Cerrar Sesión</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900 transition">Iniciar Sesión</a>
                            <a href="{{ route('register') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Registrarse</a>
                        @endauth
                    </nav>
                </div>
            </div>
        </header>
        <main class="flex-1">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center">
                    <h2 class="text-4xl font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                        Crea Mockups Profesionales
                        <span class="block text-indigo-600">en Segundos</span>
                    </h2>
                    <p class="mt-6 max-w-2xl mx-auto text-xl text-gray-500">
                        Genera imágenes promocionales impresionantes para tus apps y juegos. Soporta múltiples idiomas, templates personalizables y renderizado profesional.
                    </p>
                    <div class="mt-10 flex gap-4 justify-center">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                Ir al Dashboard
                                <svg class="ml-2 -mr-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                Comenzar Gratis
                                <svg class="ml-2 -mr-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                            <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Iniciar Sesión
                            </a>
                        @endauth
                    </div>
                </div>
                <div class="mt-24">
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Templates Profesionales</h3>
                            <p class="text-gray-600">Múltiples plantillas diseñadas específicamente para App Store y Play Store.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Multi-idioma</h3>
                            <p class="text-gray-600">Genera mockups en múltiples idiomas con sugerencias de texto con IA.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Renderizado Rápido</h3>
                            <p class="text-gray-600">Genera y descarga tus mockups en alta calidad en segundos.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-24 bg-indigo-600 rounded-2xl p-8">
                    <div class="text-center text-white">
                        <h3 class="text-3xl font-bold mb-4">¿Listo para comenzar?</h3>
                        <p class="text-xl mb-8 text-indigo-100">Usuarios: {{ \App\Models\User::count() }} | Templates: {{ \App\Models\Template::count() }}</p>
                        <a href="{{ url('/editor') }}" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 font-medium rounded-md hover:bg-gray-50 transition">Ir al Editor</a>
                    </div>
                </div>
            </div>
        </main>
        <footer class="bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <p class="text-center text-gray-500 text-sm">&copy; {{ date('Y') }} Idogui Screen. Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</p>
            </div>
        </footer>
    </div>
</body>
</html>
