<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceAttachment;
use App\Models\Vendor;
use App\Services\ZohoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// use App\Mail\InvoiceApprovedMail;
// use App\Mail\InvoiceRejectedMail;
// use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    // =====================================================
    // GET STATISTICS
    // =====================================================

    /**
     * Get invoice statistics for dashboard
     */








// =====================================================
// UPDATE INVOICE (Finance can edit full invoice)
// =====================================================

/**
 * Finance can edit the full invoice
 */
public function updateInvoice(Request $request, $id)
{
    try {
        $user = Auth::user();
        $userRole = $user->role->slug ?? 'viewer';
        
        // Only Finance or Super Admin can edit
        if (!in_array($userRole, ['finance', 'super-admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only Finance team can edit invoices.'
            ], 403);
        }

        $invoice = Invoice::with('items')->findOrFail($id);

        // Can only edit when status is pending_finance
        if (!in_array($invoice->status, ['pending_finance']) && $userRole !== 'super-admin') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice can only be edited at Finance stage.'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'invoice_number' => 'sometimes|string|max:50',
            'invoice_date' => 'sometimes|date',
            'due_date' => 'sometimes|nullable|date',
            'description' => 'sometimes|nullable|string|max:1000',
            'base_total' => 'sometimes|numeric|min:0',
            'gst_percent' => 'sometimes|numeric|min:0|max:100',
            'gst_total' => 'sometimes|numeric|min:0',
            'tds_percent' => 'sometimes|numeric|min:0|max:100',
            'tds_amount' => 'sometimes|numeric|min:0',
            'grand_total' => 'sometimes|numeric|min:0',
            'net_payable' => 'sometimes|numeric|min:0',
            'remarks' => 'sometimes|nullable|string|max:1000',
            'items' => 'sometimes|array',
            'items.*.id' => 'sometimes|exists:invoice_items,id',
            'items.*.particulars' => 'sometimes|string|max:255',
            'items.*.quantity' => 'sometimes|numeric|min:0',
            'items.*.rate' => 'sometimes|numeric|min:0',
            'items.*.amount' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update invoice fields
        $invoiceData = $request->only([
            'invoice_number',
            'invoice_date',
            'due_date',
            'description',
            'base_total',
            'gst_percent',
            'gst_total',
            'tds_percent',
            'tds_amount',
            'grand_total',
            'net_payable',
            'remarks',
            'zoho_gst_tax_id',
            'zoho_tds_tax_id',
        ]);

        if (!empty($invoiceData)) {
            $invoice->update($invoiceData);
        }

        // Update line items if provided
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $itemData) {
                if (isset($itemData['id'])) {
                    $item = \App\Models\InvoiceItem::find($itemData['id']);
                    if ($item && $item->invoice_id === $invoice->id) {
                        $item->update([
                            'particulars' => $itemData['particulars'] ?? $item->particulars,
                            'quantity' => $itemData['quantity'] ?? $item->quantity,
                            'rate' => $itemData['rate'] ?? $item->rate,
                            'amount' => $itemData['amount'] ?? $item->amount,
                        ]);
                    }
                }
            }
        }

        Log::info('Invoice updated by Finance', [
            'invoice_id' => $invoice->id,
            'updated_by' => $user->id,
            'updated_fields' => array_keys($invoiceData),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully.',
            'data' => $invoice->fresh()->load(['items', 'vendor', 'contract']),
        ]);

    } catch (\Exception $e) {
        Log::error('Update Invoice Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.'
        ], 500);
    }
}











  public function getStatistics()
{
    try {
        $stats = [
            'total' => Invoice::count(),
            'submitted' => Invoice::where('status', 'submitted')->count(),
            'under_review' => Invoice::where('status', 'under_review')->count(),
            'approved' => Invoice::where('status', 'approved')->count(),
            'rejected' => Invoice::where('status', 'rejected')->count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'draft' => Invoice::where('status', 'draft')->count(),
            'resubmitted' => Invoice::where('status', 'resubmitted')->count(),
            
            'pending_rm' => Invoice::where('status', 'pending_rm')->count(),
            'pending_vp' => Invoice::where('status', 'pending_vp')->count(),
            'pending_ceo' => Invoice::where('status', 'pending_ceo')->count(),
            'pending_finance' => Invoice::where('status', 'pending_finance')->count(),
            
            'total_amount_pending' => Invoice::whereIn('status', ['submitted', 'under_review', 'resubmitted', 'pending_rm', 'pending_vp', 'pending_ceo', 'pending_finance'])->sum('grand_total'),
            'total_amount_approved' => Invoice::where('status', 'approved')->sum('grand_total'),
            'total_amount_paid' => Invoice::where('status', 'paid')->sum('grand_total'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);

    } catch (\Exception $e) {
        Log::error('Get Invoice Statistics Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.'
        ], 500);
    }
}

    // =====================================================
    // LIST ALL INVOICES
    // =====================================================

    /**
     * Get all invoices with filters
     */
