<?php

namespace Tests\Feature\Student;

use App\Models\InstitutionalResource;
use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentMobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_create_request_via_mobile_api(): void
    {
        $student = User::factory()->student()->create();
        $resource = InstitutionalResource::factory()->create();

        Sanctum::actingAs($student);

        $response = $this->postJson('/api/v1/student/requests', [
            'title' => 'Falla de proyector en aula 201',
            'description' => 'El proyector no enciende al presionar el botón de encendido.',
            'category' => 'soporte_tecnologico',
            'location' => 'Bloque B - Aula 201',
            'institutional_resource_id' => $resource->id,
            'priority' => 'media',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('request.title', 'Falla de proyector en aula 201')
            ->assertJsonPath('request.status', 'pendiente')
            ->assertJsonPath('request.location', 'Bloque B - Aula 201')
            ->assertJsonPath('request.student_id', $student->id);

        $this->assertDatabaseHas('student_requests', [
            'student_id' => $student->id,
            'title' => 'Falla de proyector en aula 201',
            'status' => 'pendiente',
        ]);

        $this->assertDatabaseHas('request_status_histories', [
            'previous_status' => 'ninguno',
            'new_status' => 'pendiente',
        ]);
    }

    public function test_student_can_list_their_own_requests(): void
    {
        $student = User::factory()->student()->create();
        $otherStudent = User::factory()->student()->create();

        StudentRequest::factory()->count(2)->create(['student_id' => $student->id]);
        StudentRequest::factory()->count(3)->create(['student_id' => $otherStudent->id]);

        Sanctum::actingAs($student);

        $response = $this->getJson('/api/v1/student/requests');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_student_can_attach_media_evidence(): void
    {
        Storage::fake('public');

        $student = User::factory()->student()->create();
        $studentRequest = StudentRequest::factory()->create(['student_id' => $student->id]);

        Sanctum::actingAs($student);

        $file = UploadedFile::fake()->image('evidencia_proyector.jpg');

        $response = $this->postJson("/api/v1/student/requests/{$studentRequest->id}/media", [
            'file' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('media.file_name', 'evidencia_proyector.jpg');

        $this->assertDatabaseHas('media_evidences', [
            'student_request_id' => $studentRequest->id,
            'uploaded_by' => $student->id,
            'file_name' => 'evidencia_proyector.jpg',
        ]);
    }

    public function test_student_can_consult_tracking_and_timeline(): void
    {
        $student = User::factory()->student()->create();
        $studentRequest = StudentRequest::factory()->create([
            'student_id' => $student->id,
            'status' => 'en_proceso',
        ]);

        $studentRequest->statusHistories()->create([
            'changed_by' => $student->id,
            'previous_status' => 'pendiente',
            'new_status' => 'en_proceso',
            'notes' => 'Asignado a mantenimiento',
        ]);

        Sanctum::actingAs($student);

        $response = $this->getJson("/api/v1/student/requests/{$studentRequest->id}/tracking");

        $response->assertStatus(200)
            ->assertJsonPath('current_status', 'en_proceso')
            ->assertJsonStructure([
                'tracking_code',
                'current_status',
                'priority',
                'category',
                'timeline',
            ]);
    }

    public function test_student_can_add_and_view_comments(): void
    {
        $student = User::factory()->student()->create();
        $studentRequest = StudentRequest::factory()->create(['student_id' => $student->id]);

        Sanctum::actingAs($student);

        $postCommentResponse = $this->postJson("/api/v1/student/requests/{$studentRequest->id}/comments", [
            'comment' => 'Adjunté fotos adicionales del problema.',
        ]);

        $postCommentResponse->assertStatus(201)
            ->assertJsonPath('comment.comment', 'Adjunté fotos adicionales del problema.');

        $getCommentsResponse = $this->getJson("/api/v1/student/requests/{$studentRequest->id}/comments");

        $getCommentsResponse->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_admin_cannot_use_student_mobile_routes(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/student/requests');

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Acceso no autorizado para tu rol de usuario.');
    }
}
