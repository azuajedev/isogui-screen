<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Página de inicio (pública)
Route::get('/', function () {
    return view('home');
})->name('home');

// Rutas de autenticación
Route::get('/login', function () {
    return view('auth.login');
})->name('login')->middleware('guest');

Route::post('/login', function () {
    $credentials = request()->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    // Intentar autenticar
    if (Auth::attempt($credentials, request()->filled('remember'))) {
        request()->session()->regenerate();
        
        // Verificar que el usuario esté autenticado
        if (Auth::check()) {
            return redirect()->intended('/dashboard');
        }
    }

    return back()->withErrors([
        'email' => 'Las credenciales no coinciden con nuestros registros.',
    ])->onlyInput('email');
})->middleware('guest');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::get('/register', function () {
    return view('auth.register');
})->name('register')->middleware('guest');

Route::post('/register', function () {
    $validated = request()->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    $user = \App\Models\User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => bcrypt($validated['password']),
        'role' => 'user',
        'plan' => 'free',
    ]);

    Auth::login($user);
    return redirect('/dashboard');
})->middleware('guest');

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        $designs = \App\Models\Design::where('user_id', auth()->id())
            ->orderBy('last_edited_at', 'desc')
            ->limit(6)
            ->get();
        
        $totalDesigns = \App\Models\Design::where('user_id', auth()->id())->count();
        
        return view('dashboard', compact('designs', 'totalDesigns'));
    })->name('dashboard');

    // Editor de mockups
    Route::get('/editor', function () {
        return view('editor');
    })->name('editor');

    // Editor con diseño específico (ID encriptado)
    Route::get('/editor/{encrypted_id}', function () {
        return view('editor');
    })->name('editor.design');

    // Proyectos
    Route::get('/projects', function () {
        return view('projects.index');
    })->name('projects.index');

    // Galería de imágenes
    Route::get('/gallery', function () {
        return view('gallery.index');
    })->name('gallery.index');

    // Perfil de usuario
    Route::get('/profile', function () {
        return view('profile.index');
    })->name('profile.index');

    // Rutas de administración (solo para admin)
    Route::middleware([\App\Http\Middleware\EnsureUserIsAdmin::class])->prefix('admin')->group(function () {
        Route::get('/mockups', function () {
            return view('admin.mockups');
        })->name('admin.mockups');
    });
});