public function index(Request $request)
{
    try {
        $user = Auth::user();
        $userId = $user->id;
        $userRole = $user->role->slug ?? 'viewer';
        
        $query = Invoice::with([
            'vendor', 
            'vendor.companyInfo', 
            'attachments', 
            'contract', 
            'items', 
            'items.contractItem',
            'items.contractItem.category'
        ]);

        // =====================================================
        // ROLE-BASED FILTERING
        // =====================================================
        
        if ($userRole === 'super-admin') {
            // Super Admin sees ALL invoices - NO RESTRICTIONS
            // No filter needed
            
        } elseif ($userRole === 'manager') {
    // RM sees invoices that have items with their tag
    $userTagIds = \App\Models\ManagerTag::where('user_id', $userId)->pluck('tag_id')->toArray();
    
    $query->where(function($q) use ($userId, $userTagIds) {
        // Invoices assigned to this RM
        $q->where('assigned_rm_id', $userId)
          // OR invoices with items having RM's tag
          ->orWhereHas('items', function($q2) use ($userTagIds) {
              $q2->whereIn('tag_id', $userTagIds);
          })
          // OR unassigned submitted/resubmitted with matching tag
          ->orWhere(function($q2) use ($userTagIds) {
              $q2->whereIn('status', ['submitted', 'resubmitted'])
                 ->where(function($q3) use ($userTagIds) {
                     $q3->whereIn('assigned_tag_id', $userTagIds)
                        ->orWhereNull('assigned_rm_id');
                 });
          });
    });
            
        } elseif ($userRole === 'vp') {
            // VOO sees:
            // 1. pending_vp (need to approve)
            // 2. pending_ceo, pending_finance, approved, paid (already passed VOO)
            // 3. rejected at VOO level or AFTER (VOO, CEO, Finance, Super Admin)
            $query->where(function($q) {
                $q->where('status', 'pending_vp')
                  ->orWhereIn('status', ['pending_ceo', 'pending_finance', 'approved', 'paid'])
                  ->orWhere(function($q2) {
                      $q2->where('status', 'rejected')
                         ->whereIn('rejected_by_role', ['VOO', 'CEO', 'Finance', 'Super Admin']);
                  });
            });
            
        } elseif ($userRole === 'ceo') {
            // CEO sees:
            // 1. pending_ceo (need to approve)
            // 2. pending_finance, approved, paid (already passed CEO)
            // 3. rejected at CEO level or AFTER (CEO, Finance, Super Admin)
            $query->where(function($q) {
                $q->where('status', 'pending_ceo')
                  ->orWhereIn('status', ['pending_finance', 'approved', 'paid'])
                  ->orWhere(function($q2) {
                      $q2->where('status', 'rejected')
                         ->whereIn('rejected_by_role', ['CEO', 'Finance', 'Super Admin']);
                  });
            });
            
        } elseif ($userRole === 'finance') {
            // Finance sees:
            // 1. pending_finance (need to approve)
            // 2. approved, paid (history)
            // 3. rejected at Finance level or AFTER (Finance, Super Admin)
            $query->where(function($q) {
                $q->whereIn('status', ['pending_finance', 'approved', 'paid'])
                  ->orWhere(function($q2) {
                      $q2->where('status', 'rejected')
                         ->whereIn('rejected_by_role', ['Finance', 'Super Admin']);
                  });
            });
            
        } elseif ($userRole === 'viewer') {
            // Viewer sees only approved + paid
            $query->whereIn('status', ['approved', 'paid']);
            
        } else {
            // Unknown role - see nothing
            $query->whereRaw('1 = 0');
        }

        // =====================================================
        // ADDITIONAL FILTERS (from request)
        // =====================================================

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'pending') {
                $query->whereIn('status', ['submitted', 'under_review', 'resubmitted', 'pending_rm', 'pending_vp', 'pending_ceo', 'pending_finance']);
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('invoice_type', $request->type);
        }

        // Filter by vendor
        if ($request->has('vendor_id') && $request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function ($q2) use ($search) {
                      $q2->where('vendor_name', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $invoices,
            'user_role' => $userRole
        ]);

    } catch (\Exception $e) {
        Log::error('Get Invoices Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.'
        ], 500);
    }
}

    // =====================================================
    // GET PENDING INVOICES
    // =====================================================

    /**
     * Get pending invoices for approval
     */
    public function getPending(Request $request)
    {
        try {
            $invoices = Invoice::with(['vendor', 'attachments'])
                ->whereIn('status', ['submitted', 'under_review', 'resubmitted'])
                ->orderBy('submitted_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $invoices,
                'count' => $invoices->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Get Pending Invoices Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // GET INVOICE BY STATUS
    // =====================================================

    /**
     * Get invoices by status
     */
    public function getByStatus($status)
    {
        try {
            $allowedStatuses = ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'resubmitted', 'paid', 'pending'];

            if (!in_array($status, $allowedStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status.'
                ], 400);
            }

            $query = Invoice::with(['vendor', 'attachments']);

            if ($status === 'pending') {
                $query->whereIn('status', ['submitted', 'under_review', 'resubmitted']);
            } else {
                $query->where('status', $status);
            }

            $invoices = $query->orderBy('updated_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $invoices,
                'count' => $invoices->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Get Invoices By Status Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // GET INVOICE DETAILS
    // =====================================================
/**
 * Get single invoice details
 */
public function show($id)
{
    try {
        $user = Auth::user();
        $userId = $user->id;
        $userRole = $user->role->slug ?? 'viewer';
        
        $invoice = Invoice::with([
            'vendor',
            'vendor.companyInfo',
            'vendor.contact',
            'vendor.statutoryInfo',
            'contract',
            'contract.items',
            'contract.items.category',
            'attachments',
            'items',
            'items.contractItem',
            'items.contractItem.category',
            'reviewedByUser',
            'approvedByUser',
            'rejectedByUser',
            'assignedRm'
        ])->findOrFail($id);

        // =====================================================
        // ACCESS CONTROL - Check if user can view this invoice
        // =====================================================
        
        $canView = false;
        $userTagIds = [];
        
        if ($userRole === 'super-admin') {
            // Super Admin can view ALL - NO RESTRICTIONS
            $canView = true;
            
        } elseif ($userRole === 'manager') {
            // RM can view if:
            // 1. Assigned to them
            // 2. Has matching tag at invoice level
            // 3. Has items with their tag
            $userTagIds = \App\Models\ManagerTag::where('user_id', $userId)->pluck('tag_id')->toArray();
            
            if ($invoice->assigned_rm_id === $userId) {
                $canView = true;
            } elseif (in_array($invoice->assigned_tag_id, $userTagIds)) {
                $canView = true;
            } else {
                // Check if any item has RM's tag
                $hasItemWithTag = $invoice->items->whereIn('tag_id', $userTagIds)->count() > 0;
                if ($hasItemWithTag) {
                    $canView = true;
                }
            }
            
        } elseif ($userRole === 'vp') {
            // VOO can view:
            // 1. pending_vp
            // 2. Later stages (pending_ceo, pending_finance, approved, paid)
            // 3. Rejected at VOO level or after
            if ($invoice->status === 'pending_vp') {
                $canView = true;
            } elseif (in_array($invoice->status, ['pending_ceo', 'pending_finance', 'approved', 'paid'])) {
                $canView = true;
            } elseif ($invoice->status === 'rejected' && in_array($invoice->rejected_by_role, ['VOO', 'CEO', 'Finance', 'Super Admin'])) {
                $canView = true;
            }
            
        } elseif ($userRole === 'ceo') {
            // CEO can view:
            // 1. pending_ceo
            // 2. Later stages (pending_finance, approved, paid)
            // 3. Rejected at CEO level or after
            if ($invoice->status === 'pending_ceo') {
                $canView = true;
            } elseif (in_array($invoice->status, ['pending_finance', 'approved', 'paid'])) {
                $canView = true;
            } elseif ($invoice->status === 'rejected' && in_array($invoice->rejected_by_role, ['CEO', 'Finance', 'Super Admin'])) {
                $canView = true;
            }
            
        } elseif ($userRole === 'finance') {
            // Finance can view:
            // 1. pending_finance, approved, paid
            // 2. Rejected at Finance level or after
            if (in_array($invoice->status, ['pending_finance', 'approved', 'paid'])) {
                $canView = true;
            } elseif ($invoice->status === 'rejected' && in_array($invoice->rejected_by_role, ['Finance', 'Super Admin'])) {
                $canView = true;
            }
            
        } elseif ($userRole === 'viewer') {
            // Viewer can view approved + paid only
            if (in_array($invoice->status, ['approved', 'paid'])) {
                $canView = true;
            }
        }
        
        if (!$canView) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this invoice.'
            ], 403);
        }

        // =====================================================
        // RM SPECIFIC DATA
        // =====================================================
        
        $rmItems = null;
        $rmPendingItems = 0;
        $rmApprovedItems = 0;
        $canApproveItems = false;
        
        if ($userRole === 'manager' && $invoice->status === 'pending_rm') {
            // Get items that belong to this RM
            $rmItems = $invoice->items->filter(function($item) use ($userTagIds) {
                return in_array($item->tag_id, $userTagIds);
            })->values();
            
            $rmPendingItems = $rmItems->where('rm_approved', false)->count();
            $rmApprovedItems = $rmItems->where('rm_approved', true)->count();
            $canApproveItems = $rmPendingItems > 0;
        }
        
        // Get all pending items count (for display)
        $totalPendingItems = $invoice->items->where('rm_approved', false)->count();
        $totalApprovedItems = $invoice->items->where('rm_approved', true)->count();

        return response()->json([
            'success' => true,
            'data' => $invoice,
            'user_role' => $userRole,
            'can_edit' => ($userRole === 'finance' && $invoice->status === 'pending_finance') || $userRole === 'super-admin',
            'can_approve_items' => $canApproveItems,
            'rm_items' => $rmItems,
            'rm_pending_items' => $rmPendingItems,
            'rm_approved_items' => $rmApprovedItems,
            'total_pending_items' => $totalPendingItems,
            'total_approved_items' => $totalApprovedItems,
        ]);

    } catch (\Exception $e) {
        Log::error('Get Invoice Details Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Invoice not found.'
        ], 404);
    }
}



