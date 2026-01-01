<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TravelEmployee;
use App\Models\ManagerTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TravelEmployeeController extends Controller
{
    // =====================================================
    // LIST ALL EMPLOYEES
    // =====================================================

    public function index(Request $request)
    {
        try {
            $query = TravelEmployee::query();

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            // Filter by project/tag
            if ($request->has('tag_id') && $request->tag_id) {
                $query->where('tag_id', $request->tag_id);
            }

            // Search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('employee_name', 'like', "%{$search}%")
                      ->orWhere('employee_code', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('tag_name', 'like', "%{$search}%");
                });
            }

            $employees = $query->orderBy('employee_name', 'asc')
                ->paginate($request->get('per_page', 15));

            // Add manager info to each employee
            $employees->getCollection()->transform(function ($employee) {
                $employee->manager_name = $employee->manager_name;
                $employee->manager_id = $employee->getManagerId();
                return $employee;
            });

            return response()->json([
                'success' => true,
                'data' => $employees
            ]);

        } catch (\Exception $e) {
            Log::error('Get Travel Employees Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // GET EMPLOYEE DETAILS
    // =====================================================

    public function show($id)
    {
        try {
            $employee = TravelEmployee::findOrFail($id);
            $employee->manager_name = $employee->manager_name;
            $employee->manager_id = $employee->getManagerId();
            $employee->total_invoices = $employee->invoices()->count();
            $employee->total_amount = $employee->getTotalInvoiceAmount();

            return response()->json([
                'success' => true,
                'data' => $employee
            ]);

        } catch (\Exception $e) {
            Log::error('Get Travel Employee Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Employee not found.'
            ], 404);
        }
    }

    // =====================================================
    // CREATE EMPLOYEE
    // =====================================================

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'employee_name' => 'required|string|max:255',
                'employee_code' => 'nullable|string|max:50',
                'designation' => 'nullable|string|max:100',
                'department' => 'nullable|string|max:100',
                'tag_id' => 'required|string',
                'tag_name' => 'required|string',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if employee with same name exists in same project
            $exists = TravelEmployee::where('employee_name', $request->employee_name)
                ->where('tag_id', $request->tag_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee with this name already exists in this project.'
                ], 422);
            }

            $employee = TravelEmployee::create([
                'employee_name' => $request->employee_name,
                'employee_code' => $request->employee_code,
                'designation' => $request->designation,
                'department' => $request->department,
                'tag_id' => $request->tag_id,
                'tag_name' => $request->tag_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_active' => true,
            ]);

            Log::info('Travel Employee created', [
                'employee_id' => $employee->id,
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully.',
                'data' => $employee
            ]);

        } catch (\Exception $e) {
            Log::error('Create Travel Employee Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // UPDATE EMPLOYEE
    // =====================================================

    public function update(Request $request, $id)
    {
        try {
            $employee = TravelEmployee::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'employee_name' => 'required|string|max:255',
                'employee_code' => 'nullable|string|max:50',
                'designation' => 'nullable|string|max:100',
                'department' => 'nullable|string|max:100',
                'tag_id' => 'required|string',
                'tag_name' => 'required|string',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if employee with same name exists in same project (excluding current)
            $exists = TravelEmployee::where('employee_name', $request->employee_name)
                ->where('tag_id', $request->tag_id)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee with this name already exists in this project.'
                ], 422);
            }

            $employee->update([
                'employee_name' => $request->employee_name,
                'employee_code' => $request->employee_code,
                'designation' => $request->designation,
                'department' => $request->department,
                'tag_id' => $request->tag_id,
                'tag_name' => $request->tag_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_active' => $request->has('is_active') ? $request->is_active : $employee->is_active,
            ]);

            Log::info('Travel Employee updated', [
                'employee_id' => $employee->id,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully.',
                'data' => $employee->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Update Travel Employee Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // DELETE EMPLOYEE
    // =====================================================

    public function destroy($id)
    {
        try {
            $employee = TravelEmployee::findOrFail($id);

            // Check if employee has invoices
            if ($employee->hasInvoices()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete employee with existing invoices. Deactivate instead.'
                ], 400);
            }

            $employee->delete();

            Log::info('Travel Employee deleted', [
                'employee_id' => $id,
                'deleted_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete Travel Employee Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // TOGGLE ACTIVE STATUS
    // =====================================================

    public function toggleStatus($id)
    {
        try {
            $employee = TravelEmployee::findOrFail($id);
            $employee->is_active = !$employee->is_active;
            $employee->save();

            $status = $employee->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Employee {$status} successfully.",
                'data' => $employee
            ]);

        } catch (\Exception $e) {
            Log::error('Toggle Travel Employee Status Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // GET EMPLOYEES DROPDOWN (for vendor portal)
    // =====================================================

    public function dropdown(Request $request)
    {
        try {
            $query = TravelEmployee::where('is_active', true);

            // Filter by project if provided
            if ($request->has('tag_id') && $request->tag_id) {
                $query->where('tag_id', $request->tag_id);
            }

            $employees = $query->select('id', 'employee_name', 'employee_code', 'tag_id', 'tag_name')
                ->orderBy('employee_name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $employees
            ]);

        } catch (\Exception $e) {
            Log::error('Get Travel Employees Dropdown Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // GET PROJECTS/TAGS DROPDOWN
    // =====================================================

    public function getProjects()
    {
        try {
            $tags = ManagerTag::select('tag_id', 'tag_name', 'user_id')
                ->with('user:id,name')
                ->get()
                ->map(function ($tag) {
                    return [
                        'tag_id' => $tag->tag_id,
                        'tag_name' => $tag->tag_name,
                        'manager_id' => $tag->user_id,
                        'manager_name' => $tag->user ? $tag->user->name : 'Unassigned',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $tags
            ]);

        } catch (\Exception $e) {
            Log::error('Get Projects Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
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
                'total' => TravelEmployee::count(),
                'active' => TravelEmployee::where('is_active', true)->count(),
                'inactive' => TravelEmployee::where('is_active', false)->count(),
                'by_project' => TravelEmployee::where('is_active', true)
                    ->selectRaw('tag_id, tag_name, count(*) as count')
                    ->groupBy('tag_id', 'tag_name')
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Get Travel Employee Statistics Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }
}