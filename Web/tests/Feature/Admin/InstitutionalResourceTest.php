<?php

namespace Tests\Feature\Admin;

use App\Models\InstitutionalResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InstitutionalResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_institutional_resources_crud(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        // 1. Create Resource
        $createResponse = $this->postJson('/api/v1/admin/resources', [
            'name' => 'Servidor Central de Laboratorio 3',
            'code' => 'SRV-LAB3-001',
            'category' => 'soporte_tecnologico',
            'location' => 'Piso 2 - Edificio B',
            'description' => 'Servidor principal para prácticas informáticas.',
        ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('resource.code', 'SRV-LAB3-001');

        $resourceId = $createResponse->json('resource.id');

        // 2. List Resources
        $listResponse = $this->getJson('/api/v1/admin/resources');
        $listResponse->assertStatus(200)
            ->assertJsonCount(1);

        // 3. Update Resource
        $updateResponse = $this->putJson("/api/v1/admin/resources/{$resourceId}", [
            'name' => 'Servidor Central Actualizado',
            'code' => 'SRV-LAB3-001',
            'category' => 'soporte_tecnologico',
        ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('resource.name', 'Servidor Central Actualizado');

        // 4. Delete Resource
        $deleteResponse = $this->deleteJson("/api/v1/admin/resources/{$resourceId}");
        $deleteResponse->assertStatus(200);

        $this->assertDatabaseMissing('institutional_resources', ['id' => $resourceId]);
    }

    public function test_student_cannot_manage_institutional_resources(): void
    {
        $student = User::factory()->student()->create();
        Sanctum::actingAs($student);

        $response = $this->postJson('/api/v1/admin/resources', [
            'name' => 'Recurso Prohibido',
            'code' => 'REC-001',
            'category' => 'mantenimiento',
        ]);

        $response->assertStatus(403);
    }
}
