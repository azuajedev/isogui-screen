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
        // Crear usuario de prueba
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
            'plan' => 'free',
        ]);

        // Crear usuario admin
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'plan' => 'enterprise',
        ]);

        // Ejecutar seeder de templates
        $this->call([
            TemplateSeeder::class,
        ]);
    }
}
