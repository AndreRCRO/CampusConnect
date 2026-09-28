<?php

namespace Tests\Feature\Admin;

use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_all_requests_and_filter(): void
    {
        $admin = User::factory()->admin()->create();

        StudentRequest::factory()->create([
            'category' => 'infraestructura',
            'priority' => 'urgente',
            'status' => 'pendiente',
        ]);

        StudentRequest::factory()->create([
            'category' => 'soporte_tecnologico',
            'priority' => 'baja',
            'status' => 'en_proceso',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/admin/requests?category=infraestructura');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category', 'infraestructura');
    }

    public function test_admin_can_prioritize_request(): void
    {
        $admin = User::factory()->admin()->create();
        $studentRequest = StudentRequest::factory()->create(['priority' => 'baja']);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/v1/admin/requests/{$studentRequest->id}/priority", [
            'priority' => 'urgente',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('new_priority', 'urgente');

        $this->assertDatabaseHas('student_requests', [
            'id' => $studentRequest->id,
            'priority' => 'urgente',
        ]);
    }

    public function test_admin_can_assign_responsible_staff(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->staff()->create();
        $studentRequest = StudentRequest::factory()->create();

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/v1/admin/requests/{$studentRequest->id}/assign", [
            'assigned_to' => $staff->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('assigned_staff.id', $staff->id);

        $this->assertDatabaseHas('student_requests', [
            'id' => $studentRequest->id,
            'assigned_to' => $staff->id,
        ]);
    }

    public function test_admin_cannot_assign_student_as_responsible_staff(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        $studentRequest = StudentRequest::factory()->create();

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/v1/admin/requests/{$studentRequest->id}/assign", [
            'assigned_to' => $student->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'El usuario asignado debe ser personal administrativo o técnico.');
    }

    public function test_admin_can_change_status_and_record_history(): void
    {
        $admin = User::factory()->admin()->create();
        $studentRequest = StudentRequest::factory()->create(['status' => 'pendiente']);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/v1/admin/requests/{$studentRequest->id}/status", [
            'status' => 'en_proceso',
            'notes' => 'Se ha derivado la solicitud al técnico de turno.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('new_status', 'en_proceso');

        $this->assertDatabaseHas('student_requests', [
            'id' => $studentRequest->id,
            'status' => 'en_proceso',
        ]);

        $this->assertDatabaseHas('request_status_histories', [
            'student_request_id' => $studentRequest->id,
            'changed_by' => $admin->id,
            'previous_status' => 'pendiente',
            'new_status' => 'en_proceso',
            'notes' => 'Se ha derivado la solicitud al técnico de turno.',
        ]);
    }

    public function test_student_cannot_access_admin_dashboard_routes(): void
    {
        $student = User::factory()->student()->create();
        Sanctum::actingAs($student);

        $response = $this->getJson('/api/v1/admin/requests');

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Acceso no autorizado para tu rol de usuario.');
    }
}
