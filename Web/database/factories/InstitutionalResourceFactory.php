<?php

namespace Database\Factories;

use App\Models\InstitutionalResource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InstitutionalResource>
 */
class InstitutionalResourceFactory extends Factory
{
    protected $model = InstitutionalResource::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'code' => 'RES-' . strtoupper(fake()->unique()->bothify('??###')),
            'category' => fake()->randomElement(['mantenimiento', 'soporte_tecnologico', 'infraestructura', 'equipamiento', 'otro']),
            'location' => 'Edificio ' . fake()->randomElement(['A', 'B', 'C']) . ' - Aula ' . fake()->numberBetween(101, 405),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
