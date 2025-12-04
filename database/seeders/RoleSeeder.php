<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin - gets ALL permissions
        $superAdmin = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full access to everything',
                'is_default' => true
            ]
        );
        $superAdmin->permissions()->sync(Permission::pluck('id'));

        // Manager - gets most permissions except user/role management
        $manager = Role::updateOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'description' => 'Can manage vendors but not users/roles',
                'is_default' => true
            ]
        );
        $managerPermissions = Permission::where('group', 'vendors')->pluck('id');
        $manager->permissions()->sync($managerPermissions);

        // Viewer - only view permissions
        $viewer = Role::updateOrCreate(
            ['slug' => 'viewer'],
            [
                'name' => 'Viewer',
                'description' => 'Can only view, no edit access',
                'is_default' => true
            ]
        );
        $viewerPermissions = Permission::where('slug', 'like', 'view-%')->pluck('id');
        $viewer->permissions()->sync($viewerPermissions);
    }
}