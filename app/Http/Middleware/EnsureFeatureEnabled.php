<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Support\PackagePricing;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $school = School::current();

        // If school exists and feature is explicitly disabled
        if ($school && ! $school->hasFeature($feature)) {
            $allFeatures = PackagePricing::allFeatures();
            $featureMeta = $allFeatures[$feature] ?? [
                'name' => ucfirst($feature),
                'description' => 'This module is currently not included in your school subscription tier.',
            ];

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Feature '{$featureMeta['name']}' is not enabled in your school's current subscription package.",
                    'feature' => $feature,
                    'package_tier' => $school->package_tier,
                ], 403);
            }

            // Find which tiers include this feature
            $allTiers = PackagePricing::allTiers();
            $suggestedTiers = [];
            foreach ($allTiers as $tKey => $tier) {
                if (in_array($feature, $tier['features'])) {
                    $suggestedTiers[] = $tier['name'];
                }
            }

            return response()->view('errors.feature-disabled', [
                'feature' => $feature,
                'featureMeta' => $featureMeta,
                'school' => $school,
                'suggestedTiers' => $suggestedTiers,
            ], 403);
        }

        return $next($request);
    }
}
