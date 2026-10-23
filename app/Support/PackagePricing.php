<?php

namespace App\Support;

class PackagePricing
{
    public const FEATURE_STUDENTS = 'students';
    public const FEATURE_STAFF = 'staff';
    public const FEATURE_ATTENDANCE = 'attendance';
    public const FEATURE_EXAMS = 'exams';
    public const FEATURE_REPORTS = 'reports';
    public const FEATURE_FEES = 'fees';
    public const FEATURE_NOTICES = 'notices';
    public const FEATURE_AI = 'ai';

    public const TIER_STARTER = 'starter';
    public const TIER_STANDARD = 'standard';
    public const TIER_PREMIUM = 'premium';
    public const TIER_ENTERPRISE = 'enterprise';
    public const TIER_CUSTOM = 'custom';

    /**
     * Complete definition of all modular features.
     */
    public static function allFeatures(): array
    {
        return [
            self::FEATURE_STUDENTS => [
                'key' => self::FEATURE_STUDENTS,
                'name' => 'Student Information System (SIS)',
                'category' => 'Core Platform',
                'description' => 'Admissions flow, student profiles, guardian links, documents and academic promotions.',
                'icon' => 'academic-cap',
                'per_student_monthly' => '10.00',
                'is_core' => true,
            ],
            self::FEATURE_STAFF => [
                'key' => self::FEATURE_STAFF,
                'name' => 'Staff & Faculty Management',
                'category' => 'Core Platform',
                'description' => 'Staff directory, designations, qualifications, and section teaching assignments.',
                'icon' => 'user-group',
                'per_student_monthly' => '5.00',
                'is_core' => true,
            ],
            self::FEATURE_ATTENDANCE => [
                'key' => self::FEATURE_ATTENDANCE,
                'name' => 'Daily Attendance & Calendar',
                'category' => 'Operations',
                'description' => 'Rapid attendance sheet with bulk mark, 2-day locking, leave management, and monthly rollups.',
                'icon' => 'calendar-days',
                'per_student_monthly' => '10.00',
                'is_core' => false,
            ],
            self::FEATURE_EXAMS => [
                'key' => self::FEATURE_EXAMS,
                'name' => 'Exams & Marks Entry',
                'category' => 'Academics',
                'description' => 'Exam schedules, rapid marks entry grid, CBSE grade scales, and locked exam audit changes.',
                'icon' => 'clipboard-document-check',
                'per_student_monthly' => '12.00',
                'is_core' => false,
            ],
            self::FEATURE_REPORTS => [
                'key' => self::FEATURE_REPORTS,
                'name' => 'Report Cards & Analytics',
                'category' => 'Academics',
                'description' => 'Automated result computation, rank generation, and downloadable official DomPDF report cards.',
                'icon' => 'chart-bar',
                'per_student_monthly' => '8.00',
                'is_core' => false,
            ],
            self::FEATURE_FEES => [
                'key' => self::FEATURE_FEES,
                'name' => 'Fee Management & Invoicing',
                'category' => 'Finance',
                'description' => 'Custom fee structures, sibling concessions, counter collection, PDF receipts, and defaulters ledger.',
                'icon' => 'credit-card',
                'per_student_monthly' => '15.00',
                'is_core' => false,
            ],
            self::FEATURE_NOTICES => [
                'key' => self::FEATURE_NOTICES,
                'name' => 'Notice Board & Broadcasts',
                'category' => 'Communication',
                'description' => 'Targeted circulars, parental acknowledgement logs, and multi-channel notification dispatcher.',
                'icon' => 'megaphone',
                'per_student_monthly' => '5.00',
                'is_core' => false,
            ],
            self::FEATURE_AI => [
                'key' => self::FEATURE_AI,
                'name' => 'AI Assistive Studio & Drafter',
                'category' => 'Intelligence',
                'description' => 'AI report card remarks, circular drafter, courteous fee reminders, and human review studio.',
                'icon' => 'sparkles',
                'per_student_monthly' => '25.00',
                'is_core' => false,
            ],
        ];
    }

