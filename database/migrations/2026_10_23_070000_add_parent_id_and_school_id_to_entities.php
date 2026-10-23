<?php

use App\Models\School;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. schools table parent_id (hierarchy for branch schools)
        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('id')->constrained('schools')->nullOnDelete();
            }
        });

        // 2. users table school_id
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'school_id')) {
                $table->foreignId('school_id')->nullable()->after('id')->constrained('schools')->nullOnDelete();
            }
        });

        // 3. staff table school_id
        Schema::table('staff', function (Blueprint $table) {
            if (! Schema::hasColumn('staff', 'school_id')) {
                $table->foreignId('school_id')->nullable()->after('id')->constrained('schools')->nullOnDelete();
            }
        });

        // 4. students table school_id
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'school_id')) {
                $table->foreignId('school_id')->nullable()->after('id')->constrained('schools')->nullOnDelete();
            }
        });

        // 5. academic_years table school_id
        Schema::table('academic_years', function (Blueprint $table) {
            if (! Schema::hasColumn('academic_years', 'school_id')) {
                $table->foreignId('school_id')->nullable()->after('id')->constrained('schools')->nullOnDelete();
            }
        });

        // Backfill existing records to first school if present
        $firstSchool = DB::table('schools')->orderBy('id')->first();
        if ($firstSchool) {
            DB::table('users')->whereNull('school_id')->update(['school_id' => $firstSchool->id]);
            DB::table('staff')->whereNull('school_id')->update(['school_id' => $firstSchool->id]);
            DB::table('students')->whereNull('school_id')->update(['school_id' => $firstSchool->id]);
            DB::table('academic_years')->whereNull('school_id')->update(['school_id' => $firstSchool->id]);
        }
    }

    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            if (Schema::hasColumn('academic_years', 'school_id')) {
                $table->dropForeign(['school_id']);
                $table->dropColumn('school_id');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'school_id')) {
                $table->dropForeign(['school_id']);
                $table->dropColumn('school_id');
            }
        });

        Schema::table('staff', function (Blueprint $table) {
            if (Schema::hasColumn('staff', 'school_id')) {
                $table->dropForeign(['school_id']);
                $table->dropColumn('school_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'school_id')) {
                $table->dropForeign(['school_id']);
                $table->dropColumn('school_id');
            }
        });

        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
        });
    }
};
