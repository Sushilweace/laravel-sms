<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Tests\TestCase;

class SecurityIsolationTest extends TestCase
{
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $studentsResponse = $this->get('/students');
        $studentsResponse->assertRedirect('/login');
    }

    public function test_parent_cannot_view_unrelated_student_profile(): void
    {
        $parentUser = User::where('email', 'parent@school.test')->first();
        if (! $parentUser) {
            $this->markTestSkipped('Demo seeder data required.');
        }

        // Own child
        $ownStudent = $parentUser->guardian->students->first();
        $this->actingAs($parentUser);
        $response = $this->get(route('students.show', $ownStudent));
        $response->assertStatus(200);

        // Another student not in guardian's family
        $otherStudent = Student::whereDoesntHave('guardians', function ($q) use ($parentUser) {
            $q->where('guardians.id', $parentUser->guardian->id);
        })->first();

        if ($otherStudent) {
            $otherResponse = $this->get(route('students.show', $otherStudent));
            $otherResponse->assertStatus(403);
        }
    }

    public function test_guardian_scope_returns_only_linked_children(): void
    {
        $parentUser = User::where('email', 'parent@school.test')->first();
        if (! $parentUser) {
            $this->markTestSkipped('Demo seeder data required.');
        }

        $visibleStudents = Student::visibleTo($parentUser)->get();
        $this->assertCount(1, $visibleStudents);
        $this->assertEquals($parentUser->guardian->students->first()->id, $visibleStudents->first()->id);
    }
}
