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
                
                'total_amount_pending' => Invoice::whereIn('status', ['submitted', 'under_review', 'resubmitted'])->sum('grand_total'),
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
            $query = Invoice::with([
                    'vendor', 
                    'vendor.companyInfo', 
                    'attachments', 
                    'contract', 
                    'items', 
                    'items.category'
                ])
                ->orderBy('created_at', 'desc');

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                if ($request->status === 'pending') {
                    $query->whereIn('status', ['submitted', 'under_review', 'resubmitted']);
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

            $invoices = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $invoices
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
            $invoice = Invoice::with([
                'vendor',
                'vendor.companyInfo',
                'contract',
                'attachments',
                'items',
                'items.category',
                'reviewedByUser',
                'approvedByUser',
                'rejectedByUser'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $invoice
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
    // START REVIEW
    // =====================================================

    /**
     * Mark invoice as under review
     */
    public function startReview($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            if (!in_array($invoice->status, ['submitted', 'resubmitted'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This invoice cannot be reviewed.'
                ], 400);
            }

            $userId = Auth::id() ?? 1;

            $invoice->update([
                'status' => 'under_review',
                'reviewed_by' => $userId,
                'reviewed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice is now under review.',
                'data' => $invoice->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Start Review Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // APPROVE INVOICE (WITH ZOHO INTEGRATION)
    // =====================================================

    /**
     * Approve invoice and push to Zoho
     */
    public function approve(Request $request, $id)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            if (!$invoice->canReview()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This invoice cannot be approved.'
                ], 400);
            }

            $userId = Auth::id() ?? 1;

            // Update invoice status to approved
            $invoice->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
                'rejection_reason' => null,
                'rejected_by' => null,
                'rejected_at' => null,
            ]);

            // 🔥 Push to Zoho Books
            $zohoError = null;
            $zohoSynced = false;
            
            try {
                $zohoService = app(ZohoService::class);
                
                if ($zohoService->isConnected()) {
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
                // Log error but don't fail the approval
                Log::error('Failed to push invoice to Zoho', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
                $zohoError = $e->getMessage();
            }

            // Send email notification to vendor (uncomment when mail is ready)
            // try {
            //     Mail::to($invoice->vendor->vendor_email)->send(
            //         new InvoiceApprovedMail($invoice)
            //     );
            // } catch (\Exception $e) {
            //     Log::error('Invoice Approved Email Error: ' . $e->getMessage());
            // }

            $message = 'Invoice approved successfully.';
            if ($zohoError) {
                $message .= ' (Zoho sync failed: ' . $zohoError . ')';
            } elseif ($zohoSynced) {
                $message .= ' Bill created in Zoho.';
            }

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

    // =====================================================
    // REJECT INVOICE
    // =====================================================

    /**
     * Reject invoice
     */
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

            if (!$invoice->canReview()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This invoice cannot be rejected.'
                ], 400);
            }

            $userId = Auth::id() ?? 1;

            $invoice->update([
                'status' => 'rejected',
                'rejected_by' => $userId,
                'rejected_at' => now(),
                'rejection_reason' => $request->rejection_reason,
                'approved_by' => null,
                'approved_at' => null,
            ]);

            // Send email notification to vendor (uncomment when mail is ready)
            // try {
            //     Mail::to($invoice->vendor->vendor_email)->send(
            //         new InvoiceRejectedMail($invoice, $request->rejection_reason)
            //     );
            // } catch (\Exception $e) {
            //     Log::error('Invoice Rejected Email Error: ' . $e->getMessage());
            // }

            return response()->json([
                'success' => true,
                'message' => 'Invoice rejected successfully.',
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