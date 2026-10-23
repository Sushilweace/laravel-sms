<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Domain tables to augment with audit user tracking.
     */
    protected array $tables = [
        'schools',
        'academic_years',
        'school_classes',
        'subjects',
        'staff',
        'sections',
        'class_subjects',
        'teaching_assignments',
        'students',
        'guardians',
        'enrollments',
        'promotion_runs',
        'attendances',
        'attendance_days',
        'leave_requests',
        'grade_scales',
        'grades',
        'exams',
        'exam_subjects',
        'marks',
        'fee_heads',
        'fee_structures',
        'concessions',
        'invoices',
        'payments',
        'notices',
        'notification_logs',
        'ai_requests',
        'ai_suggestions',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'created_by')) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                }

                if (! Schema::hasColumn($tableName, 'updated_by')) {
                    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $columnsToDrop = [];
                if (Schema::hasColumn($tableName, 'updated_by')) {
                    $columnsToDrop[] = 'updated_by';
                }
                if (Schema::hasColumn($tableName, 'created_by') && ! in_array($tableName, ['exams'])) {
                    $columnsToDrop[] = 'created_by';
                }

                if (! empty($columnsToDrop)) {
                    $table->dropForeign(array_map(fn ($c) => "{$tableName}_{$c}_foreign", $columnsToDrop));
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