// =====================================================
// CHANGE TAG (RM can reassign to another RM)
// =====================================================

/**
 * RM can change tag - Invoice moves to new RM
 */
public function changeTag(Request $request, $id)
{
    try {
        $validator = Validator::make($request->all(), [
            'tag_id' => 'required|string',
            'tag_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Tag is required.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $userId = $user->id;
        $userRole = $user->role->slug ?? 'viewer';
        
        $invoice = Invoice::findOrFail($id);

        // Only RM or Super Admin can change tag
        if (!in_array($userRole, ['manager', 'super-admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only RM can change tag.'
            ], 403);
        }

        // Can only change tag when status is pending_rm
        if (!in_array($invoice->status, ['submitted', 'resubmitted', 'pending_rm'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tag can only be changed before VP approval.'
            ], 400);
        }

        // Find new RM based on new tag
        $newTagId = $request->tag_id;
        $newTagName = $request->tag_name;
        $newRmId = null;
        
        $managerTag = \App\Models\ManagerTag::where('tag_id', $newTagId)->first();
        if ($managerTag) {
            $newRmId = $managerTag->user_id;
        }

     
      // Update invoice with new tag and new RM
$invoice->update([
    'assigned_tag_id' => $newTagId,
    'assigned_tag_name' => $newTagName,
    'assigned_rm_id' => $newRmId,
]);

// ALSO UPDATE ALL LINE ITEMS WITH NEW TAG
foreach ($invoice->items as $item) {
    $item->update([
        'tag_id' => $newTagId,
        'tag_name' => $newTagName,
    ]);
}

        // Get new RM name for message
        $newRmName = 'Unassigned';
        if ($newRmId) {
            $rmUser = \App\Models\User::find($newRmId);
            $newRmName = $rmUser ? $rmUser->name : 'RM';
        }

        Log::info('Invoice tag changed', [
            'invoice_id' => $invoice->id,
            'old_tag' => $invoice->getOriginal('assigned_tag_name'),
            'new_tag' => $newTagName,
            'new_rm_id' => $newRmId,
            'changed_by' => $userId,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Tag changed to {$newTagName}. Invoice assigned to {$newRmName}.",
            'data' => $invoice->fresh(),
            'new_rm' => $newRmName,
        ]);

    } catch (\Exception $e) {
        Log::error('Change Tag Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.'
        ], 500);
    }
}













  // =====================================================
// START REVIEW (Initiates Approval Flow)
// =====================================================

/**
 * Start review - moves to pending_rm
 */
// =====================================================
// START REVIEW (Initiates Approval Flow)
// =====================================================

/**
 * Start review - moves to pending_rm
 * Assigns tags to items and finds all RMs
 */
public function startReview($id)
{
    try {
        $invoice = Invoice::with(['contract', 'items.contractItem'])->findOrFail($id);

        if (!in_array($invoice->status, ['submitted', 'resubmitted'])) {
            return response()->json([
                'success' => false,
                'message' => 'This invoice cannot be reviewed.'
            ], 400);
        }

        $userId = Auth::id() ?? 1;
        
        // Check if exceeds contract
        $exceedsContract = $invoice->checkExceedsContract();
        
        // =====================================================
        // GET FIRST TAG (for items without tag)
        // =====================================================
        
        $firstTagId = null;
        $firstTagName = null;
        
        // Find first item with tag
        foreach ($invoice->items as $item) {
            if (!empty($item->tag_id)) {
                $firstTagId = $item->tag_id;
                $firstTagName = $item->tag_name;
                break;
            }
            // Also check from contract_item
            if ($item->contractItem && !empty($item->contractItem->tag_id)) {
                $firstTagId = $item->contractItem->tag_id;
                $firstTagName = $item->contractItem->tag_name;
                break;
            }
        }
        
        // =====================================================
        // ASSIGN TAGS TO ALL ITEMS
        // =====================================================
        
        foreach ($invoice->items as $item) {
            $itemTagId = $item->tag_id;
            $itemTagName = $item->tag_name;
            
            // If item doesn't have tag, get from contract_item
            if (empty($itemTagId) && $item->contractItem) {
                $itemTagId = $item->contractItem->tag_id;
                $itemTagName = $item->contractItem->tag_name;
            }
            
            // If still no tag, use first tag
            if (empty($itemTagId)) {
                $itemTagId = $firstTagId;
                $itemTagName = $firstTagName;
            }
            
            // Update item with tag
            $item->update([
                'tag_id' => $itemTagId,
                'tag_name' => $itemTagName,
                'rm_approved' => false,
                'rm_approved_by' => null,
                'rm_approved_at' => null,
            ]);
        }
        
        // =====================================================
        // FIND PRIMARY RM (first tag's manager)
        // =====================================================
        
        $assignedRmId = null;
        if ($firstTagId) {
            $managerTag = \App\Models\ManagerTag::where('tag_id', $firstTagId)->first();
            if ($managerTag) {
                $assignedRmId = $managerTag->user_id;
            }
        }

        $invoice->update([
            'status' => 'pending_rm',
            'current_approver_role' => 'rm',
            'exceeds_contract' => $exceedsContract,
            'assigned_rm_id' => $assignedRmId,
            'assigned_tag_id' => $firstTagId,
            'assigned_tag_name' => $firstTagName,
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
        ]);

        // Get unique RMs for this invoice
        $uniqueTagIds = $invoice->items()->distinct()->pluck('tag_id')->filter()->toArray();
        $rmNames = [];
        foreach ($uniqueTagIds as $tagId) {
            $managerTag = \App\Models\ManagerTag::where('tag_id', $tagId)->first();
            if ($managerTag && $managerTag->user) {
                $rmNames[] = $managerTag->user->name;
            }
        }
        $rmNamesStr = !empty($rmNames) ? implode(', ', array_unique($rmNames)) : 'RM';

        return response()->json([
            'success' => true,
            'message' => "Invoice sent to {$rmNamesStr} for approval.",
            'data' => $invoice->fresh(),
            'assigned_rms' => $rmNames,
        ]);

    } catch (\Exception $e) {
        Log::error('Start Review Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.'
        ], 500);
    }
}


  
/**
 * Approve invoice - Multi-level flow
 * Flow: RM → VP → CEO (if exceeds contract) → Finance → Approved
 */
