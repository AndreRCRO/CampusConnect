<?php

namespace Tests\Unit;

use App\Models\InstitutionalResource;
use App\Models\MediaEvidence;
use App\Models\RequestComment;
use App\Models\RequestStatusHistory;
use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentRequestModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_request_relationships(): void
    {
        $student = User::factory()->student()->create();
        $staff = User::factory()->staff()->create();
        $resource = InstitutionalResource::factory()->create();

        $request = StudentRequest::factory()->create([
            'student_id' => $student->id,
            'assigned_to' => $staff->id,
            'institutional_resource_id' => $resource->id,
        ]);

        MediaEvidence::factory()->create(['student_request_id' => $request->id]);
        RequestComment::factory()->create(['student_request_id' => $request->id]);
        RequestStatusHistory::factory()->create(['student_request_id' => $request->id]);

        $this->assertEquals($student->id, $request->student->id);
        $this->assertEquals($staff->id, $request->assignedStaff->id);
        $this->assertEquals($resource->id, $request->institutionalResource->id);
        $this->assertCount(1, $request->mediaEvidences);
        $this->assertCount(1, $request->comments);
        $this->assertCount(1, $request->statusHistories);
    }
}
