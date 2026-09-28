<?php

namespace Database\Factories;

use App\Models\RequestComment;
use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RequestComment>
 */
class RequestCommentFactory extends Factory
{
    protected $model = RequestComment::class;

    public function definition(): array
    {
        return [
            'student_request_id' => StudentRequest::factory(),
            'user_id' => User::factory(),
            'comment' => fake()->sentence(),
            'is_internal' => false,
        ];
    }
}
