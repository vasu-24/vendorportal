<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Vendor permissions
            ['name' => 'View Vendors', 'slug' => 'view-vendors', 'group' => 'vendors'],
            ['name' => 'Create Vendors', 'slug' => 'create-vendors', 'group' => 'vendors'],
            ['name' => 'Edit Vendors', 'slug' => 'edit-vendors', 'group' => 'vendors'],
            ['name' => 'Delete Vendors', 'slug' => 'delete-vendors', 'group' => 'vendors'],
            ['name' => 'Approve Vendors', 'slug' => 'approve-vendors', 'group' => 'vendors'],
            ['name' => 'Reject Vendors', 'slug' => 'reject-vendors', 'group' => 'vendors'],

            // User permissions
            ['name' => 'View Users', 'slug' => 'view-users', 'group' => 'users'],
            ['name' => 'Create Users', 'slug' => 'create-users', 'group' => 'users'],
            ['name' => 'Edit Users', 'slug' => 'edit-users', 'group' => 'users'],
            ['name' => 'Delete Users', 'slug' => 'delete-users', 'group' => 'users'],

            // Role permissions
            ['name' => 'View Roles', 'slug' => 'view-roles', 'group' => 'roles'],
            ['name' => 'Create Roles', 'slug' => 'create-roles', 'group' => 'roles'],
            ['name' => 'Edit Roles', 'slug' => 'edit-roles', 'group' => 'roles'],
            ['name' => 'Delete Roles', 'slug' => 'delete-roles', 'group' => 'roles'],

            // Settings permissions
            ['name' => 'Manage Settings', 'slug' => 'manage-settings', 'group' => 'settings'],
            ['name' => 'Manage Templates', 'slug' => 'manage-templates', 'group' => 'settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}