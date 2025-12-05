@php
    use Illuminate\Support\Facades\Storage;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Idogui Screen</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-900 min-h-screen">
    <!-- Header -->
    <header class="bg-slate-800 border-b border-slate-700">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-white">
                        <span class="text-3xl">🎨</span>
                        Idogui Screen
                    </h1>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-slate-400 text-sm">
                        {{ auth()->user()->name }}
                        <span class="ml-2 px-2 py-1 bg-indigo-600 text-white text-xs rounded-full font-medium">
                            {{ strtoupper(auth()->user()->role) }}
                        </span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-white text-sm transition">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 py-12">
        <!-- Bienvenida -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-white mb-2">
                ¡Bienvenido, {{ auth()->user()->name }}! 👋
            </h2>
            <p class="text-slate-400">Crea mockups profesionales para tus apps en minutos</p>
        </div>

        <!-- Acción rápida -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <!-- Crear nuevo mockup -->
            <a href="{{ route('editor') }}" class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-2xl p-8 hover:scale-[1.02] transition-transform duration-200 shadow-xl">
                <div class="text-5xl mb-4">✨</div>
                <h3 class="text-2xl font-bold text-white mb-2">Crear Mockup</h3>
                <p class="text-indigo-100 text-sm">Comienza un nuevo proyecto</p>
            </a>

            <!-- Mis proyectos -->
            <div class="bg-slate-800 rounded-2xl p-8 border border-slate-700">
                <div class="text-5xl mb-4">📁</div>
                <h3 class="text-2xl font-bold text-white mb-2">Mis Proyectos</h3>
                <p class="text-slate-400 text-sm">{{ $totalDesigns }} {{ $totalDesigns === 1 ? 'proyecto guardado' : 'proyectos guardados' }}</p>
            </div>

            <!-- Plantillas -->
            <div class="bg-slate-800 rounded-2xl p-8 border border-slate-700">
                <div class="text-5xl mb-4">🎨</div>
                <h3 class="text-2xl font-bold text-white mb-2">Plantillas</h3>
                <p class="text-slate-400 text-sm">4 plantillas disponibles</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-12">
            <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
                <p class="text-slate-400 text-sm mb-1">Mockups creados</p>
                <p class="text-3xl font-bold text-white">{{ $totalDesigns }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
                <p class="text-slate-400 text-sm mb-1">Este mes</p>
                <p class="text-3xl font-bold text-white">{{ $totalDesigns }} / 
                    <span class="text-lg text-slate-500">{{ auth()->user()->role === 'free' ? '10' : '∞' }}</span>
                </p>
            </div>
            <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
                <p class="text-slate-400 text-sm mb-1">Screenshots</p>
                <p class="text-3xl font-bold text-white">0</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
                <p class="text-slate-400 text-sm mb-1">Plan actual</p>
                <p class="text-2xl font-bold text-indigo-400">{{ strtoupper(auth()->user()->role) }}</p>
            </div>
        </div>

        <!-- Upgrade banner (solo para free) -->
        @if(auth()->user()->role === 'free')
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-8 mb-12">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-white mb-2">🚀 Actualiza a Premium</h3>
                    <p class="text-indigo-100 mb-4">Mockups ilimitados, plantillas exclusivas y más</p>
                    <ul class="space-y-1 text-sm text-indigo-100">
                        <li>✓ Mockups ilimitados</li>
                        <li>✓ Todas las plantillas PRO</li>
                        <li>✓ Generación con IA</li>
                        <li>✓ Traducción automática</li>
                    </ul>
                </div>
                <button class="bg-white text-indigo-600 font-bold px-8 py-3 rounded-lg hover:bg-indigo-50 transition">
                    Actualizar ahora
                </button>
            </div>
        </div>
        @endif

        <!-- Proyectos recientes -->
        <div class="bg-slate-800 rounded-2xl p-8 border border-slate-700">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white">Proyectos Recientes</h3>
                @if($designs->count() > 0)
                <a href="{{ route('editor') }}" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium transition">
                    Ver todos →
                </a>
                @endif
            </div>
            
            @if($designs->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($designs as $design)
                    <a href="{{ route('editor') }}?design={{ $design->id }}" class="block bg-slate-700 rounded-xl overflow-hidden hover:ring-2 hover:ring-indigo-500 transition group">
                        <div class="aspect-[4/3] bg-slate-600 relative overflow-hidden">
                            @if($design->thumbnail_path)
                                <img src="{{ Storage::url($design->thumbnail_path) }}" alt="{{ $design->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-500 text-4xl">
                                    🎨
                                </div>
                            @endif
                        </div>
                        <div class="p-4">
                            <h4 class="text-white font-semibold mb-1 group-hover:text-indigo-400 transition">{{ $design->name }}</h4>
                            <div class="flex items-center justify-between text-xs text-slate-400">
                                <span class="px-2 py-1 bg-slate-600 rounded">
                                    {{ ucfirst($design->canvas_type) }}
                                </span>
                                <span>{{ $design->last_edited_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-6xl mb-4 opacity-50">📂</div>
                    <p class="text-slate-400 mb-4">No tienes proyectos aún</p>
                    <a href="{{ route('editor') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                        Crear tu primer mockup
                    </a>
                </div>
            @endif
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800 mt-12">
        <div class="max-w-7xl mx-auto px-6 py-8">
            <p class="text-center text-slate-500 text-sm">
                © 2025 Idogui Screen. Todos los derechos reservados.
            </p>
        </div>
    </footer>
</body>
</html>
