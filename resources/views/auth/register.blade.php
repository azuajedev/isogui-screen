<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Idogui Screen</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center py-12">
    <div class="w-full max-w-md px-6">
        <!-- Logo y título -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">
                <span class="text-5xl">🎨</span>
                Idogui Screen
            </h1>
            <p class="text-slate-400">Únete y crea mockups increíbles</p>
        </div>

        <!-- Formulario de registro -->
        <div class="bg-slate-800 rounded-2xl shadow-xl p-8 border border-slate-700">
            <h2 class="text-2xl font-semibold text-white mb-6">Crear Cuenta</h2>

            @if ($errors->any())
                <div class="mb-6 bg-red-500/10 border border-red-500/50 rounded-lg p-4">
                    <ul class="text-red-400 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Nombre -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-2">
                        Nombre completo
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}"
                        required 
                        autofocus
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        placeholder="Juan Pérez"
                    >
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-2">
                        Correo electrónico
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        required
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        placeholder="tu@email.com"
                    >
                </div>

                <!-- Contraseña -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-2">
                        Contraseña
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        placeholder="Mínimo 8 caracteres"
                    >
                </div>

                <!-- Confirmar contraseña -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-2">
                        Confirmar contraseña
                    </label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        placeholder="Repite tu contraseña"
                    >
                </div>

                <!-- Botón de registro -->
                <button 
                    type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 ease-in-out transform hover:scale-[1.02] active:scale-[0.98]"
                >
                    Crear Cuenta
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-700"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-slate-800 text-slate-400">o</span>
                </div>
            </div>

            <!-- Link a login -->
            <p class="text-center text-slate-400 text-sm">
                ¿Ya tienes una cuenta?
                <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-medium transition">
                    Inicia sesión
                </a>
            </p>
        </div>

        <!-- Plan info -->
        <div class="mt-6 bg-gradient-to-r from-indigo-500/10 to-purple-500/10 rounded-lg p-4 border border-indigo-500/20">
            <p class="text-sm text-indigo-300 font-medium mb-2">✨ Plan Gratuito incluye:</p>
            <ul class="space-y-1 text-xs text-slate-400">
                <li>• 10 mockups al mes</li>
                <li>• Plantillas básicas</li>
                <li>• Exportación en PNG/JPEG</li>
            </ul>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <a href="{{ route('home') }}" class="text-slate-400 hover:text-slate-300 text-sm transition">
                ← Volver al inicio
            </a>
        </div>
    </div>
</body>
</html>