public function approve(Request $request, $id)
{
    try {
        $invoice = Invoice::with('contract')->findOrFail($id);
        $user = Auth::user();
        $userId = $user->id ?? 1;
        $userRole = $user->role->slug ?? 'admin';
        
        // Get Zoho sync date (optional - defaults to invoice date)
        $zohoSyncDate = $request->input('zoho_sync_date', $invoice->invoice_date);
        
        // Check if invoice can be approved
        $allowedStatuses = ['submitted', 'resubmitted', 'pending_rm', 'pending_vp', 'pending_ceo', 'pending_finance'];
        if (!in_array($invoice->status, $allowedStatuses)) {
            return response()->json([
                'success' => false,
                'message' => 'This invoice cannot be approved.'
            ], 400);
        }
        
        // Check if invoice exceeds contract value
        $exceedsContract = $invoice->checkExceedsContract();
        
        // Determine current status and next status
        $currentStatus = $invoice->status;
        $nextStatus = null;
        $updateData = [];
        
        // =====================================================
        // APPROVAL FLOW LOGIC
        // =====================================================
        
        switch ($currentStatus) {
            // STEP 1: Submitted/Resubmitted → Pending RM
            case 'submitted':
            case 'resubmitted':
                // Move to RM approval
                $nextStatus = 'pending_rm';
                $updateData = [
                    'status' => $nextStatus,
                    'current_approver_role' => 'rm',
                    'exceeds_contract' => $exceedsContract,
                    'rejection_reason' => null,
                ];
                break;
                
// STEP 2: RM Approves → Check if all items approved → Pending VP
case 'pending_rm':
    // Check if user is RM or Admin
    if (!in_array($userRole, ['manager', 'super-admin'])) {
        return response()->json([
            'success' => false,
            'message' => 'Only RM can approve at this stage.'
        ], 403);
    }
    
    // Get RM's tag IDs
    $rmTagIds = \App\Models\ManagerTag::where('user_id', $userId)->pluck('tag_id')->toArray();
    
    // Approve only items with RM's tags
    $approvedCount = 0;
    foreach ($invoice->items as $item) {
        if (in_array($item->tag_id, $rmTagIds) && !$item->rm_approved) {
            $item->update([
                'rm_approved' => true,
                'rm_approved_by' => $userId,
                'rm_approved_at' => now(),
            ]);
            $approvedCount++;
        }
    }
    
    if ($approvedCount === 0) {
        return response()->json([
            'success' => false,
            'message' => 'No items to approve for your tag.'
        ], 400);
    }
    
    // Check if ALL items are approved
    $pendingItems = $invoice->items()->where('rm_approved', false)->count();
    
    if ($pendingItems > 0) {
        // Still waiting for other RMs
        $updateData = [
            'rm_approved_by' => $userId,
            'rm_approved_at' => now(),
        ];
        $invoice->update($updateData);
        
        return response()->json([
            'success' => true,
            'message' => "Your items approved. Waiting for other RMs to approve ({$pendingItems} items pending).",
            'data' => $invoice->fresh(),
            'pending_items' => $pendingItems,
            'all_approved' => false,
        ]);
    }
    
    // All items approved - move to VP
    $nextStatus = 'pending_vp';
    $updateData = [
        'status' => $nextStatus,
        'current_approver_role' => 'vp',
        'rm_approved_by' => $userId,
        'rm_approved_at' => now(),
    ];
    break;
    
    

            // STEP 3: VP Approves → Pending CEO (if exceeds) OR Pending Finance
            case 'pending_vp':
                // Check if user is VP or Admin
                if (!in_array($userRole, ['vp', 'super-admin', 'super-admin'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only VP can approve at this stage.'
                    ], 403);
                }
                
                // If exceeds contract, go to CEO, else go to Finance
                if ($invoice->exceeds_contract) {
                    $nextStatus = 'pending_ceo';
                    $updateData = [
                        'status' => $nextStatus,
                        'current_approver_role' => 'ceo',
                        'vp_approved_by' => $userId,
                        'vp_approved_at' => now(),
                    ];
                } else {
                    $nextStatus = 'pending_finance';
                    $updateData = [
                        'status' => $nextStatus,
                        'current_approver_role' => 'finance',
                        'vp_approved_by' => $userId,
                        'vp_approved_at' => now(),
                    ];
                }
                break;
                
            // STEP 4: CEO Approves → Pending Finance
            case 'pending_ceo':
                // Check if user is CEO or Admin
                if (!in_array($userRole, ['ceo', 'super-admin', 'super-admin'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only CEO can approve at this stage.'
                    ], 403);
                }
                
                $nextStatus = 'pending_finance';
                $updateData = [
                    'status' => $nextStatus,
                    'current_approver_role' => 'finance',
                    'ceo_approved_by' => $userId,
                    'ceo_approved_at' => now(),
                ];
                break;
                
            // STEP 5: Finance Approves → Approved (Final)
            case 'pending_finance':
                // Check if user is Finance or Admin
                if (!in_array($userRole, ['finance', 'super-admin', 'super-admin'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only Finance can approve at this stage.'
                    ], 403);
                }
                
                $nextStatus = 'approved';
                $updateData = [
                    'status' => $nextStatus,
                    'current_approver_role' => null,
                    'finance_approved_by' => $userId,
                    'finance_approved_at' => now(),
                    'approved_by' => $userId,
                    'approved_at' => now(),
                ];
                break;
                
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid invoice status for approval.'
                ], 400);
        }
        
        // Update invoice
        $invoice->update($updateData);
        
        // =====================================================
        // ZOHO SYNC (Only when finally approved)
        // =====================================================
        $zohoError = null;
        $zohoSynced = false;
        
        if ($nextStatus === 'approved') {
            try {
                $zohoService = app(ZohoService::class);
                
                if ($zohoService->isConnected()) {
                    
                    // Save zoho_sync_date
                    $invoice->zoho_sync_date = $zohoSyncDate;
                    $invoice->save();
                    $zohoService->createBill($invoice);
                    $zohoSynced = true;
                    
                    Log::info('Invoice pushed to Zoho', [
                        'invoice_id' => $invoice->id,
                        'zoho_bill_id' => $invoice->fresh()->zoho_invoice_id,
                    ]);
                } else {
                    Log::warning('Zoho not connected, skipping bill sync', [
                        'invoice_id' => $invoice->id,
                    ]);
                    $zohoError = 'Zoho not connected';
                }
            } catch (\Exception $e) {
                Log::error('Failed to push invoice to Zoho', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
                $zohoError = $e->getMessage();
            }
        }
        
        // Build response message
        $statusMessages = [
            'pending_rm' => 'Invoice sent to RM for approval.',
            'pending_vp' => 'RM approved. Invoice sent to VP for approval.',
            'pending_ceo' => 'VP approved. Invoice sent to CEO for approval (exceeds contract).',
            'pending_finance' => 'Invoice sent to Finance for final approval.',
            'approved' => 'Invoice approved successfully.',
        ];
        
        $message = $statusMessages[$nextStatus] ?? 'Invoice status updated.';
        
        if ($zohoError && $nextStatus === 'approved') {
            $message .= ' (Zoho sync failed: ' . $zohoError . ')';
        } elseif ($zohoSynced) {
            $message .= ' Bill created in Zoho.';
        }
        
        Log::info('Invoice approval flow', [
            'invoice_id' => $invoice->id,
            'from_status' => $currentStatus,
            'to_status' => $nextStatus,
            'approved_by' => $userId,
            'user_role' => $userRole,
            'exceeds_contract' => $exceedsContract,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $invoice->fresh(),
            'zoho_synced' => $zohoSynced,
        ]);

    } catch (\Exception $e) {
        Log::error('Approve Invoice Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.'
        ], 500);
    }
}


