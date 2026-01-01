<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TravelBatch;
use App\Models\TravelInvoice;
use App\Models\TravelInvoiceItem;
use App\Models\TravelInvoiceBill;
use App\Models\TravelEmployee;
use App\Models\ManagerTag;
use App\Services\ZohoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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
                'draft' => TravelInvoice::where('status', 'draft')->count(),
                'submitted' => TravelInvoice::where('status', 'submitted')->count(),
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
                    $query->whereIn('status', ['submitted', 'pending_rm', 'pending_vp', 'pending_ceo', 'pending_finance']);
                } else {
                    $query->where('status', $request->status);
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
                $batch->can_start_review = $batch->invoices->whereIn('status', ['submitted', 'resubmitted'])->count() > 0;
                
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

            // Filter invoices based on role - each role sees only relevant invoices
            if ($userRole === 'manager') {
                // Manager sees only their tagged invoices
                $managerTagIds = $this->getManagerTagIds($userId);
                
                $filteredInvoices = $batch->invoices->filter(function($invoice) use ($managerTagIds) {
                    return in_array($invoice->tag_id, $managerTagIds);
                })->values();
                
                $batch->setRelation('invoices', $filteredInvoices);
                
            } elseif ($userRole === 'vp') {
                // VP sees only invoices at pending_vp or beyond (not pending_rm, submitted)
                $filteredInvoices = $batch->invoices->filter(function($invoice) {
                    return in_array($invoice->status, ['pending_vp', 'pending_ceo', 'pending_finance', 'approved', 'rejected', 'paid']);
                })->values();
                
                $batch->setRelation('invoices', $filteredInvoices);
                
            } elseif ($userRole === 'ceo') {
                // CEO sees only invoices at pending_ceo or beyond
                $filteredInvoices = $batch->invoices->filter(function($invoice) {
                    return in_array($invoice->status, ['pending_ceo', 'pending_finance', 'approved', 'rejected', 'paid']);
                })->values();
                
                $batch->setRelation('invoices', $filteredInvoices);
                
            } elseif ($userRole === 'finance') {
                // Finance sees only invoices at pending_finance or beyond
                $filteredInvoices = $batch->invoices->filter(function($invoice) {
                    return in_array($invoice->status, ['pending_finance', 'approved', 'rejected', 'paid']);
                })->values();
                
                $batch->setRelation('invoices', $filteredInvoices);
            }
            // super-admin sees all invoices (no filter)

            $summary = $batch->getSummary();
            $approvableCount = $batch->getApprovableCountForRole($userRole, $userId);
            
            // Check can_start_review based on FILTERED invoices (for manager)
            $canStartReview = $batch->invoices->whereIn('status', ['submitted', 'resubmitted'])->count() > 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'batch' => $batch,
                    'invoices' => $batch->invoices,
                    'summary' => $summary,
                    'approvable_count' => $approvableCount,
                    'can_start_review' => $canStartReview,
                ],
                'user_role' => $userRole
            ]);

        } catch (\Exception $e) {
            Log::error('Get Batch Summary Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Batch not found.'], 404);
        }
    }

    // =====================================================
    // START REVIEW - SINGLE INVOICE
    // =====================================================

    public function startReview($id)
    {
        try {
            $invoice = TravelInvoice::findOrFail($id);
            $userId = Auth::id();

            if (!$invoice->canStartReview()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This invoice cannot be reviewed.'
                ], 400);
            }

            $invoice->startReview($userId);

            return response()->json([
                'success' => true,
                'message' => 'Invoice sent to RM for approval.',
                'data' => $invoice->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Start Review Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
        }
    }

    // =====================================================
    // START REVIEW - BATCH (All invoices - UNIVERSAL)
    // =====================================================

    public function startReviewBatch($batchId)
    {
        try {
            $batch = TravelBatch::findOrFail($batchId);
            $user = Auth::user();
            $userId = $user->id;

            // Get ALL submitted/resubmitted invoices (UNIVERSAL - not filtered by manager)
            $invoices = $batch->invoices()
                ->whereIn('status', ['submitted', 'resubmitted'])
                ->get();

            if ($invoices->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No invoices to review.'
                ], 400);
            }

            $count = 0;
            foreach ($invoices as $invoice) {
                if ($invoice->startReview($userId)) {
                    $count++;
                }
            }

            $batch->updateStatus();

            return response()->json([
                'success' => true,
                'message' => "✅ {$count} invoices sent to RM for review.",
                'count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Start Review Batch Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
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
                $zohoSynced = $this->syncToZoho($invoice);
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
    private function syncToZoho($invoice)
    {
        try {
            // TODO: Implement Zoho sync for Travel Invoices later
            Log::info('Zoho sync pending for travel invoice', ['invoice_id' => $invoice->id]);
            return false;
        } catch (\Exception $e) {
            Log::error('Zoho sync failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
        }
        return false;
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
}