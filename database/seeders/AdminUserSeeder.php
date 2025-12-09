<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existe un usuario admin
        $existingAdmin = User::where('role', 'admin')->first();

        if ($existingAdmin) {
            $this->command->info('Ya existe un usuario administrador: ' . $existingAdmin->email);
            return;
        }

        // Crear usuario administrador
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@isogui.com',
            'password' => Hash::make('admin123'), // Cambia esto en producción
            'role' => 'admin',
            'plan' => 'enterprise',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Usuario administrador creado exitosamente:');
        $this->command->info('Email: ' . $admin->email);
        $this->command->info('Password: admin123');
        $this->command->warn('¡IMPORTANTE! Cambia la contraseña en producción.');
    }
}
