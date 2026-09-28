<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureUserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class EnsureUserRoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_middleware_allows_user_with_correct_role(): void
    {
        $user = User::factory()->student()->create();
        $request = Request::create('/api/v1/student/requests', 'GET');
        $request->setUserResolver(fn () => $user);

        $middleware = new EnsureUserRole();
        $response = $middleware->handle($request, fn () => new Response('OK'), 'student');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_middleware_blocks_user_with_incorrect_role(): void
    {
        $user = User::factory()->student()->create();
        $request = Request::create('/api/v1/admin/requests', 'GET');
        $request->setUserResolver(fn () => $user);

        $middleware = new EnsureUserRole();
        $response = $middleware->handle($request, fn () => new Response('OK'), 'admin', 'staff');

        $this->assertEquals(403, $response->getStatusCode());
    }
}
