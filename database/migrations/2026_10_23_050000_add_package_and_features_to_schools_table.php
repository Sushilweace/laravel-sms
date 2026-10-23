<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('package_tier', 50)->default('enterprise')->after('currency');
            $table->string('billing_cycle', 20)->default('monthly')->after('package_tier');
            $table->decimal('package_charge', 12, 2)->default(0.00)->after('billing_cycle');
            $table->json('features')->nullable()->after('package_charge');
            $table->json('pricing_config')->nullable()->after('features');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'package_tier',
                'billing_cycle',
                'package_charge',
                'features',
                'pricing_config',
            ]);
        });
    }
};
