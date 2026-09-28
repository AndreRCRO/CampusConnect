<?php

namespace Tests\Feature;

use App\Models\InstitutionalResource;
use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWebInterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_via_web_login_form(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin.web@universidad.edu',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin.web@universidad.edu',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_student_blocked_from_web_admin_dashboard(): void
    {
        $student = User::factory()->student()->create([
            'email' => 'estudiante.test@universidad.edu',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'estudiante.test@universidad.edu',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_admin_can_view_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        StudentRequest::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200)
            ->assertViewIs('admin.dashboard')
            ->assertViewHas('totalRequests', 3);
    }

    public function test_admin_can_change_request_status_via_web(): void
    {
        $admin = User::factory()->admin()->create();
        $studentRequest = StudentRequest::factory()->create(['status' => 'pendiente']);

        $response = $this->actingAs($admin)->post("/admin/requests/{$studentRequest->id}/status", [
            'status' => 'en_proceso',
            'notes' => 'Derivado a técnicos de soporte.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_requests', [
            'id' => $studentRequest->id,
            'status' => 'en_proceso',
        ]);

        $this->assertDatabaseHas('request_status_histories', [
            'student_request_id' => $studentRequest->id,
            'previous_status' => 'pendiente',
            'new_status' => 'en_proceso',
            'notes' => 'Derivado a técnicos de soporte.',
        ]);
    }

    public function test_admin_can_assign_responsible_staff_via_web(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->staff()->create();
        $studentRequest = StudentRequest::factory()->create();

        $response = $this->actingAs($admin)->post("/admin/requests/{$studentRequest->id}/assign", [
            'assigned_to' => $staff->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_requests', [
            'id' => $studentRequest->id,
            'assigned_to' => $staff->id,
        ]);
    }

    public function test_admin_can_manage_institutional_resources_web(): void
    {
        $admin = User::factory()->admin()->create();

        // 1. Create Resource
        $createResponse = $this->actingAs($admin)->post('/admin/resources', [
            'name' => 'Aula Magna 101',
            'code' => 'AM-101',
            'category' => 'infraestructura',
            'location' => 'Piso 1 - Pabellón Central',
            'description' => 'Aula con capacidad para 120 alumnos y proyector HD.',
            'is_active' => '1',
        ]);

        $createResponse->assertRedirect(route('admin.resources.index'));
        $this->assertDatabaseHas('institutional_resources', ['code' => 'AM-101']);

        $resource = InstitutionalResource::where('code', 'AM-101')->first();

        // 2. Update Resource
        $updateResponse = $this->actingAs($admin)->put("/admin/resources/{$resource->id}", [
            'name' => 'Aula Magna 101 Renovada',
            'code' => 'AM-101',
            'category' => 'infraestructura',
            'location' => 'Piso 1 - Pabellón Central',
            'is_active' => '1',
        ]);

        $updateResponse->assertRedirect(route('admin.resources.index'));
        $this->assertDatabaseHas('institutional_resources', ['name' => 'Aula Magna 101 Renovada']);

        // 3. Delete Resource
        $deleteResponse = $this->actingAs($admin)->delete("/admin/resources/{$resource->id}");
        $deleteResponse->assertRedirect(route('admin.resources.index'));
        $this->assertDatabaseMissing('institutional_resources', ['id' => $resource->id]);
    }

    public function test_admin_can_view_consolidated_reports_web(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->staff()->create();

        StudentRequest::factory()->create(['status' => 'pendiente', 'category' => 'mantenimiento']);
        StudentRequest::factory()->create(['status' => 'en_proceso', 'category' => 'soporte_tecnologico', 'assigned_to' => $staff->id]);
        StudentRequest::factory()->create(['status' => 'resuelto', 'category' => 'infraestructura']);

        $response = $this->actingAs($admin)->get('/admin/reports');

        $response->assertStatus(200)
            ->assertViewIs('admin.reports.index')
            ->assertViewHas('totalReceived', 3)
            ->assertViewHas('pendingCount', 1)
            ->assertViewHas('closedCount', 1);
    }
}
