<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder principal de la base de datos
 * Ejecuta todos los seeders necesarios para la aplicación
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario admin
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@idogui.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'plan' => 'enterprise',
        ]);

        // Crear usuario demo
        User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@idogui.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'plan' => 'free',
        ]);

        // Crear usuario premium
        User::factory()->create([
            'name' => 'Premium User',
            'email' => 'premium@idogui.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'plan' => 'pro',
        ]);

        // Ejecutar seeder de templates
        $this->call([
            TemplateSeeder::class,
        ]);
    }
}
