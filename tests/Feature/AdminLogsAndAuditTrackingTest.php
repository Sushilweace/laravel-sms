<?php

namespace Tests\Feature;

use App\Models\NotificationLog;
use App\Models\Student;
use App\Models\User;
use Tests\TestCase;

class AdminLogsAndAuditTrackingTest extends TestCase
{
    public function test_admin_can_access_error_log_viewer_and_download_file(): void
    {
        $admin = User::where('email', 'superadmin@school.test')->first();
        $this->assertNotNull($admin);

        // 1. Web View
        $response = $this->actingAs($admin)->get('/admin/logs/errors');
        $response->assertStatus(200);
        $response->assertSee('System Error', false);
        $response->assertSee('Download Log File');

        // 2. Download Log File
        $downloadResp = $this->actingAs($admin)->get('/admin/logs/errors/download');
        $downloadResp->assertStatus(200);

        // 3. Clear Log File
        $clearResp = $this->actingAs($admin)->post('/admin/logs/errors/clear');
        $clearResp->assertRedirect();
        $clearResp->assertSessionHas('success');
    }

    public function test_admin_can_view_mail_logs_and_resend_notification(): void
    {
        $admin = User::where('email', 'superadmin@school.test')->first();
        $this->assertNotNull($admin);

        // Create sample notification log
        $log = NotificationLog::create([
            'recipient_id' => $admin->id,
            'channel' => 'mail',
            'template_key' => 'general_notice',
            'title' => 'Important Test Announcement',
            'content' => 'Please be advised that term exams begin on Monday.',
            'status' => 'delivered',
            'sent_at' => now(),
        ]);

        // 1. View Logs Page
        $response = $this->actingAs($admin)->get('/admin/logs/mail');
        $response->assertStatus(200);
        $response->assertSee('Mail, SMS', false);
        $response->assertSee('Important Test Announcement');

        // 2. JSON Details Modal Endpoint
        $jsonResp = $this->actingAs($admin)->getJson("/admin/logs/mail/{$log->id}");
        $jsonResp->assertStatus(200);
        $jsonResp->assertJsonPath('title', 'Important Test Announcement');
        $jsonResp->assertJsonPath('recipient_name', $admin->name);

        // 3. Resend Action
        $resendResp = $this->actingAs($admin)->post("/admin/logs/mail/{$log->id}/resend");
        $resendResp->assertRedirect();
        $resendResp->assertSessionHas('success');

        // 4. Test Dispatch Endpoint
        $testResp = $this->actingAs($admin)->post('/admin/logs/mail/test', [
            'recipient_id' => $admin->id,
            'channel' => 'mail',
            'title' => 'Live Test Email Header',
            'content' => 'Verification of outgoing communications.',
        ]);
        $testResp->assertRedirect();
        $testResp->assertSessionHas('success');

        $this->assertDatabaseHas('notification_logs', [
            'title' => 'Live Test Email Header',
            'recipient_id' => $admin->id,
        ]);
    }

    public function test_user_trackable_automatically_sets_created_by_and_updated_by(): void
    {
        $admin = User::where('email', 'superadmin@school.test')->first();
        $this->assertNotNull($admin);

        // Authenticate as Admin
        $this->actingAs($admin);

        // Create student without explicitly specifying created_by/updated_by
        $student = Student::create([
            'admission_no' => 'ADM-AUDIT-001',
            'first_name' => 'Kavya',
            'last_name' => 'Nair',
            'gender' => 'female',
            'date_of_birth' => '2015-06-15',
            'status' => 'active',
        ]);

        // Assert created_by automatically populated with authenticated user ID
        $this->assertEquals($admin->id, $student->created_by);
        $this->assertEquals($admin->id, $student->updated_by);
        $this->assertNotNull($student->created_at);
        $this->assertNotNull($student->updated_at);

        // Assert relationships
        $this->assertNotNull($student->creator);
        $this->assertEquals($admin->name, $student->creator->name);

        // Update with another user
        $principal = User::where('email', 'principal@school.test')->first();
        $this->actingAs($principal);

        $student->update(['last_name' => 'Nair-Verma']);

        $student->refresh();
        $this->assertEquals($admin->id, $student->created_by);
        $this->assertEquals($principal->id, $student->updated_by);
        $this->assertEquals($principal->name, $student->updater->name);

        // Clean up
        $student->forceDelete();
    }
}
