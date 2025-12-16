<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    // =====================================================
    // LIST ALL CATEGORIES
    // =====================================================

    public function index(Request $request)
    {
        try {
            $query = Category::query();

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('hsn_sac_code', 'like', "%{$search}%");
                });
            }

            // Order
            $query->orderBy('name', 'asc');

            // Paginate or get all
            if ($request->has('per_page')) {
                $categories = $query->paginate($request->per_page);
            } else {
                $categories = $query->get();
            }

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // GET ACTIVE CATEGORIES (For Dropdowns)
    // =====================================================

    public function getActive()
    {
        try {
            $categories = Category::active()
                ->orderBy('name', 'asc')
                ->get(['id', 'name', 'code', 'hsn_sac_code']);

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load categories'
            ], 500);
        }
    }

    // =====================================================
    // GET SINGLE CATEGORY
    // =====================================================

    public function show($id)
    {
        try {
            $category = Category::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $category
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
    }

    // =====================================================
    // CREATE CATEGORY
    // =====================================================

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:categories,name',
                'code' => 'nullable|string|max:50|unique:categories,code',
                'description' => 'nullable|string|max:500',
                'hsn_sac_code' => 'nullable|string|max:20',
                'status' => 'nullable|in:active,inactive',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $category = Category::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'hsn_sac_code' => $request->hsn_sac_code,
                'status' => $request->status ?? 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // UPDATE CATEGORY
    // =====================================================

    public function update(Request $request, $id)
    {
        try {
            $category = Category::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:categories,name,' . $id,
                'code' => 'nullable|string|max:50|unique:categories,code,' . $id,
                'description' => 'nullable|string|max:500',
                'hsn_sac_code' => 'nullable|string|max:20',
                'status' => 'nullable|in:active,inactive',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $category->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'hsn_sac_code' => $request->hsn_sac_code,
                'status' => $request->status ?? $category->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // DELETE CATEGORY
    // =====================================================

    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);

            // Check if category is used in contract items
            if ($category->contractItems()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category. It is used in contracts.'
                ], 400);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // TOGGLE STATUS
    // =====================================================

    public function toggleStatus($id)
    {
        try {
            $category = Category::findOrFail($id);

            $category->status = $category->status === 'active' ? 'inactive' : 'active';
            $category->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $category
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    // =====================================================
    // GET STATISTICS
    // =====================================================

    public function getStatistics()
    {
        try {
            $stats = [
                'total' => Category::count(),
                'active' => Category::active()->count(),
                'inactive' => Category::inactive()->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics'
            ], 500);
        }
    }
}