<?php

namespace Pruebas\Backend;

use App\Http\Middleware\EnsureUserRole;
use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class BackendUnitTest extends TestCase
{
    public function test_identifies_student_role(): void
    {
        $student = new User(['role' => 'student']);

        $this->assertTrue($student->isStudent());
        $this->assertFalse($student->hasAdminAccess());
    }

    public function test_identifies_admin_role_and_access(): void
    {
        $admin = new User(['role' => 'admin']);

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->hasAdminAccess());
    }

    public function test_staff_has_administrative_access(): void
    {
        $staff = new User(['role' => 'staff']);

        $this->assertTrue($staff->isStaff());
        $this->assertTrue($staff->hasAdminAccess());
    }

    public function test_mobile_request_accepts_integrated_fields(): void
    {
        $request = new StudentRequest([
            'tracking_code' => 'REQ-20260929-ABC123',
            'category' => 'soporte_tecnologico',
            'location' => 'Bloque B - Aula 204',
            'title' => 'Proyector sin señal',
            'description' => 'El proyector no detecta la entrada HDMI.',
            'priority' => 'alta',
            'status' => 'pendiente',
        ]);

        $this->assertSame('Bloque B - Aula 204', $request->location);
        $this->assertSame('soporte_tecnologico', $request->category);
        $this->assertSame('pendiente', $request->status);
    }

    public function test_request_dates_are_cast_to_datetime(): void
    {
        $request = new StudentRequest([
            'resolved_at' => '2026-09-29 10:30:00',
            'closed_at' => '2026-09-29 11:00:00',
        ]);

        $this->assertSame('2026-09-29 10:30:00', $request->resolved_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-29 11:00:00', $request->closed_at->format('Y-m-d H:i:s'));
    }

    public function test_role_middleware_rejects_unauthenticated_requests(): void
    {
        $request = Request::create('/api/v1/student/requests', 'GET');
        $response = (new EnsureUserRole)->handle(
            $request,
            fn () => new Response('OK'),
            'student',
        );

        $this->assertSame(401, $response->getStatusCode());
    }
}
