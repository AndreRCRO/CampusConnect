<?php

namespace Database\Factories;

use App\Models\RequestStatusHistory;
use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RequestStatusHistory>
 */
class RequestStatusHistoryFactory extends Factory
{
    protected $model = RequestStatusHistory::class;

    public function definition(): array
    {
        return [
            'student_request_id' => StudentRequest::factory(),
            'changed_by' => User::factory()->admin(),
            'previous_status' => 'pendiente',
            'new_status' => 'en_proceso',
            'notes' => fake()->sentence(),
        ];
    }
}
