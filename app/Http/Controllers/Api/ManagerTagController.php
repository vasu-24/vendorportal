<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ManagerTag;
use App\Models\User;
use App\Services\ZohoService;
use Illuminate\Http\Request;

class ManagerTagController extends Controller
{
    // List all manager-tag assignments
    public function index()
    {
        $managers = User::whereHas('role', function($q) {
                $q->where('slug', 'manager'); // Only managers
            })
            ->with('managerTags')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'tags' => $user->managerTags->map(fn($t) => [
                        'id' => $t->id,
                        'tag_id' => $t->tag_id,
                        'tag_name' => $t->tag_name,
                    ]),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $managers
        ]);
    }

    // Get managers dropdown (for select)
    public function managersDropdown()
    {
        $managers = User::whereHas('role', function($q) {
                $q->where('slug', 'manager');
            })
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $managers
        ]);
    }

    // Get Zoho tags dropdown
    public function tagsDropdown()
    {
        try {
            $zohoService = new ZohoService();
            $tags = $zohoService->getReportingTags();

            return response()->json([
                'success' => true,
                'data' => $tags
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tags'
            ], 500);
        }
    }

    // Save manager tags (Create/Update)
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tags' => 'required|array',
            'tags.*.tag_id' => 'required|string',
            'tags.*.tag_name' => 'required|string',
        ]);

        $userId = $request->user_id;

        // Delete existing tags for this manager
        ManagerTag::where('user_id', $userId)->delete();

        // Insert new tags
        foreach ($request->tags as $tag) {
            ManagerTag::create([
                'user_id' => $userId,
                'tag_id' => $tag['tag_id'],
                'tag_name' => $tag['tag_name'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Manager tags saved successfully'
        ]);
    }






    
/**
 * Get only tags that are assigned to managers
 */
public function assignedTagsDropdown()
{
    try {
        $tags = \App\Models\ManagerTag::select('tag_id', 'tag_name', 'user_id')
            ->with('user:id,name')
            ->get()
            ->unique('tag_id')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $tags
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to load tags'
        ], 500);
    }
}



    // Delete all tags for a manager
    public function destroy($userId)
    {
        ManagerTag::where('user_id', $userId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Manager tags removed successfully'
        ]);
    }
}