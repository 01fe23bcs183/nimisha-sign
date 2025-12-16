<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view_staff',
            'create_staff',
            'edit_staff',
            'delete_staff',
            'view_office_locations',
            'create_office_locations',
            'edit_office_locations',
            'delete_office_locations',
            'view_divisions',
            'create_divisions',
            'edit_divisions',
            'delete_divisions',
            'view_job_titles',
            'create_job_titles',
            'edit_job_titles',
            'delete_job_titles',
            'view_leaves',
            'create_leaves',
            'edit_leaves',
            'delete_leaves',
            'approve_leaves',
            'view_attendance',
            'create_attendance',
            'edit_attendance',
            'delete_attendance',
            'view_payroll',
            'create_payroll',
            'edit_payroll',
            'delete_payroll',
            'view_reports',
            'view_settings',
            'edit_settings',
            'view_announcements',
            'create_announcements',
            'edit_announcements',
            'delete_announcements',
            'view_events',
            'create_events',
            'edit_events',
            'delete_events',
            'view_documents',
            'create_documents',
            'edit_documents',
            'delete_documents',
            'view_policies',
            'create_policies',
            'edit_policies',
            'delete_policies',
            'view_travels',
            'create_travels',
            'edit_travels',
            'delete_travels',
            'approve_travels',
            'view_complaints',
            'create_complaints',
            'edit_complaints',
            'delete_complaints',
            'resolve_complaints',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $administratorRole = Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
        $administratorRole->syncPermissions($permissions);

        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $managerRole->syncPermissions([
            'view_staff',
            'create_staff',
            'edit_staff',
            'view_office_locations',
            'view_divisions',
            'view_job_titles',
            'view_leaves',
            'approve_leaves',
            'view_attendance',
            'create_attendance',
            'edit_attendance',
            'view_payroll',
            'view_reports',
            'view_announcements',
            'create_announcements',
            'view_events',
            'create_events',
            'view_documents',
            'view_policies',
            'view_travels',
            'approve_travels',
            'view_complaints',
            'resolve_complaints',
        ]);

        $hrOfficerRole = Role::firstOrCreate(['name' => 'hr_officer', 'guard_name' => 'web']);
        $hrOfficerRole->syncPermissions([
            'view_staff',
            'create_staff',
            'edit_staff',
            'view_office_locations',
            'view_divisions',
            'view_job_titles',
            'view_leaves',
            'create_leaves',
            'edit_leaves',
            'approve_leaves',
            'view_attendance',
            'create_attendance',
            'edit_attendance',
            'view_payroll',
            'create_payroll',
            'edit_payroll',
            'view_reports',
            'view_announcements',
            'create_announcements',
            'edit_announcements',
            'view_events',
            'create_events',
            'edit_events',
            'view_documents',
            'create_documents',
            'view_policies',
            'view_travels',
            'create_travels',
            'approve_travels',
            'view_complaints',
            'create_complaints',
            'resolve_complaints',
        ]);

        $staffMemberRole = Role::firstOrCreate(['name' => 'staff_member', 'guard_name' => 'web']);
        $staffMemberRole->syncPermissions([
            'view_staff',
            'view_leaves',
            'create_leaves',
            'view_attendance',
            'view_announcements',
            'view_events',
            'view_documents',
            'view_policies',
            'view_travels',
            'create_travels',
            'view_complaints',
            'create_complaints',
        ]);
    }
}
