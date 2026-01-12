<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TravelBatch;
use App\Models\TravelInvoice;
use App\Models\TravelInvoiceItem;
use App\Models\TravelInvoiceBill;
use App\Models\TravelEmployee;
use App\Models\ManagerTag;
use App\Models\Vendor;
use App\Services\ZohoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class TravelInvoiceController extends Controller
{
    // =====================================================
    // GET STATISTICS
    // =====================================================

    public function getStatistics()
    {
        try {
            $stats = [
                'total' => TravelInvoice::count(),
              
                'pending_rm' => TravelInvoice::where('status', 'pending_rm')->count(),
                'pending_vp' => TravelInvoice::where('status', 'pending_vp')->count(),
                'pending_ceo' => TravelInvoice::where('status', 'pending_ceo')->count(),
                'pending_finance' => TravelInvoice::where('status', 'pending_finance')->count(),
                'approved' => TravelInvoice::where('status', 'approved')->count(),
                'rejected' => TravelInvoice::where('status', 'rejected')->count(),
                'paid' => TravelInvoice::where('status', 'paid')->count(),
                
                'total_batches' => TravelBatch::count(),
                'total_amount_pending' => TravelInvoice::pending()->sum('gross_amount'),
                'total_amount_approved' => TravelInvoice::where('status', 'approved')->sum('gross_amount'),
                'total_amount_paid' => TravelInvoice::where('status', 'paid')->sum('gross_amount'),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Get Travel Invoice Statistics Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
        }
    }

    // =====================================================
    // LIST BATCHES (Role Based)
    // =====================================================

    public function getBatches(Request $request)
    {
        try {
            $user = Auth::user();
            $userId = $user->id;
            $userRole = $user->role->slug ?? 'viewer';

            $query = TravelBatch::with(['vendor', 'invoices.employee']);
             $query->where('status', '!=', 'draft');

            // Role-based filtering
            if ($userRole === 'super-admin') {
                // See all
            } elseif ($userRole === 'manager') {
                $userTagIds = $this->getManagerTagIds($userId);
                $query->whereHas('invoices', function($q) use ($userTagIds) {
                    $q->whereIn('tag_id', $userTagIds);
                });
            } elseif ($userRole === 'vp') {
                // VP sees batches that have ANY invoice at pending_vp or beyond
                $query->where(function($q) {
                    $q->whereHas('invoices', function($q2) {
                        $q2->whereIn('status', ['pending_vp', 'pending_ceo', 'pending_finance', 'approved', 'paid']);
                    })->orWhere('status', 'rejected');
                });
            } elseif ($userRole === 'ceo') {
                // CEO sees batches that have ANY invoice at pending_ceo or beyond
                $query->whereHas('invoices', function($q) {
                    $q->whereIn('status', ['pending_ceo', 'pending_finance', 'approved', 'paid']);
                });
            } elseif ($userRole === 'finance') {
                // Finance sees batches that have ANY invoice at pending_finance or beyond
                $query->whereHas('invoices', function($q) {
                    $q->whereIn('status', ['pending_finance', 'approved', 'paid']);
                });
            } elseif ($userRole === 'viewer') {
                $query->whereIn('status', ['approved', 'paid']);
            } else {
                $query->whereRaw('1 = 0');
            }

            // Filters
            if ($request->filled('vendor_id')) {
                $query->where('vendor_id', $request->vendor_id);
            }

if ($request->filled('status') && $request->status !== 'all') {
    if ($request->status === 'pending') {
        // Batches that have ANY pending invoice
        $query->whereHas('invoices', function($q) {
            $q->whereIn('status', ['submitted', 'pending_rm', 'pending_vp', 'pending_ceo', 'pending_finance']);
        });
    } else {
        // Batches that have ANY invoice with this status (approved/rejected/paid)
        $query->whereHas('invoices', function($q) use ($request) {
            $q->where('status', $request->status);
        });
    }
}
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('batch_number', 'like', "%{$search}%")
                      ->orWhereHas('vendor', function($q2) use ($search) {
                          $q2->where('vendor_name', 'like', "%{$search}%");
                      });
                });
            }

            $batches = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            // Transform with summary
            $batches->getCollection()->transform(function ($batch) use ($userRole, $userId) {
                
                // Filter invoices for manager based on their tags
                if ($userRole === 'manager') {
                    $managerTagIds = ManagerTag::where('user_id', $userId)->pluck('tag_id')->toArray();
                    $filteredInvoices = $batch->invoices->filter(function($invoice) use ($managerTagIds) {
                        return in_array($invoice->tag_id, $managerTagIds);
                    })->values();
                    $batch->setRelation('invoices', $filteredInvoices);
                }
                
                // Now calculate summary from filtered invoices
                $batch->invoices_count = $batch->invoices->count();
                $batch->total_amount = $batch->invoices->sum('gross_amount');
                $batch->approvable_count = $batch->getApprovableCountForRole($userRole, $userId);
       
                
                $employees = $batch->invoices->pluck('employee.employee_name')->filter()->unique()->values();
                $batch->employee_summary = $employees->count() === 0 ? '-' : 
                    ($employees->count() === 1 ? $employees->first() : $employees->first() . ', +' . ($employees->count() - 1) . ' more');
                
                $locations = $batch->invoices->pluck('location')->filter()->unique()->values();
                $batch->location_summary = $locations->count() === 0 ? '-' : 
                    ($locations->count() === 1 ? $locations->first() : $locations->first() . ', +' . ($locations->count() - 1) . ' more');
                
                unset($batch->invoices);
                return $batch;
            });

            return response()->json([
                'success' => true,
                'data' => $batches,
                'user_role' => $userRole
            ]);

        } catch (\Exception $e) {
            Log::error('Get Travel Batches Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
        }
    }

 // =====================================================