public function reject(Request $request, $id)
{
    try {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide rejection reason.',
                'errors' => $validator->errors()
            ], 422);
        }

        $invoice = Invoice::findOrFail($id);
        $user = Auth::user();
        $userId = $user->id ?? 1;
        $userRole = $user->role->slug ?? 'viewer';

        // =====================================================
        // CHECK IF USER CAN REJECT AT THIS STAGE
        // =====================================================
        
        $canReject = false;
        $currentStatus = $invoice->status;
        $rejectedByRole = null;

        if ($userRole === 'super-admin') {
            // Super Admin can reject at ANY stage - NO RESTRICTIONS
            $canReject = true;
            $rejectedByRole = 'Super Admin';
        } elseif ($userRole === 'manager' && $currentStatus === 'pending_rm') {
            $canReject = true;
            $rejectedByRole = 'RM';
        } elseif ($userRole === 'vp' && $currentStatus === 'pending_vp') {
            $canReject = true;
            $rejectedByRole = 'VOO';
        } elseif ($userRole === 'ceo' && $currentStatus === 'pending_ceo') {
            $canReject = true;
            $rejectedByRole = 'CEO';
        } elseif ($userRole === 'finance' && $currentStatus === 'pending_finance') {
            $canReject = true;
            $rejectedByRole = 'Finance';
        }

        if (!$canReject) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot reject this invoice at this stage.'
            ], 403);
        }

        // Check if invoice is in rejectable status
        $rejectableStatuses = ['submitted', 'resubmitted', 'pending_rm', 'pending_vp', 'pending_ceo', 'pending_finance'];
        
        if (!in_array($currentStatus, $rejectableStatuses)) {
            return response()->json([
                'success' => false,
                'message' => 'This invoice cannot be rejected.'
            ], 400);
        }

        // Update invoice - Goes back to VENDOR
        $invoice->update([
            'status' => 'rejected',
            'rejected_by' => $userId,
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'rejected_by_role' => $rejectedByRole,
            'current_approver_role' => null,
            // Clear previous approvals so flow starts fresh on resubmit
            'rm_approved_by' => null,
            'rm_approved_at' => null,
            'vp_approved_by' => null,
            'vp_approved_at' => null,
            'ceo_approved_by' => null,
            'ceo_approved_at' => null,
            'finance_approved_by' => null,
            'finance_approved_at' => null,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        // =====================================================
        // RESET ALL ITEM APPROVALS
        // =====================================================
        $invoice->items()->update([
            'rm_approved' => false,
            'rm_approved_by' => null,
            'rm_approved_at' => null,
        ]);

        Log::info('Invoice rejected', [
            'invoice_id' => $invoice->id,
            'rejected_by' => $userId,
            'user_role' => $userRole,
            'rejected_by_role' => $rejectedByRole,
            'previous_status' => $currentStatus,
            'reason' => $request->rejection_reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Invoice rejected by {$rejectedByRole}. Sent back to vendor.",
            'data' => $invoice->fresh()
        ]);

    } catch (\Exception $e) {
        Log::error('Reject Invoice Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.'
        ], 500);
    }
}
    // =====================================================
    // MARK AS PAID
    // =====================================================

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(Request $request, $id)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            if ($invoice->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only approved invoices can be marked as paid.'
                ], 400);
            }

            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice marked as paid.',
                'data' => $invoice->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Mark As Paid Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // ZOHO INTEGRATION METHODS
    // =====================================================

    /**
     * Manually push invoice to Zoho
     */
    public function pushToZoho($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            if ($invoice->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only approved invoices can be pushed to Zoho.'
                ], 400);
            }

            $zohoService = app(ZohoService::class);
            
            if (!$zohoService->isConnected()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zoho not connected.'
                ], 400);
            }

            if ($invoice->zoho_invoice_id) {
                // Update existing bill
                $result = $zohoService->updateBill($invoice);
                $message = 'Bill updated in Zoho.';
            } else {
                // Create new bill
                $result = $zohoService->createBill($invoice);
                $message = 'Bill created in Zoho.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $invoice->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Push To Zoho Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync single invoice status from Zoho
     */
    public function syncFromZoho($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            if (!$invoice->zoho_invoice_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not synced to Zoho.'
                ], 400);
            }

            $zohoService = app(ZohoService::class);
            
            if (!$zohoService->isConnected()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zoho not connected.'
                ], 400);
            }

            $synced = $zohoService->syncBillStatus($invoice);

            if ($synced) {
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice synced from Zoho.',
                    'data' => $invoice->fresh()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync from Zoho.'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Sync From Zoho Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }// =====================================================
// UPDATE TAX RATES (Admin Edit GST/TDS)
// =====================================================

/**
 * Update tax rates for an invoice
 */
public function updateTaxes(Request $request, $id)
{
    try {
        $validator = Validator::make($request->all(), [
            'gst_percent' => 'required|numeric|min:0|max:100',
            'gst_total' => 'required|numeric|min:0',
            'tds_percent' => 'required|numeric|min:0|max:100',
            'tds_amount' => 'required|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'net_payable' => 'required|numeric|min:0',
            'zoho_gst_tax_id' => 'nullable|string',
            'zoho_tds_tax_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $invoice = Invoice::findOrFail($id);

        // Update tax fields
        $invoice->update([
            'gst_percent' => $request->gst_percent,
            'gst_total' => $request->gst_total,
            'zoho_gst_tax_id' => $request->zoho_gst_tax_id,
            'tds_percent' => $request->tds_percent,
            'tds_amount' => $request->tds_amount,
            'zoho_tds_tax_id' => $request->zoho_tds_tax_id,
            'grand_total' => $request->grand_total,
            'net_payable' => $request->net_payable,
        ]);

        Log::info('Invoice tax rates updated', [
            'invoice_id' => $invoice->id,
            'gst_percent' => $request->gst_percent,
            'tds_percent' => $request->tds_percent,
            'updated_by' => Auth::id() ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tax rates updated successfully.',
            'data' => $invoice->fresh()
        ]);

    } catch (\Exception $e) {
        Log::error('Update Tax Rates Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.'
        ], 500);
    }
}





    /**
     * Sync all pending invoices from Zoho
     */
    public function syncAllFromZoho()
    {
        try {
            $zohoService = app(ZohoService::class);
            
            if (!$zohoService->isConnected()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zoho not connected.'
                ], 400);
            }

            $results = $zohoService->syncAllPendingBills();

            return response()->json([
                'success' => true,
                'message' => "Synced {$results['synced']} invoices. {$results['paid']} marked as paid.",
                'data' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Sync All From Zoho Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // DOWNLOAD ATTACHMENT
    // =====================================================

    /**
     * Download invoice attachment
     */
    public function downloadAttachment($invoiceId, $attachmentId)
    {
        try {
            $invoice = Invoice::findOrFail($invoiceId);
            $attachment = InvoiceAttachment::where('invoice_id', $invoice->id)->findOrFail($attachmentId);

            if (!Storage::disk('public')->exists($attachment->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found.'
                ], 404);
            }

            return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);

        } catch (\Exception $e) {
            Log::error('Download Attachment Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // GET VENDOR LIST (for dropdown)
    // =====================================================

    /**
     * Get all approved vendors for dropdown
     */
    public function getVendors()
    {
        try {
            $vendors = Vendor::where('approval_status', 'approved')
                ->select('id', 'vendor_name', 'vendor_email')
                ->orderBy('vendor_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $vendors
            ]);

        } catch (\Exception $e) {
            Log::error('Get Vendors Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }
}