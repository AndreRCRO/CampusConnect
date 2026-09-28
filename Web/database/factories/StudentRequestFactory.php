<?php

namespace Database\Factories;

use App\Models\InstitutionalResource;
use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<StudentRequest>
 */
class StudentRequestFactory extends Factory
{
    protected $model = StudentRequest::class;

    public function definition(): array
    {
        return [
            'tracking_code' => 'REQ-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
            'student_id' => User::factory()->student(),
            'institutional_resource_id' => InstitutionalResource::factory(),
            'category' => fake()->randomElement(['mantenimiento', 'soporte_tecnologico', 'infraestructura', 'equipamiento', 'otro']),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(['baja', 'media', 'alta', 'urgente']),
            'status' => 'pendiente',
            'assigned_to' => null,
            'resolved_at' => null,
            'closed_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pendiente']);
    }

    public function inProgress(): static
    {
        return $this->state(fn () => [
            'status' => 'en_proceso',
            'assigned_to' => User::factory()->staff(),
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn () => [
            'status' => 'resuelto',
            'assigned_to' => User::factory()->staff(),
            'resolved_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'status' => 'cerrado',
            'assigned_to' => User::factory()->staff(),
            'resolved_at' => now()->subHours(2),
            'closed_at' => now(),
        ]);
    }
}
