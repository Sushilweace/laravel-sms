<?php

namespace App\Providers;

use App\Models\School;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share current school instance and campus directory with all views
        View::composer('*', function ($view) {
            $view->with('currentSchool', School::current());
            $view->with('allSchools', School::orderBy('parent_id')->orderBy('name')->get());
        });

        // Blade feature directive: @feature('ai') ... @endfeature
        Blade::if('feature', function (string $feature) {
            $school = School::current();

            return $school ? $school->hasFeature($feature) : true;
        });
    }
}
