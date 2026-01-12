<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorContractController extends Controller
{
    // =====================================================
    // GET VENDOR'S CONTRACTS
    // =====================================================

    public function index(Request $request)
    {
        try {
            $vendorId = Auth::guard('vendor')->id();

$query = Contract::with(['items.category'])
    ->where('vendor_id', $vendorId)
    ->where('is_visible_to_vendor', true)
    ->where(function($q) {
        // Normal contracts: must be signed
        $q->where(function($q2) {
            $q2->where('contract_type', '!=', 'adhoc')
               ->where('is_signed', true);
        })
        // ADHOC contracts: always visible (no signing needed)
        ->orWhere('contract_type', 'adhoc');
    });

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where('contract_number', 'like', "%{$search}%");
            }

            // Order
            $query->orderBy('created_at', 'desc');

            // Paginate
            $perPage = $request->get('per_page', 10);
            $contracts = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $contracts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load contracts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // GET SINGLE CONTRACT
    // =====================================================

    public function show($id)
    {
        try {
            $vendorId = Auth::guard('vendor')->id();

            $contract = Contract::with(['items.category'])
                ->where('vendor_id', $vendorId)
                ->where('is_visible_to_vendor', true)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $contract
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Contract not found'
            ], 404);
        }
    }

    // =====================================================
    // GET CONTRACT ITEMS (for invoice dropdown)
    // =====================================================
public function getContractItems($id)
{
    try {
        $vendorId = Auth::guard('vendor')->id();

        $contract = Contract::where('vendor_id', $vendorId)
            ->where('is_visible_to_vendor', true)
            ->findOrFail($id);

      $items = ContractItem::with(['category'])
    ->where('contract_id', $id)
    ->get()
    ->map(function ($item) {
        return [
            'id' => $item->id,
            'category_id' => $item->category_id,
            'category_name' => $item->category->name ?? '',
            'quantity' => $item->quantity,      // ADD THIS
            'rate' => $item->rate,              // ADD THIS
            'tag_id' => $item->tag_id,
            'tag_name' => $item->tag_name,
        ];
    });

        return response()->json([
            'success' => true,
            'data' => $items
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Contract not found'
        ], 404);
    }
}

    // =====================================================
    // GET STATISTICS
    // =====================================================

    public function getStatistics()
    {
        try {
            $vendorId = Auth::guard('vendor')->id();

            $baseQuery = Contract::where('vendor_id', $vendorId)
                ->where('is_visible_to_vendor', true);

            $stats = [
                'total' => (clone $baseQuery)->count(),
                'active' => (clone $baseQuery)->where('status', 'active')->count(),
                'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
                'expired' => (clone $baseQuery)->where('status', 'expired')->count(),
                'total_value' => (clone $baseQuery)->sum('contract_value'),
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

    // =====================================================
    // GET CONTRACTS FOR DROPDOWN (Invoice form)
    // =====================================================
public function getContractsDropdown()
{
    try {
        $vendorId = Auth::guard('vendor')->id();

        $contracts = Contract::where('vendor_id', $vendorId)
            ->where('is_visible_to_vendor', true)
            ->where(function($q) {
                // Normal contracts: must be signed + status signed/active
                $q->where(function($q2) {
                    $q2->where('is_signed', true)
                       ->whereIn('status', ['signed', 'active']);
                })
                // ADHOC contracts: any active status (no signing needed)
                ->orWhere(function($q2) {
                    $q2->where('contract_type', 'adhoc')
                       ->whereIn('status', ['draft', 'active', 'signed']);
                });
            })
            ->orderBy('contract_number', 'desc')
            ->get(['id', 'contract_number', 'contract_type', 'contract_value', 'sow_value']);

        return response()->json([
            'success' => true,
            'data' => $contracts
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to load contracts'
        ], 500);
    }
}}