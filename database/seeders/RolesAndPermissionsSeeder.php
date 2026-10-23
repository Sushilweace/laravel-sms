<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Academic
            'academic.manage',
            'academic.view',
            // Students
            'students.view',
            'students.create',
            'students.update',
            'students.delete',
            'students.promote',
            'students.export',
            // Staff
            'staff.view',
            'staff.manage',
            // Attendance
            'attendance.view',
            'attendance.mark',
            'attendance.edit-past',
            'attendance.reports',
            // Exams & Marks
            'exams.view',
            'exams.manage',
            'marks.enter',
            'marks.edit-locked',
            'results.view',
            'results.publish',
            // Fees
            'fees.view',
            'fees.structure',
            'fees.generate',
            'fees.collect',
            'fees.reverse',
            'fees.discount',
            'fees.report',
            // Notices
            'notices.view',
            'notices.create',
            'notices.approve',
            'notices.delete',
            'notifications.broadcast',
            // AI
            'ai.generate',
            'ai.review',
            // Audit
            'audit.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Roles
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        // Super Admin gets all permissions

        $schoolAdmin = Role::firstOrCreate(['name' => 'School Admin']);
        $schoolAdmin->syncPermissions([
            'academic.manage', 'academic.view',
            'students.view', 'students.create', 'students.update', 'students.delete', 'students.promote', 'students.export',
            'staff.view', 'staff.manage',
            'attendance.view', 'attendance.mark', 'attendance.edit-past', 'attendance.reports',
            'exams.view', 'exams.manage', 'marks.enter', 'marks.edit-locked', 'results.view', 'results.publish',
            'fees.view', 'fees.structure', 'fees.generate', 'fees.collect', 'fees.reverse', 'fees.discount', 'fees.report',
            'notices.view', 'notices.create', 'notices.approve', 'notices.delete', 'notifications.broadcast',
            'ai.generate', 'ai.review',
            'audit.view',
        ]);

        $teacher = Role::firstOrCreate(['name' => 'Teacher']);
        $teacher->syncPermissions([
            'academic.view',
            'students.view',
            'attendance.view', 'attendance.mark',
            'exams.view', 'marks.enter', 'results.view',
            'notices.view', 'notices.create',
            'ai.generate',
        ]);

        $accountant = Role::firstOrCreate(['name' => 'Accountant']);
        $accountant->syncPermissions([
            'students.view',
            'fees.view', 'fees.structure', 'fees.generate', 'fees.collect', 'fees.report',
            'notices.view',
        ]);

        $student = Role::firstOrCreate(['name' => 'Student']);
        $student->syncPermissions([
            'students.view',
            'attendance.view',
            'results.view',
            'fees.view',
            'notices.view',
        ]);

        $parent = Role::firstOrCreate(['name' => 'Parent']);
        $parent->syncPermissions([
            'students.view',
            'attendance.view',
            'results.view',
            'fees.view',
            'notices.view',
        ]);
    }
}
