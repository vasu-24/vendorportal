<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================
        // CREATE ALL 45 PERMISSIONS
        // =====================================================
        
        $permissions = [
            // =====================================================
            // VENDOR PERMISSIONS (8)
            // =====================================================
            ['name' => 'View Vendors', 'slug' => 'view-vendors', 'group' => 'vendors'],
            ['name' => 'Create Vendors', 'slug' => 'create-vendors', 'group' => 'vendors'],
            ['name' => 'Edit Vendors', 'slug' => 'edit-vendors', 'group' => 'vendors'],
            ['name' => 'Delete Vendors', 'slug' => 'delete-vendors', 'group' => 'vendors'],
            ['name' => 'Approve Vendors', 'slug' => 'approve-vendors', 'group' => 'vendors'],
            ['name' => 'Reject Vendors', 'slug' => 'reject-vendors', 'group' => 'vendors'],
            ['name' => 'Import Vendors', 'slug' => 'import-vendors', 'group' => 'vendors'],
            ['name' => 'Sync Vendors', 'slug' => 'sync-vendors', 'group' => 'vendors'],
            
            // =====================================================
            // CONTRACT PERMISSIONS (4)
            // =====================================================
            ['name' => 'View Contracts', 'slug' => 'view-contracts', 'group' => 'contracts'],
            ['name' => 'Create Contracts', 'slug' => 'create-contracts', 'group' => 'contracts'],
            ['name' => 'Edit Contracts', 'slug' => 'edit-contracts', 'group' => 'contracts'],
            ['name' => 'Delete Contracts', 'slug' => 'delete-contracts', 'group' => 'contracts'],
            
            // =====================================================
            // INVOICE PERMISSIONS (9)
            // =====================================================
            ['name' => 'View Invoices', 'slug' => 'view-invoices', 'group' => 'invoices'],
            ['name' => 'Create Invoices', 'slug' => 'create-invoices', 'group' => 'invoices'],
            ['name' => 'Edit Invoices', 'slug' => 'edit-invoices', 'group' => 'invoices'],
            ['name' => 'Delete Invoices', 'slug' => 'delete-invoices', 'group' => 'invoices'],
            ['name' => 'Review Invoices', 'slug' => 'review-invoices', 'group' => 'invoices'],
            ['name' => 'Approve Invoices', 'slug' => 'approve-invoices', 'group' => 'invoices'],
            ['name' => 'Reject Invoices', 'slug' => 'reject-invoices', 'group' => 'invoices'],
            ['name' => 'Pay Invoices', 'slug' => 'pay-invoices', 'group' => 'invoices'],
            ['name' => 'Sync Invoices', 'slug' => 'sync-invoices', 'group' => 'invoices'],
            
            // =====================================================
            // USER PERMISSIONS (4)
            // =====================================================
            ['name' => 'View Users', 'slug' => 'view-users', 'group' => 'users'],
            ['name' => 'Create Users', 'slug' => 'create-users', 'group' => 'users'],
            ['name' => 'Edit Users', 'slug' => 'edit-users', 'group' => 'users'],
            ['name' => 'Delete Users', 'slug' => 'delete-users', 'group' => 'users'],
            
            // =====================================================
            // ROLE PERMISSIONS (4)
            // =====================================================
            ['name' => 'View Roles', 'slug' => 'view-roles', 'group' => 'roles'],
            ['name' => 'Create Roles', 'slug' => 'create-roles', 'group' => 'roles'],
            ['name' => 'Edit Roles', 'slug' => 'edit-roles', 'group' => 'roles'],
            ['name' => 'Delete Roles', 'slug' => 'delete-roles', 'group' => 'roles'],
            
            // =====================================================
            // CATEGORY PERMISSIONS (4)
            // =====================================================
            ['name' => 'View Categories', 'slug' => 'view-categories', 'group' => 'categories'],
            ['name' => 'Create Categories', 'slug' => 'create-categories', 'group' => 'categories'],
            ['name' => 'Edit Categories', 'slug' => 'edit-categories', 'group' => 'categories'],
            ['name' => 'Delete Categories', 'slug' => 'delete-categories', 'group' => 'categories'],
            
            // =====================================================
            // ORGANISATION PERMISSIONS (4)
            // =====================================================
            ['name' => 'View Organisations', 'slug' => 'view-organisations', 'group' => 'organisations'],
            ['name' => 'Create Organisations', 'slug' => 'create-organisations', 'group' => 'organisations'],
            ['name' => 'Edit Organisations', 'slug' => 'edit-organisations', 'group' => 'organisations'],
            ['name' => 'Delete Organisations', 'slug' => 'delete-organisations', 'group' => 'organisations'],
            
            // =====================================================
            // MANAGER TAGS PERMISSIONS (4)
            // =====================================================
            ['name' => 'View Manager Tags', 'slug' => 'view-manager-tags', 'group' => 'manager-tags'],
            ['name' => 'Create Manager Tags', 'slug' => 'create-manager-tags', 'group' => 'manager-tags'],
            ['name' => 'Edit Manager Tags', 'slug' => 'edit-manager-tags', 'group' => 'manager-tags'],
            ['name' => 'Delete Manager Tags', 'slug' => 'delete-manager-tags', 'group' => 'manager-tags'],
            
            // =====================================================
            // TEMPLATE PERMISSIONS (1)
            // =====================================================
            ['name' => 'Manage Templates', 'slug' => 'manage-templates', 'group' => 'templates'],
            
            // =====================================================
            // ZOHO PERMISSIONS (2)
            // =====================================================
            ['name' => 'Manage Zoho', 'slug' => 'manage-zoho', 'group' => 'zoho'],
            ['name' => 'View Zoho Data', 'slug' => 'view-zoho-data', 'group' => 'zoho'],
            
            // =====================================================
            // DASHBOARD PERMISSIONS (2)
            // =====================================================
            ['name' => 'View Dashboard', 'slug' => 'view-dashboard', 'group' => 'dashboard'],
            ['name' => 'Sync Dashboard', 'slug' => 'sync-dashboard', 'group' => 'dashboard'],
            
            // =====================================================
            // SETTINGS PERMISSIONS (1)
            // =====================================================
            ['name' => 'Manage Settings', 'slug' => 'manage-settings', 'group' => 'settings'],
        ];
        
        echo "Creating 45 permissions...\n";
        
        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['slug' => $perm['slug']],
                $perm
            );
        }
        
        echo "✅ 45 Permissions created!\n\n";
        
        // =====================================================
        // CREATE 6 ROLES
        // =====================================================
        
        $roles = [
            [
                'name' => 'Super Admin', 
                'slug' => 'super-admin', 
                'description' => 'Full system access - All permissions'
            ],
            [
                'name' => 'Manager', 
                'slug' => 'manager', 
                'description' => 'Relation Manager (RM) - First level invoice approver'
            ],
            [
                'name' => 'VP', 
                'slug' => 'vp', 
                'description' => 'Vice President - Second level invoice approver'
            ],
            [
                'name' => 'CEO', 
                'slug' => 'ceo', 
                'description' => 'CEO - Approves when invoice exceeds contract value'
            ],
            [
                'name' => 'Finance', 
                'slug' => 'finance', 
                'description' => 'Finance Team - Final approver and payment processing'
            ],
            [
                'name' => 'Viewer', 
                'slug' => 'viewer', 
                'description' => 'Read only access - Can only view data'
            ],
        ];
        
        echo "Creating 6 roles...\n";
        
        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
        
        echo "✅ 6 Roles created!\n\n";
        
        // =====================================================
        // ASSIGN PERMISSIONS TO ROLES
        // =====================================================
        
        echo "Assigning permissions to roles...\n";
        
        // -------------------------------------------------
        // SUPER ADMIN - ALL 45 PERMISSIONS
        // -------------------------------------------------
        $superAdmin = Role::where('slug', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->permissions()->sync(Permission::pluck('id'));
            echo "✅ Super Admin: ALL permissions assigned\n";
        }
        
        // -------------------------------------------------
        // MANAGER (RM) - View + Invoice Approval
        // -------------------------------------------------
        $manager = Role::where('slug', 'manager')->first();
        if ($manager) {
            $managerPerms = Permission::whereIn('slug', [
                // View
                'view-dashboard',
                'view-vendors',
                'view-contracts',
                'view-invoices',
                'view-categories',
                'view-zoho-data',
                // Invoice actions
                'review-invoices',
                'approve-invoices',
                'reject-invoices',
            ])->pluck('id');
            $manager->permissions()->sync($managerPerms);
            echo "✅ Manager: " . count($managerPerms) . " permissions assigned\n";
        }
        
        // -------------------------------------------------
        // VP - View + Invoice Approval
        // -------------------------------------------------
        $vp = Role::where('slug', 'vp')->first();
        if ($vp) {
            $vpPerms = Permission::whereIn('slug', [
                // View
                'view-dashboard',
                'view-vendors',
                'view-contracts',
                'view-invoices',
                'view-categories',
                'view-zoho-data',
                // Invoice actions
                'review-invoices',
                'approve-invoices',
                'reject-invoices',
            ])->pluck('id');
            $vp->permissions()->sync($vpPerms);
            echo "✅ VP: " . count($vpPerms) . " permissions assigned\n";
        }
        
        // -------------------------------------------------
        // CEO - View + Invoice Approval (only exceeds)
        // -------------------------------------------------
        $ceo = Role::where('slug', 'ceo')->first();
        if ($ceo) {
            $ceoPerms = Permission::whereIn('slug', [
                // View
                'view-dashboard',
                'view-vendors',
                'view-contracts',
                'view-invoices',
                'view-categories',
                'view-zoho-data',
                // Invoice actions
                'approve-invoices',
                'reject-invoices',
            ])->pluck('id');
            $ceo->permissions()->sync($ceoPerms);
            echo "✅ CEO: " . count($ceoPerms) . " permissions assigned\n";
        }
        
        // -------------------------------------------------
        // FINANCE - View + Invoice Approval + Payment + Zoho
        // -------------------------------------------------
        $finance = Role::where('slug', 'finance')->first();
        if ($finance) {
            $financePerms = Permission::whereIn('slug', [
                // View
                'view-dashboard',
                'view-vendors',
                'view-contracts',
                'view-invoices',
                'view-categories',
                'view-zoho-data',
                // Invoice actions
                'approve-invoices',
                'reject-invoices',
                'pay-invoices',
                'sync-invoices',
                // Zoho
                'manage-zoho',
            ])->pluck('id');
            $finance->permissions()->sync($financePerms);
            echo "✅ Finance: " . count($financePerms) . " permissions assigned\n";
        }
        
        // -------------------------------------------------
        // VIEWER - Only View permissions
        // -------------------------------------------------
        $viewer = Role::where('slug', 'viewer')->first();
        if ($viewer) {
            $viewerPerms = Permission::whereIn('slug', [
                'view-dashboard',
                'view-vendors',
                'view-contracts',
                'view-invoices',
                'view-categories',
            ])->pluck('id');
            $viewer->permissions()->sync($viewerPerms);
            echo "✅ Viewer: " . count($viewerPerms) . " permissions assigned\n";
        }
        
        echo "\n========================================\n";
        echo "✅ SEEDING COMPLETE!\n";
        echo "========================================\n";
        echo "Total Permissions: 45\n";
        echo "Total Roles: 6\n";
        echo "========================================\n";
    }
}