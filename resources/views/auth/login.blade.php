<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Idogui Screen</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md px-6">
        <!-- Logo y título -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">
                <span class="text-5xl">🎨</span>
                Idogui Screen
            </h1>
            <p class="text-slate-400">Crea mockups profesionales en minutos</p>
        </div>

        <!-- Formulario de login -->
        <div class="bg-slate-800 rounded-2xl shadow-xl p-8 border border-slate-700">
            <h2 class="text-2xl font-semibold text-white mb-6">Iniciar Sesión</h2>

            @if ($errors->any())
                <div class="mb-6 bg-red-500/10 border border-red-500/50 rounded-lg p-4">
                    <p class="text-red-400 text-sm">{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

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
                        autofocus
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        placeholder="tu@email.com"
                    >
                </div>

                <!-- Contraseña -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-2">
                        Contraseña
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        placeholder="••••••••"
                    >
                </div>

                <!-- Recordarme -->
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            class="w-4 h-4 text-indigo-500 bg-slate-700 border-slate-600 rounded focus:ring-indigo-500 focus:ring-2"
                        >
                        <span class="ml-2 text-sm text-slate-300">Recordarme</span>
                    </label>
                </div>

                <!-- Botón de login -->
                <button 
                    type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 ease-in-out transform hover:scale-[1.02] active:scale-[0.98]"
                >
                    Iniciar Sesión
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

            <!-- Link a registro -->
            <p class="text-center text-slate-400 text-sm">
                ¿No tienes una cuenta?
                <a href="{{ route('register') }}" class="text-indigo-400 hover:text-indigo-300 font-medium transition">
                    Regístrate gratis
                </a>
            </p>
        </div>

        <!-- Usuarios de prueba -->
        <div class="mt-6 bg-slate-800/50 rounded-lg p-4 border border-slate-700/50">
            <p class="text-xs text-slate-400 mb-2 font-semibold">👤 Usuarios de prueba:</p>
            <div class="space-y-1 text-xs text-slate-500">
                <p>• <span class="text-slate-400">admin@idogui.com</span> (password) - Admin</p>
                <p>• <span class="text-slate-400">demo@idogui.com</span> (password) - Demo</p>
                <p>• <span class="text-slate-400">premium@idogui.com</span> (password) - Premium</p>
            </div>
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
