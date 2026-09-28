<?php

namespace Database\Factories;

use App\Models\MediaEvidence;
use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaEvidence>
 */
class MediaEvidenceFactory extends Factory
{
    protected $model = MediaEvidence::class;

    public function definition(): array
    {
        return [
            'student_request_id' => StudentRequest::factory(),
            'uploaded_by' => User::factory()->student(),
            'file_path' => 'evidences/sample.jpg',
            'file_name' => 'evidencia_' . fake()->word() . '.jpg',
            'file_type' => 'image/jpeg',
            'file_size' => fake()->numberBetween(1000, 500000),
        ];
    }
}
