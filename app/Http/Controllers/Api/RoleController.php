<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    // Get all roles WITH PERMISSIONS
    public function index()
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($roles);
    }

    // Get single role with permissions
    public function show($id)
    {
        $role = Role::with('permissions')->withCount('users')->findOrFail($id);
        return response()->json($role);
    }

    // Get all permissions (grouped)
    public function getPermissions()
    {
        try {
            $permissions = Permission::all();
            
            // Group by 'group' column if exists
            if ($permissions->isNotEmpty()) {
                $grouped = $permissions->groupBy('group');
                return response()->json($grouped);
            }
            
            return response()->json([]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Store new role
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'is_default' => false,
        ]);

        $role->permissions()->sync($request->permissions);
        $role->load('permissions');
        $role->loadCount('users');

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully!',
            'role' => $role
        ], 201);
    }

    // Update role
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->name = $request->name;
        $role->slug = Str::slug($request->name);
        $role->description = $request->description;
        $role->save();

        $role->permissions()->sync($request->permissions);
        $role->load('permissions');
        $role->loadCount('users');

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully!',
            'role' => $role
        ]);
    }

    // Delete role
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Prevent deleting default roles
        if ($role->is_default) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete default system roles!'
            ], 403);
        }

        // Check if users are using this role
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete role that is assigned to users!'
            ], 403);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully!'
        ]);
    }
}