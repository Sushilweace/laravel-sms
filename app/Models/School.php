<?php

namespace App\Models;

use App\Support\PackagePricing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'code',
        'address',
        'phone',
        'email',
        'logo_path',
        'timezone',
        'currency',
        'package_tier',
        'billing_cycle',
        'package_charge',
        'features',
        'pricing_config',
    ];

    protected $casts = [
        'features' => 'array',
        'pricing_config' => 'array',
        'package_charge' => 'decimal:2',
    ];

    public static function current(): ?static
    {
        // 1. Check explicit session override (active campus switcher)
        if (function_exists('session') && session()->has('active_school_id')) {
            $sessionSchoolId = session('active_school_id');
            if ($sessionSchoolId && $school = static::find($sessionSchoolId)) {
                return $school;
            }
        }

        // 2. Check logged-in user assigned school
        if (auth()->check() && auth()->user()->school_id) {
            if ($userSchool = static::find(auth()->user()->school_id)) {
                return $userSchool;
            }
        }

        // 3. Fallback to primary root school
        return static::first();
    }

    /**
     * Determine if a specific modular feature is enabled for this school.
     */
    public function hasFeature(string $feature): bool
    {
        $features = $this->features;

        // If no explicit feature mapping configured, default to all enabled
        if (empty($features) || ! is_array($features)) {
            return true;
        }

        return ! empty($features[$feature]);
    }

    /**
     * Get complete catalog of features with current enabled status for this school.
     */
    public function getFeaturesList(): array
    {
        $catalog = PackagePricing::allFeatures();
        $enabledMap = $this->features ?? [];

        foreach ($catalog as $key => $meta) {
            $catalog[$key]['enabled'] = ! empty($enabledMap[$key]) || (empty($this->features) && ! isset($enabledMap[$key]));
        }

        return $catalog;
    }

    /**
     * Count currently active student enrollments.
     */
    public function getActiveStudentCount(): int
    {
        $currentYear = AcademicYear::current();

        return Enrollment::query()
            ->when($currentYear, fn ($q) => $q->where('academic_year_id', $currentYear->id))
            ->where('status', 'active')
            ->count();
    }

    /**
     * Calculate package charges based on tier, selected features, and student population.
     */
    public function calculatePackageCharge(?string $tier = null, ?array $features = null, ?string $cycle = null): array
    {
        $tier = $tier ?? $this->package_tier ?? PackagePricing::TIER_ENTERPRISE;
        $cycle = $cycle ?? $this->billing_cycle ?? 'monthly';

        $activeFeatures = $features ?? $this->features ?? [];
        if (empty($activeFeatures)) {
            // If empty, take default tier features
            $tiers = PackagePricing::allTiers();
            $tierFeatures = $tiers[$tier]['features'] ?? array_keys(PackagePricing::allFeatures());
            $activeFeatures = array_fill_keys($tierFeatures, true);
        }

        $studentCount = $this->getActiveStudentCount();

        return PackagePricing::calculateCharge($tier, $activeFeatures, $studentCount, $cycle);
    }

    /**
     * Recalculate package charge and persist to database.
     */
    public function recalculateAndSaveCharge(): array
    {
        $quote = $this->calculatePackageCharge();
        $this->update(['package_charge' => $quote['final_charge']]);

        return $quote;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(School::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(School::class, 'parent_id');
    }

    public function branches(): HasMany
    {
        return $this->children();
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Staff::class)->where(function ($q) {
            $q->where('designation', 'like', '%teacher%')
                ->orWhereHas('user.roles', fn ($r) => $r->where('name', 'Teacher'));
        });
    }

    public function accountants(): HasMany
    {
        return $this->hasMany(Staff::class)->where(function ($q) {
            $q->where('designation', 'like', '%accountant%')
                ->orWhere('designation', 'like', '%bursar%')
                ->orWhereHas('user.roles', fn ($r) => $r->where('name', 'Accountant'));
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }
}
