<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Tests\TestCase;

class SchoolManagementTest extends TestCase
{
    protected User $admin;

    protected School $rootSchool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('email', 'superadmin@school.test')->firstOrFail();
        $this->rootSchool = School::firstOrFail();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/schools');
        $response->assertRedirect('/login');
    }

    public function test_non_admin_user_is_forbidden_from_school_management(): void
    {
        $teacher = User::where('email', 'teacher@school.test')->firstOrFail();
        $response = $this->actingAs($teacher)->get('/admin/schools');
        $response->assertStatus(403);
    }

    public function test_admin_can_view_schools_index_and_metrics(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/schools');

        $response->assertStatus(200);
        $response->assertSee('Schools & Branch Campuses', false);
        $response->assertSee($this->rootSchool->name);
        $response->assertSee($this->rootSchool->code);
        $response->assertSee('Register New School');
        $response->assertSee('Total Enrolled Students');
    }

    public function test_admin_can_search_schools(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/schools?q='.urlencode($this->rootSchool->code));

        $response->assertStatus(200);
        $response->assertSee($this->rootSchool->name);
    }

    public function test_admin_can_view_school_show_with_all_child_entities(): void
    {
        // 1. Default / Students Tab
        $responseStudents = $this->actingAs($this->admin)->get(route('admin.schools.show', [
            'school' => $this->rootSchool->id,
            'tab' => 'students',
        ]));
        $responseStudents->assertStatus(200);
        $responseStudents->assertSee($this->rootSchool->name);
        $responseStudents->assertSee('Students & Linked Parents', false);

        // Check that at least one student is listed
        $firstStudent = Student::first();
        if ($firstStudent) {
            $responseStudents->assertSee($firstStudent->first_name);
        }

        // 2. Teachers Tab
        $responseTeachers = $this->actingAs($this->admin)->get(route('admin.schools.show', [
            'school' => $this->rootSchool->id,
            'tab' => 'teachers',
        ]));
        $responseTeachers->assertStatus(200);
        $responseTeachers->assertSee('Teaching Faculty');

        // 3. Accountants Tab
        $responseAccountants = $this->actingAs($this->admin)->get(route('admin.schools.show', [
            'school' => $this->rootSchool->id,
            'tab' => 'accountants',
        ]));
        $responseAccountants->assertStatus(200);
        $responseAccountants->assertSee('Accountants & Finance Staff', false);

        // 4. Parents & Guardians Tab (linking to student children)
        $responseParents = $this->actingAs($this->admin)->get(route('admin.schools.show', [
            'school' => $this->rootSchool->id,
            'tab' => 'parents',
        ]));
        $responseParents->assertStatus(200);
        $responseParents->assertSee('Parent Directory', false);
        $responseParents->assertSee('Enrolled Child Students');

        // 5. Child Branch Campuses Tab
        $responseBranches = $this->actingAs($this->admin)->get(route('admin.schools.show', [
            'school' => $this->rootSchool->id,
            'tab' => 'branches',
        ]));
        $responseBranches->assertStatus(200);
        $responseBranches->assertSee('Child Branch Campuses', false);
    }

    public function test_admin_can_access_create_school_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.schools.create'));

        $response->assertStatus(200);
        $response->assertSee('Register New Institution / Campus');
        $response->assertSee('parent_id');
        $response->assertSee($this->rootSchool->name);
    }

    public function test_admin_can_create_new_branch_school_with_parent_id(): void
    {
        $uniqueCode = 'TEST-BR-'.rand(100, 999);

        $payload = [
            'name' => 'Greenwood East Campus Branch',
            'code' => $uniqueCode,
            'parent_id' => $this->rootSchool->id,
            'phone' => '+91 99999 88888',
            'email' => 'east.campus@greenwood.test',
            'address' => '42 Eastern Expressway, Innovation Hub',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'package_tier' => 'standard',
            'billing_cycle' => 'annually',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.schools.store'), $payload);

        $createdSchool = School::where('code', $uniqueCode)->first();
        $this->assertNotNull($createdSchool);
        $this->assertEquals($this->rootSchool->id, $createdSchool->parent_id);
        $this->assertEquals('Greenwood East Campus Branch', $createdSchool->name);
        $this->assertEquals('standard', $createdSchool->package_tier);

        $response->assertRedirect(route('admin.schools.show', $createdSchool));
        $response->assertSessionHas('success');

        // Verify parent-child relationship
        $this->assertTrue($this->rootSchool->children->contains($createdSchool));
        $this->assertEquals($this->rootSchool->id, $createdSchool->parent->id);
    }

    public function test_admin_can_edit_and_update_school(): void
    {
        $branch = School::create([
            'name' => 'Temporary Branch To Edit',
            'code' => 'TEMP-EDIT-'.rand(100, 999),
            'parent_id' => $this->rootSchool->id,
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'package_tier' => 'starter',
            'billing_cycle' => 'monthly',
        ]);

        $editResponse = $this->actingAs($this->admin)->get(route('admin.schools.edit', $branch));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Edit School / Campus Settings');
        $editResponse->assertSee($branch->name);

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.schools.update', $branch), [
            'name' => 'Updated Branch Name',
            'code' => $branch->code,
            'parent_id' => $this->rootSchool->id,
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'package_tier' => 'premium',
            'billing_cycle' => 'annually',
        ]);

        $updateResponse->assertRedirect(route('admin.schools.show', $branch));
        $updateResponse->assertSessionHas('success');

        $branch->refresh();
        $this->assertEquals('Updated Branch Name', $branch->name);
        $this->assertEquals('premium', $branch->package_tier);
        $this->assertEquals('annually', $branch->billing_cycle);
    }

    public function test_admin_can_delete_branch_school(): void
    {
        $branch = School::create([
            'name' => 'Branch To Delete',
            'code' => 'DEL-'.rand(1000, 9999),
            'parent_id' => $this->rootSchool->id,
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'package_tier' => 'starter',
            'billing_cycle' => 'monthly',
        ]);

        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.schools.destroy', $branch));

        $deleteResponse->assertRedirect(route('admin.schools.index'));
        $deleteResponse->assertSessionHas('success');

        $this->assertNull(School::find($branch->id));
    }
}
