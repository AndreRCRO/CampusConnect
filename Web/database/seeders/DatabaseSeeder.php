<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'estudiante@univalle.edu'],
            ['name' => 'Ana Rodriguez', 'password' => 'Campus123', 'role' => 'student'],
        );

        User::updateOrCreate(
            ['email' => 'admin@univalle.edu'],
            ['name' => 'Administrador Campus', 'password' => 'Campus123', 'role' => 'admin'],
        );
    }
}
