<?php

namespace Tests\Feature\Admin;

use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ConsolidatedReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_generate_consolidated_reports(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->staff()->create(['name' => 'Carlos Perez']);

        // Create sample requests across categories and priorities
        StudentRequest::factory()->create([
            'category' => 'mantenimiento',
            'priority' => 'urgente',
            'status' => 'pendiente',
        ]);

        StudentRequest::factory()->create([
            'category' => 'soporte_tecnologico',
            'priority' => 'alta',
            'status' => 'en_proceso',
            'assigned_to' => $staff->id,
        ]);

        StudentRequest::factory()->create([
            'category' => 'infraestructura',
            'priority' => 'media',
            'status' => 'resuelto',
            'assigned_to' => $staff->id,
            'created_at' => now()->subHours(10),
            'resolved_at' => now()->subHours(2),
        ]);

        StudentRequest::factory()->create([
            'category' => 'equipamiento',
            'priority' => 'baja',
            'status' => 'cerrado',
            'created_at' => now()->subHours(24),
            'closed_at' => now()->subHours(4),
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/admin/reports/consolidated');

        $response->assertStatus(200)
            ->assertJsonPath('report_summary.total_solicitudes_recibidas', 4)
            ->assertJsonPath('report_summary.solicitudes_pendientes', 1)
            ->assertJsonPath('report_summary.solicitudes_cerradas', 2)
            ->assertJsonPath('solicitudes_segun_tipo.mantenimiento', 1)
            ->assertJsonPath('solicitudes_segun_tipo.soporte_tecnologico', 1)
            ->assertJsonPath('solicitudes_segun_tipo.infraestructura', 1)
            ->assertJsonPath('solicitudes_segun_tipo.equipamiento', 1)
            ->assertJsonPath('solicitudes_segun_prioridad.urgente', 1)
            ->assertJsonPath('solicitudes_segun_prioridad.alta', 1)
            ->assertJsonStructure([
                'report_summary' => [
                    'total_solicitudes_recibidas',
                    'solicitudes_pendientes',
                    'solicitudes_cerradas',
                    'tiempo_promedio_atencion_horas',
                ],
                'solicitudes_segun_tipo',
                'solicitudes_segun_prioridad',
                'responsables_asignados',
            ]);
    }
}
