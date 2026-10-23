<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Support\Money;
use App\Support\PackagePricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolSettingsController extends Controller
{
    public function edit(): View
    {
        $school = School::current();
        if (! $school) {
            $school = School::create([
                'name' => 'My School',
                'code' => 'SCH-01',
                'timezone' => 'Asia/Kolkata',
                'currency' => 'INR',
                'package_tier' => PackagePricing::TIER_ENTERPRISE,
                'billing_cycle' => 'monthly',
            ]);
        }

        $allFeatures = PackagePricing::allFeatures();
        $allTiers = PackagePricing::allTiers();
        $studentCount = $school->getActiveStudentCount();
        $currentQuote = $school->calculatePackageCharge();

        // Ensure active features map
        $activeFeatures = [];
        $savedFeatures = $school->features ?? [];
        foreach ($allFeatures as $key => $meta) {
            if (! empty($savedFeatures)) {
                $activeFeatures[$key] = ! empty($savedFeatures[$key]);
            } else {
                $activeFeatures[$key] = true; // default all enabled
            }
        }

        return view('settings.school', compact(
            'school',
            'allFeatures',
            'allTiers',
            'studentCount',
            'currentQuote',
            'activeFeatures'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $school = School::current();
        if (! $school) {
            abort(404, 'School not found.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:20|unique:schools,code,'.$school->id,
            'phone' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'timezone' => 'required|string|max:50',
            'currency' => 'required|string|size:3',
            'package_tier' => 'required|string|in:starter,standard,premium,enterprise,custom',
            'billing_cycle' => 'required|string|in:monthly,annually',
            'features' => 'nullable|array',
        ]);

        $tier = $data['package_tier'];
        $billingCycle = $data['billing_cycle'];
        $allFeaturesCatalog = PackagePricing::allFeatures();
        $tiersCatalog = PackagePricing::allTiers();

        // Determine enabled features
        $enabledFeatures = [];
        if ($tier === PackagePricing::TIER_CUSTOM) {
            foreach ($allFeaturesCatalog as $fKey => $fMeta) {
                $enabledFeatures[$fKey] = ! empty($data['features'][$fKey]);
            }
        } else {
            // Apply tier's standard feature set
            $tierFeatures = $tiersCatalog[$tier]['features'] ?? [];
            foreach ($allFeaturesCatalog as $fKey => $fMeta) {
                // If submitted explicit toggles exist, allow them, else use tier defaults
                if (isset($data['features'])) {
                    $enabledFeatures[$fKey] = ! empty($data['features'][$fKey]);
                } else {
                    $enabledFeatures[$fKey] = in_array($fKey, $tierFeatures);
                }
            }
        }

        $studentCount = $school->getActiveStudentCount();
        $quote = PackagePricing::calculateCharge($tier, $enabledFeatures, $studentCount, $billingCycle);

        $school->update([
            'name' => $data['name'],
            'code' => $data['code'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'timezone' => $data['timezone'],
            'currency' => $data['currency'],
            'package_tier' => $tier,
            'billing_cycle' => $billingCycle,
            'package_charge' => $quote['final_charge'],
            'features' => $enabledFeatures,
            'pricing_config' => $quote,
        ]);

        $formattedCharge = Money::format($quote['final_charge'], $school->currency === 'INR' ? '₹' : $school->currency);

        return back()->with('success', "School settings & subscription package updated! New charge: {$formattedCharge} ({$billingCycle}).");
    }

    public function calculateApi(Request $request): JsonResponse
    {
        $school = School::current();
        $studentCount = $school ? $school->getActiveStudentCount() : 0;

        $tier = $request->input('package_tier', PackagePricing::TIER_ENTERPRISE);
        $billingCycle = $request->input('billing_cycle', 'monthly');
        $rawFeatures = $request->input('features', []);

        $allFeatures = PackagePricing::allFeatures();
        $enabledFeatures = [];
        foreach ($allFeatures as $key => $meta) {
            $enabledFeatures[$key] = ! empty($rawFeatures[$key]);
        }

        $quote = PackagePricing::calculateCharge($tier, $enabledFeatures, $studentCount, $billingCycle);

        return response()->json([
            'success' => true,
            'quote' => $quote,
            'formatted' => [
                'base_monthly' => Money::format($quote['base_monthly']),
                'student_charge_monthly' => Money::format($quote['student_charge_monthly']),
                'monthly_subtotal' => Money::format($quote['monthly_subtotal']),
                'gross_total' => Money::format($quote['gross_total']),
                'discount_amount' => Money::format($quote['discount_amount']),
                'final_charge' => Money::format($quote['final_charge']),
                'effective_monthly' => Money::format($quote['effective_monthly']),
            ],
        ]);
    }
}