// GET BATCH SUMMARY
// =====================================================

public function getBatchSummary($batchId)
{
    try {
        $user = Auth::user();
        $userId = $user->id;
        $userRole = $user->role->slug ?? 'viewer';

        $batch = TravelBatch::with([
            'vendor', 
            'vendor.companyInfo', 
            'vendor.contact', 
            'vendor.statutoryInfo',
            'invoices.employee', 
            'invoices.items', 
            'invoices.bills'
        ])->findOrFail($batchId);

        // 👇 FIRST: Exclude draft invoices for ALL roles (admin should not see drafts)
        $nonDraftInvoices = $batch->invoices->filter(function($invoice) {
            return $invoice->status !== 'draft';
        });

        // Filter invoices based on role - each role sees only relevant invoices
        if ($userRole === 'manager') {
            // Manager sees only their tagged invoices (excluding draft)
            $managerTagIds = $this->getManagerTagIds($userId);
            
            $filteredInvoices = $nonDraftInvoices->filter(function($invoice) use ($managerTagIds) {
                return in_array($invoice->tag_id, $managerTagIds);
            })->values();
            
            $batch->setRelation('invoices', $filteredInvoices);
            
        } elseif ($userRole === 'vp') {
            // VP sees only invoices at pending_vp or beyond
            $filteredInvoices = $nonDraftInvoices->filter(function($invoice) {
                return in_array($invoice->status, ['pending_vp', 'pending_ceo', 'pending_finance', 'approved', 'rejected', 'paid']);
            })->values();
            
            $batch->setRelation('invoices', $filteredInvoices);
            
        } elseif ($userRole === 'ceo') {
            // CEO sees only invoices at pending_ceo or beyond
            $filteredInvoices = $nonDraftInvoices->filter(function($invoice) {
                return in_array($invoice->status, ['pending_ceo', 'pending_finance', 'approved', 'rejected', 'paid']);
            })->values();
            
            $batch->setRelation('invoices', $filteredInvoices);
            
        } elseif ($userRole === 'finance') {
            // Finance sees only invoices at pending_finance or beyond
            $filteredInvoices = $nonDraftInvoices->filter(function($invoice) {
                return in_array($invoice->status, ['pending_finance', 'approved', 'rejected', 'paid']);
            })->values();
            
            $batch->setRelation('invoices', $filteredInvoices);
            
        } else {
            // super-admin sees all invoices (except draft)
            $batch->setRelation('invoices', $nonDraftInvoices->values());
        }

        $summary = $batch->getSummary();
        $approvableCount = $batch->getApprovableCountForRole($userRole, $userId);

        return response()->json([
            'success' => true,
            'data' => [
                'batch' => $batch,
                'invoices' => $batch->invoices,
                'summary' => $summary,
                'approvable_count' => $approvableCount,
            ],
            'user_role' => $userRole
        ]);

    } catch (\Exception $e) {
        Log::error('Get Batch Summary Error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Batch not found.'], 404);
    }
}
  

  

    // =====================================================
    // APPROVE - SINGLE INVOICE
    // =====================================================

    public function approve(Request $request, $id)
    {
        try {
            $invoice = TravelInvoice::findOrFail($id);
            $user = Auth::user();
            $userRole = $user->role->slug ?? 'viewer';
            
            // Get Zoho sync date (optional - defaults to invoice date)
            $zohoSyncDate = $request->input('zoho_sync_date', $invoice->invoice_date);

            // Check if manager can approve this invoice (tag check)
            if ($userRole === 'manager') {
                $managerTagIds = $this->getManagerTagIds($user->id);
                if (!in_array($invoice->tag_id, $managerTagIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not authorized to approve this invoice.'
                    ], 403);
                }
            }

            $result = $invoice->approve($user);

            if (!$result['success']) {
                return response()->json($result, 403);
            }

            // Zoho sync if approved
            $zohoSynced = false;
            if ($result['new_status'] === 'approved') {
                $zohoSynced = $this->syncToZoho($invoice, $zohoSyncDate);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'] . ($zohoSynced ? ' Pushed to Zoho.' : ''),
                'data' => $invoice->fresh(),
                'zoho_synced' => $zohoSynced,
            ]);

        } catch (\Exception $e) {
            Log::error('Approve Travel Invoice Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
        }
    }

    // =====================================================
    // REJECT - SINGLE INVOICE
    // =====================================================

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

            $invoice = TravelInvoice::findOrFail($id);
            $user = Auth::user();
            $userRole = $user->role->slug ?? 'viewer';

            // Check if manager can reject this invoice (tag check)
            if ($userRole === 'manager') {
                $managerTagIds = $this->getManagerTagIds($user->id);
                if (!in_array($invoice->tag_id, $managerTagIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not authorized to reject this invoice.'
                    ], 403);
                }
            }

            $result = $invoice->reject($user, $request->rejection_reason);

            if (!$result['success']) {
                return response()->json($result, 403);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $invoice->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Reject Travel Invoice Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
        }
    }

    // =====================================================
    // APPROVE ALL - BATCH (Role-based)
    // =====================================================

    public function approveAll(Request $request, $batchId)
    {
        try {
            $user = Auth::user();
            $userId = $user->id;
            $userRole = $user->role->slug ?? 'viewer';

            $batch = TravelBatch::findOrFail($batchId);
            $invoices = $batch->getApprovableInvoicesForRole($userRole, $userId);

            if ($invoices->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No invoices to approve at this stage.'
                ], 400);
            }

            $approvedCount = 0;
            $zohoSynced = false;
            $lastStatus = null;

            foreach ($invoices as $invoice) {
                $result = $invoice->approve($user);
                if ($result['success']) {
                    $approvedCount++;
                    $lastStatus = $result['new_status'] ?? null;

                    // Zoho sync if approved
                    if ($lastStatus === 'approved') {
                        $zohoSynced = $this->syncToZoho($invoice) || $zohoSynced;
                    }
                }
            }

            $batch->updateStatus();

            $message = $this->getBulkApprovalMessage($approvedCount, $lastStatus);
            if ($zohoSynced) $message .= ' Pushed to Zoho.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'approved_count' => $approvedCount,
                'new_status' => $lastStatus,
                'zoho_synced' => $zohoSynced,
            ]);

        } catch (\Exception $e) {
            Log::error('Approve All Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
        }
    }

    // =====================================================
    // REJECT ALL - BATCH
    // =====================================================

    public function rejectAll(Request $request, $batchId)
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

            $user = Auth::user();
            $userId = $user->id;
            $userRole = $user->role->slug ?? 'viewer';

            $batch = TravelBatch::findOrFail($batchId);
            $invoices = $batch->getApprovableInvoicesForRole($userRole, $userId);

            if ($invoices->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No invoices to reject at this stage.'
                ], 400);
            }

            $rejectedCount = 0;
            foreach ($invoices as $invoice) {
                $result = $invoice->reject($user, $request->rejection_reason);
                if ($result['success']) {
                    $rejectedCount++;
                }
            }

            $batch->updateStatus();

            $roleName = $this->getRoleLabel($userRole);

            return response()->json([
                'success' => true,
                'message' => "❌ {$rejectedCount} invoices rejected by {$roleName}.",
                'rejected_count' => $rejectedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Reject All Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
        }
    }

    // =====================================================
    // GET INVOICE DETAILS
    // =====================================================

    public function show($id)
    {
        try {
            $user = Auth::user();
            $userRole = $user->role->slug ?? 'viewer';

            $invoice = TravelInvoice::with([
                'batch',
                'vendor',
                'vendor.companyInfo',
                'employee',
                'items',
                'bills',
                'assignedRm',
                'rmApprovedByUser',
                'vpApprovedByUser',
                'ceoApprovedByUser',
                'financeApprovedByUser',
                'approvedByUser',
                'rejectedByUser',
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $invoice,
                'timeline' => $invoice->getApprovalTimeline(),
                'can_approve' => $invoice->canBeApprovedBy($user),
                'can_reject' => $invoice->canBeRejectedBy($user),
                'user_role' => $userRole,
            ]);

        } catch (\Exception $e) {
            Log::error('Get Travel Invoice Details Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        }
    }

    // =====================================================
    // MARK AS PAID
    // =====================================================

    public function markAsPaid(Request $request, $id)
    {
        try {
            $invoice = TravelInvoice::findOrFail($id);

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

            if ($invoice->batch) {
                $invoice->batch->updateStatus();
            }

            return response()->json([
                'success' => true,
                'message' => 'Invoice marked as paid.',
                'data' => $invoice->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Mark As Paid Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
        }
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Get tag IDs assigned to a manager
     */
    private function getManagerTagIds($userId)
    {
        return ManagerTag::where('user_id', $userId)->pluck('tag_id')->toArray();
    }

    /**
     * Sync invoice to Zoho (TODO: Implement later)
     */
 /**
 * Sync invoice to Zoho
 * 
 * 
 * 
 * 
 */


// In TravelInvoiceController.php
public function vendors()
{
    try {
        $vendors = Vendor::whereNotIn('status', ['pending', 'rejected'])
            ->select('id', 'vendor_name')
            ->orderBy('vendor_name')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $vendors
        ]);
    } catch (\Exception $e) {
        Log::error('Get Travel Vendors Error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to load vendors'], 500);
    }
}


private function syncToZoho($invoice, $zohoSyncDate = null)
{
    try {
        $zohoService = app(ZohoService::class);
        
        if (!$zohoService->isConnected()) {
            Log::warning('Zoho not connected, skipping travel bill sync', [
                'invoice_id' => $invoice->id,
            ]);
            return false;
        }
        
        // Save zoho_sync_date to invoice - FORMAT AS DATE ONLY!
        if ($zohoSyncDate) {
            $invoice->zoho_sync_date = \Carbon\Carbon::parse($zohoSyncDate)->format('Y-m-d');
            $invoice->save();
        }
        
        $zohoService->createTravelBill($invoice);
        
        Log::info('Travel Invoice pushed to Zoho', [
            'invoice_id' => $invoice->id,
            'zoho_bill_id' => $invoice->fresh()->zoho_bill_id,
        ]);
        
        return true;
        
    } catch (\Exception $e) {
        Log::error('Zoho sync failed for travel invoice', [
            'invoice_id' => $invoice->id,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

    /**
     * Get bulk approval message based on status
     */
    private function getBulkApprovalMessage($count, $status)
    {
        $messages = [
            'pending_rm' => "✅ {$count} invoices sent to RM for review.",
            'pending_vp' => "✅ {$count} invoices approved by RM and sent to VOO.",
            'pending_ceo' => "✅ {$count} invoices approved by VOO and sent to CEO.",
            'pending_finance' => "✅ {$count} invoices approved and sent to Finance.",
            'approved' => "✅ {$count} invoices approved by Finance.",
        ];

        return $messages[$status] ?? "✅ {$count} invoices updated.";
    }

    /**
     * Get role label for display
     */









    private function getRoleLabel($role)
    {
        $labels = [
            'manager' => 'RM',
            'vp' => 'VOO',
            'ceo' => 'CEO',
            'finance' => 'Finance',
            'super-admin' => 'Super Admin',
        ];

        return $labels[$role] ?? 'Unknown';
    }

/**
 * Update Travel Invoice (Finance Edit)
 * PUT /api/admin/travel-invoices/{id}/update
 */
public function updateInvoice(Request $request, $id)
{
    try {
        $invoice = TravelInvoice::with('items')->findOrFail($id);
        
        // Only allow edit at pending_finance status
        if ($invoice->status !== 'pending_finance') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice can only be edited at pending_finance stage'
            ], 400);
        }
        
        // Validate request
        $validated = $request->validate([
            'location' => 'nullable|string|max:255',
            'travel_date' => 'nullable|date',
            'tds_percent' => 'nullable|numeric|min:0|max:100',
            'tds_tax_id' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.id' => 'required|exists:travel_invoice_items,id',
            'items.*.mode' => 'nullable|string',
            'items.*.particulars' => 'nullable|string',
            'items.*.basic' => 'nullable|numeric|min:0',
            'items.*.taxes' => 'nullable|numeric|min:0',
            'items.*.service_charge' => 'nullable|numeric|min:0',
            'items.*.gst' => 'nullable|numeric|min:0',
        ]);
        
        DB::beginTransaction();
        
        // Update invoice basic info
        $invoice->update([
            'location' => $validated['location'] ?? $invoice->location,
            'travel_date' => $validated['travel_date'] ?? $invoice->travel_date,
            'tds_percent' => $validated['tds_percent'] ?? $invoice->tds_percent,
            'tds_tax_id' => $validated['tds_tax_id'] ?? $invoice->tds_tax_id,
        ]);
        
        // Update items
        $totalBasic = 0;
        $totalTaxes = 0;
        $totalService = 0;
        $totalGst = 0;
        
        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $itemData) {
                $item = TravelInvoiceItem::find($itemData['id']);
                if ($item && $item->travel_invoice_id == $invoice->id) {
                    $basic = $itemData['basic'] ?? $item->basic;
                    $taxes = $itemData['taxes'] ?? $item->taxes;
                    $serviceCharge = $itemData['service_charge'] ?? $item->service_charge;
                    $gst = $itemData['gst'] ?? $item->gst;
                    $grossAmount = $basic + $taxes + $serviceCharge + $gst;
                    
                    $item->update([
                        'mode' => $itemData['mode'] ?? $item->mode,
                        'particulars' => $itemData['particulars'] ?? $item->particulars,
                        'basic' => $basic,
                        'taxes' => $taxes,
                        'service_charge' => $serviceCharge,
                        'gst' => $gst,
                        'gross_amount' => $grossAmount,
                    ]);
                    
                    $totalBasic += $basic;
                    $totalTaxes += $taxes;
                    $totalService += $serviceCharge;
                    $totalGst += $gst;
                }
            }
        } else {
            // Recalculate from existing items
            foreach ($invoice->items as $item) {
                $totalBasic += $item->basic;
                $totalTaxes += $item->taxes;
                $totalService += $item->service_charge;
                $totalGst += $item->gst;
            }
        }
        
        // Calculate totals
        $grossAmount = $totalBasic + $totalTaxes + $totalService + $totalGst;
        $tdsPercent = $validated['tds_percent'] ?? $invoice->tds_percent ?? 5;
        $tdsAmount = ($totalBasic * $tdsPercent) / 100;
        $netAmount = $grossAmount - $tdsAmount;
        
        // Update invoice totals
        $invoice->update([
            'basic_total' => $totalBasic,
            'taxes_total' => $totalTaxes,
            'service_charge_total' => $totalService,
            'gst_total' => $totalGst,
            'gross_amount' => $grossAmount,
            'tds_amount' => $tdsAmount,
            'net_amount' => $netAmount,
        ]);
        
        DB::commit();
        
        Log::info('Travel Invoice updated by Finance', [
            'invoice_id' => $invoice->id,
            'updated_by' => auth()->id(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully',
            'data' => $invoice->fresh()->load('items')
        ]);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Travel Invoice Update Error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to update invoice'
        ], 500);
    }
}

}