    /**
     * Package tier configurations and base rates.
     */
    public static function allTiers(): array
    {
        return [
            self::TIER_STARTER => [
                'key' => self::TIER_STARTER,
                'name' => 'Starter Pack',
                'tagline' => 'Essential administration for small & growing schools',
                'base_monthly' => '3000.00',
                'per_student_monthly' => '20.00',
                'features' => [
                    self::FEATURE_STUDENTS,
                    self::FEATURE_STAFF,
                    self::FEATURE_ATTENDANCE,
                ],
                'highlight' => false,
            ],
            self::TIER_STANDARD => [
                'key' => self::TIER_STANDARD,
                'name' => 'Standard Academic',
                'tagline' => 'Full academic lifecycle with attendance, exams, and report cards',
                'base_monthly' => '6000.00',
                'per_student_monthly' => '35.00',
                'features' => [
                    self::FEATURE_STUDENTS,
                    self::FEATURE_STAFF,
                    self::FEATURE_ATTENDANCE,
                    self::FEATURE_EXAMS,
                    self::FEATURE_REPORTS,
                    self::FEATURE_NOTICES,
                ],
                'highlight' => false,
            ],
            self::TIER_PREMIUM => [
                'key' => self::TIER_PREMIUM,
                'name' => 'Premium Campus',
                'tagline' => 'Comprehensive administration including complete fee invoicing & collections',
                'base_monthly' => '10000.00',
                'per_student_monthly' => '50.00',
                'features' => [
                    self::FEATURE_STUDENTS,
                    self::FEATURE_STAFF,
                    self::FEATURE_ATTENDANCE,
                    self::FEATURE_EXAMS,
                    self::FEATURE_REPORTS,
                    self::FEATURE_FEES,
                    self::FEATURE_NOTICES,
                ],
                'highlight' => true,
            ],
            self::TIER_ENTERPRISE => [
                'key' => self::TIER_ENTERPRISE,
                'name' => 'Enterprise & AI Suite',
                'tagline' => 'Full suite including cutting-edge AI assistance, unlimited records, & top priority',
                'base_monthly' => '15000.00',
                'per_student_monthly' => '75.00',
                'features' => [
                    self::FEATURE_STUDENTS,
                    self::FEATURE_STAFF,
                    self::FEATURE_ATTENDANCE,
                    self::FEATURE_EXAMS,
                    self::FEATURE_REPORTS,
                    self::FEATURE_FEES,
                    self::FEATURE_NOTICES,
                    self::FEATURE_AI,
                ],
                'highlight' => false,
            ],
            self::TIER_CUSTOM => [
                'key' => self::TIER_CUSTOM,
                'name' => 'Custom Modular Plan',
                'tagline' => 'Pick and choose specific modules tailored to your school budget',
                'base_monthly' => '2000.00',
                'per_student_monthly' => '0.00', // dynamic based on features
                'features' => [],
                'highlight' => false,
            ],
        ];
    }

    /**
     * Calculate comprehensive quote and package charge breakdown.
     */
    public static function calculateCharge(
        string $tier,
        array $enabledFeatures,
        int $studentCount,
        string $billingCycle = 'monthly'
    ): array {
        $tiers = self::allTiers();
        $featuresCatalog = self::allFeatures();

        $tierConfig = $tiers[$tier] ?? $tiers[self::TIER_ENTERPRISE];
        $baseMonthly = $tierConfig['base_monthly'];

        // Determine effective per-student rate
        if ($tier === self::TIER_CUSTOM) {
            $studentRate = '0.00';
            foreach ($enabledFeatures as $featKey => $isEnabled) {
                if ($isEnabled && isset($featuresCatalog[$featKey])) {
                    $studentRate = Money::add($studentRate, $featuresCatalog[$featKey]['per_student_monthly']);
                }
            }
        } else {
            $studentRate = $tierConfig['per_student_monthly'];
        }

        // Student monthly charge = count * per_student_rate
        $studentChargeMonthly = Money::multiply($studentRate, (string) max(0, $studentCount));

        // Subtotal monthly = base + student charge
        $monthlySubtotal = Money::add($baseMonthly, $studentChargeMonthly);

        $isAnnual = strtolower($billingCycle) === 'annually';
        $months = $isAnnual ? 12 : 1;

        $grossTotal = Money::multiply($monthlySubtotal, (string) $months);

        // Annual discount: 15% discount for full annual commitment
        $discountAmount = '0.00';
        $discountPercentage = 0;
        if ($isAnnual) {
            $discountPercentage = 15;
            $discountAmount = Money::percentage($grossTotal, 15);
        }

        $finalCharge = Money::subtract($grossTotal, $discountAmount);
        $effectiveMonthly = $isAnnual ? Money::divide($finalCharge, '12') : $finalCharge;

        return [
            'tier' => $tier,
            'tier_name' => $tierConfig['name'],
            'billing_cycle' => $billingCycle,
            'student_count' => $studentCount,
            'base_monthly' => $baseMonthly,
            'per_student_rate' => $studentRate,
            'student_charge_monthly' => $studentChargeMonthly,
            'monthly_subtotal' => $monthlySubtotal,
            'months' => $months,
            'gross_total' => $grossTotal,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'final_charge' => $finalCharge,
            'effective_monthly' => $effectiveMonthly,
            'enabled_features_count' => count(array_filter($enabledFeatures)),
        ];
    }
}
