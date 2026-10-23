<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Support\PackagePricing;
use Tests\TestCase;

class SchoolFeatureAndPackageTest extends TestCase
{
    protected function tearDown(): void
    {
        $school = School::first();
        if ($school) {
            $school->update([
                'package_tier' => 'enterprise',
                'billing_cycle' => 'monthly',
                'features' => null,
            ]);
        }

        parent::tearDown();
    }

    public function test_default_school_has_all_features_enabled(): void
    {
        $school = School::first();

        $this->assertNotNull($school);
        $this->assertTrue($school->hasFeature('ai'));
        $this->assertTrue($school->hasFeature('fees'));
        $this->assertTrue($school->hasFeature('exams'));
        $this->assertTrue($school->hasFeature('attendance'));
    }

    public function test_package_pricing_calculation(): void
    {
        // 10 students on Starter monthly: 3000 base + (10 * 20) = 3200
        $quote = PackagePricing::calculateCharge(PackagePricing::TIER_STARTER, [], 10, 'monthly');
        $this->assertEquals('3200.00', $quote['final_charge']);

        // 10 students on Enterprise annually: (15000 base + (10 * 75)) * 12 = 15750 * 12 = 189000
        // 15% discount on 189000 = 28350 => 160650
        $annualQuote = PackagePricing::calculateCharge(PackagePricing::TIER_ENTERPRISE, [], 10, 'annually');
        $this->assertEquals('160650.00', $annualQuote['final_charge']);
        $this->assertEquals('28350.00', $annualQuote['discount_amount']);
    }

    public function test_disabling_feature_blocks_access_to_module(): void
    {
        $school = School::first();
        $school->update([
            'package_tier' => 'starter',
            'features' => [
                'ai' => false,
                'students' => true,
                'attendance' => true,
            ],
        ]);

        $admin = User::where('email', 'superadmin@school.test')->first();

        // Attempting to access disabled AI assistant module
        $response = $this->actingAs($admin)->get('/ai');

        $response->assertStatus(403);
        $response->assertSee('Module Not Active In Current Package');
    }

    public function test_enabling_feature_restores_access(): void
    {
        $school = School::first();
        $school->update([
            'package_tier' => 'enterprise',
            'features' => [
                'ai' => true,
                'students' => true,
            ],
        ]);

        $admin = User::where('email', 'superadmin@school.test')->first();

        $response = $this->actingAs($admin)->get('/ai');
        $response->assertStatus(200);
    }

    public function test_admin_can_update_package_and_features_via_settings_screen(): void
    {
        $admin = User::where('email', 'superadmin@school.test')->first();
        $school = School::first();

        $response = $this->actingAs($admin)->post('/settings/school', [
            'name' => 'Greenwood International School Updated',
            'code' => $school->code,
            'phone' => '+91 98765 00000',
            'email' => 'admin@greenwood.test',
            'address' => 'Updated Address',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'package_tier' => 'premium',
            'billing_cycle' => 'annually',
            'features' => [
                'students' => '1',
                'staff' => '1',
                'attendance' => '1',
                'exams' => '1',
                'reports' => '1',
                'fees' => '1',
                'notices' => '1',
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $school->refresh();
        $this->assertEquals('Greenwood International School Updated', $school->name);
        $this->assertEquals('premium', $school->package_tier);
        $this->assertEquals('annually', $school->billing_cycle);
        $this->assertTrue($school->package_charge > 0);
    }

    public function test_package_calculation_api_returns_json_quote(): void
    {
        $admin = User::where('email', 'superadmin@school.test')->first();

        $response = $this->actingAs($admin)->postJson('/settings/school/calculate', [
            'package_tier' => 'starter',
            'billing_cycle' => 'monthly',
            'features' => [
                'students' => 1,
                'staff' => 1,
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'quote' => [
                'tier',
                'final_charge',
                'student_count',
            ],
            'formatted' => [
                'final_charge',
            ],
        ]);
    }
}
