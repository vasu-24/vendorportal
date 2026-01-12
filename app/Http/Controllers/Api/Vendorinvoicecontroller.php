<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceAttachment;
use App\Models\Contract;
use App\Models\ContractItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VendorInvoiceController extends Controller
{
    /**
     * Get authenticated vendor
     */
    private function getVendor()
    {
        return Auth::guard('vendor')->user();
    }

    // =====================================================
    // GET STATISTICS
    // =====================================================

    /**
     * Get vendor's invoice statistics
     */
    public function getStatistics()
    {
        try {
            $vendor = $this->getVendor();

        $stats = [
    'total' => Invoice::where('vendor_id', $vendor->id)->count(),
    'draft' => Invoice::where('vendor_id', $vendor->id)->where('status', 'draft')->count(),
    'submitted' => Invoice::where('vendor_id', $vendor->id)->where('status', 'submitted')->count(),
    'under_review' => Invoice::where('vendor_id', $vendor->id)->where('status', 'under_review')->count(),
    'resubmitted' => Invoice::where('vendor_id', $vendor->id)->where('status', 'resubmitted')->count(),
    'pending_rm' => Invoice::where('vendor_id', $vendor->id)->where('status', 'pending_rm')->count(),
    'pending_vp' => Invoice::where('vendor_id', $vendor->id)->where('status', 'pending_vp')->count(),
    'pending_ceo' => Invoice::where('vendor_id', $vendor->id)->where('status', 'pending_ceo')->count(),
    'pending_finance' => Invoice::where('vendor_id', $vendor->id)->where('status', 'pending_finance')->count(),
    'approved' => Invoice::where('vendor_id', $vendor->id)->where('status', 'approved')->count(),
    'rejected' => Invoice::where('vendor_id', $vendor->id)->where('status', 'rejected')->count(),
    'paid' => Invoice::where('vendor_id', $vendor->id)->where('status', 'paid')->count(),
    'total_amount' => Invoice::where('vendor_id', $vendor->id)->sum('grand_total'),
    'total_approved' => Invoice::where('vendor_id', $vendor->id)->where('status', 'approved')->sum('grand_total'),
    'total_paid' => Invoice::where('vendor_id', $vendor->id)->where('status', 'paid')->sum('grand_total'),
];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Vendor Invoice Statistics Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // LIST INVOICES
    // =====================================================

    /**
     * Get list of vendor's invoices
     */
    public function index(Request $request)
{
    try {
        $vendor = $this->getVendor();

        $query = Invoice::with(['attachments', 'contract', 'items.category'])
            ->where('vendor_id', $vendor->id)
            ->orderBy('created_at', 'desc');

        // Filter by status
     // Filter by status
if ($request->has('status') && !empty($request->status) && $request->status !== 'all') {
    if ($request->status === 'pending') {
        $query->where(function($q) {
            $q->whereIn('status', [
                'submitted', 
                'under_review', 
                'resubmitted', 
                'pending_rm', 
                'pending_vp', 
                'pending_ceo', 
                'pending_finance'
            ]);
        });
    } else {
        $query->where('status', $request->status);
    }
}

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('invoice_type', $request->type);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Date filter
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        $invoices = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $invoices
        ]);

    } catch (\Exception $e) {
        Log::error('Vendor Get Invoices Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.'
        ], 500);
    }
}



    // =====================================================
    // GET INVOICES BY STATUS
    // =====================================================

    /**
     * Get vendor's invoices by status
     */
    public function getByStatus($status)
    {
        try {
            $vendor = $this->getVendor();

            $allowedStatuses = ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'resubmitted', 'paid', 'pending'];

            if (!in_array($status, $allowedStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status.'
                ], 400);
            }

            $query = Invoice::with(['attachments', 'contract', 'items.category'])
                ->where('vendor_id', $vendor->id);

            if ($status === 'pending') {
                $query->whereIn('status', ['submitted', 'under_review', 'resubmitted']);
            } else {
                $query->where('status', $status);
            }

            $invoices = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $invoices,
                'count' => $invoices->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Vendor Get Invoices By Status Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // GET CONTRACTS (for dropdown)
    // =====================================================

    /**
     * Get vendor's contracts for dropdown
     */
    public function getContracts()
    {
        try {
            $vendor = $this->getVendor();

            $contracts = Contract::where('vendor_id', $vendor->id)
                ->where('is_visible_to_vendor', true)
                ->whereIn('status', ['draft', 'active', 'signed'])
                ->select('id', 'contract_number', 'contract_type', 'contract_value', 'sow_value')
                ->orderBy('contract_number', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $contracts
            ]);

        } catch (\Exception $e) {
            Log::error('Get Contracts Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // CREATE INVOICE
    // =====================================================

    /**
     * Store new invoice
     */
    public function store(Request $request)
    {
        try {
            $vendor = $this->getVendor();

            // Determine invoice type
            $invoiceType = $request->input('invoice_type', 'normal');
            $isAdhoc = $invoiceType === 'adhoc';

            // Validation
            $validator = Validator::make($request->all(), [
                'contract_id' => 'required|exists:contracts,id',
                'invoice_number' => 'required|string|max:50',
                'invoice_date' => 'required|date',
                'due_date' => 'nullable|date',
                'description' => 'nullable|string|max:1000',
                'base_total' => 'required|numeric|min:0',
                'gst_total' => 'required|numeric|min:0',
                'grand_total' => 'required|numeric|min:0',
                'items' => 'required|string', // JSON string of line items
                'invoice_attachment' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
                'timesheet_attachment' => 'nullable|file|mimes:xlsx,xls|max:5120', // 5MB
                'include_timesheet' => 'nullable|boolean',
                'gst_percent' => 'nullable|numeric',
                'tds_percent' => 'nullable|numeric',
                'invoice_type' => 'nullable|in:normal,adhoc',
                'exceed_notes' => 'nullable|string',
            ]);

            // Custom validation for unique invoice number per vendor
            $validator->after(function ($validator) use ($request, $vendor) {
                $exists = Invoice::where('vendor_id', $vendor->id)
                    ->where('invoice_number', $request->invoice_number)
                    ->exists();
                
                if ($exists) {
                    $validator->errors()->add('invoice_number', 'This invoice number already exists.');
                }
            });

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Parse line items
            $items = json_decode($request->items, true);
            if (empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one line item is required.'
                ], 422);
            }

            DB::beginTransaction();

            // Calculate amounts
            $baseTotal = floatval($request->base_total);
            $gstPercent = floatval($request->gst_percent ?? 18);
            $tdsPercent = floatval($request->tds_percent ?? 5);

            $gstAmount = ($baseTotal * $gstPercent) / 100;
            $grandTotal = $baseTotal + $gstAmount;
            $tdsAmount = ($baseTotal * $tdsPercent) / 100;
            $netPayable = $grandTotal - $tdsAmount;

            // Handle Timesheet Upload (only for Normal invoices)
            $timesheetPath = null;
            $timesheetFilename = null;
            if (!$isAdhoc && $request->include_timesheet && $request->hasFile('timesheet_attachment')) {
                $tsFile = $request->file('timesheet_attachment');
                $timesheetFilename = $tsFile->getClientOriginalName();
                $timesheetPath = $tsFile->storeAs('invoices/' . $vendor->id . '/timesheets', 'timesheet_' . time() . '.' . $tsFile->getClientOriginalExtension(), 'public');
            }

            // Create invoice
            $invoice = Invoice::create([
                'vendor_id' => $vendor->id,
                'contract_id' => $request->contract_id,
                'invoice_type' => $invoiceType,
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'description' => $request->description,
                'base_total' => $baseTotal,
                'gst_percent' => $gstPercent,
                'gst_amount' => $gstAmount,
                'gst_total' => $gstAmount,
                'grand_total' => $grandTotal,
                'tds_percent' => $tdsPercent,
                'tds_amount' => $tdsAmount,
                'net_payable' => $netPayable,
                'include_timesheet' => !$isAdhoc && $request->include_timesheet ? true : false,
                'timesheet_path' => $timesheetPath,
                'timesheet_filename' => $timesheetFilename,
                'zoho_gst_tax_id' => $request->zoho_gst_tax_id,
                'status' => 'draft',
                'exceed_notes' => $request->exceed_notes,
            ]);

            // Create line items
            foreach ($items as $item) {
                // Get category_id from contract_item or directly
                $categoryId = $item['category_id'] ?? null;
                
                if (!$categoryId && !empty($item['contract_item_id'])) {
                    $contractItem = ContractItem::find($item['contract_item_id']);
                    $categoryId = $contractItem ? $contractItem->category_id : null;
                }

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'contract_item_id' => $item['contract_item_id'] ?? null,
                    'category_id' => $categoryId,
                    'particulars' => $item['particulars'] ?? null,
                    'sac' => $item['sac'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? null,
                    'rate' => $item['rate'],
                    'tax_percent' => $item['tax_percent'] ?? null,
                    'amount' => $item['amount'],
                    'tag_id' => $item['tag_id'] ?? null,
                    'tag_name' => $item['tag_name'] ?? null,
                ]);
            }

            // Upload invoice attachment
            if ($request->hasFile('invoice_attachment')) {
                $file = $request->file('invoice_attachment');
                $fileName = 'invoice_' . $invoice->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('invoices/' . $vendor->id, $fileName, 'public');

                InvoiceAttachment::create([
                    'invoice_id' => $invoice->id,
                    'attachment_type' => 'invoice',
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully.',
                'data' => $invoice->load(['items.category', 'attachments'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create Invoice Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // VIEW INVOICE
    // =====================================================

    /**
     * Get invoice details
     */
    public function show($id)
    {
        try {
            $vendor = $this->getVendor();

            $invoice = Invoice::with(['attachments', 'contract', 'items.category', 'items.contractItem'])
                ->where('vendor_id', $vendor->id)
                ->findOrFail($id);

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
    // UPDATE INVOICE
    // =====================================================

    /**
     * Update invoice
     */
    public function update(Request $request, $id)
    {
        try {
            $vendor = $this->getVendor();

            $invoice = Invoice::where('vendor_id', $vendor->id)->findOrFail($id);

            // Check if editable
            if (!$invoice->isEditable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This invoice cannot be edited.'
                ], 403);
            }

            $isAdhoc = $invoice->invoice_type === 'adhoc';

            // Validation
            $validator = Validator::make($request->all(), [
                'contract_id' => 'required|exists:contracts,id',
                'invoice_number' => 'required|string|max:50',
                'invoice_date' => 'required|date',
                'due_date' => 'nullable|date',
                'description' => 'nullable|string|max:1000',
                'base_total' => 'required|numeric|min:0',
                'gst_total' => 'required|numeric|min:0',
                'grand_total' => 'required|numeric|min:0',
                'items' => 'required|string', // JSON string of line items
                'invoice_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                'timesheet_attachment' => 'nullable|file|mimes:xlsx,xls|max:5120',
                'include_timesheet' => 'nullable|boolean',
                'gst_percent' => 'nullable|numeric',
                'tds_percent' => 'nullable|numeric',
                'exceed_notes' => 'nullable|string',
            ]);

            // Check unique invoice number (excluding current)
            $validator->after(function ($validator) use ($request, $vendor, $id) {
                $exists = Invoice::where('vendor_id', $vendor->id)
                    ->where('invoice_number', $request->invoice_number)
                    ->where('id', '!=', $id)
                    ->exists();
                
                if ($exists) {
                    $validator->errors()->add('invoice_number', 'This invoice number already exists.');
                }
            });

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Parse line items
            $items = json_decode($request->items, true);
            if (empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one line item is required.'
                ], 422);
            }

            DB::beginTransaction();

            // Determine new status
            $newStatus = $invoice->status;
            if ($invoice->status === 'rejected') {
                $newStatus = 'resubmitted';
            }

            // Calculate amounts
            $baseTotal = floatval($request->base_total);
            $gstPercent = floatval($request->gst_percent ?? 18);
            $tdsPercent = floatval($request->tds_percent ?? 5);

            $gstAmount = ($baseTotal * $gstPercent) / 100;
            $grandTotal = $baseTotal + $gstAmount;
            $tdsAmount = ($baseTotal * $tdsPercent) / 100;
            $netPayable = $grandTotal - $tdsAmount;

            // Handle Timesheet Upload (only for Normal invoices)
            $timesheetPath = $invoice->timesheet_path;
            $timesheetFilename = $invoice->timesheet_filename;
            if (!$isAdhoc && $request->include_timesheet && $request->hasFile('timesheet_attachment')) {
                // Delete old timesheet if exists
                if ($invoice->timesheet_path) {
                    Storage::disk('public')->delete($invoice->timesheet_path);
                }
                $tsFile = $request->file('timesheet_attachment');
                $timesheetFilename = $tsFile->getClientOriginalName();
                $timesheetPath = $tsFile->storeAs('invoices/' . $vendor->id . '/timesheets', 'timesheet_' . time() . '.' . $tsFile->getClientOriginalExtension(), 'public');
            }

            // Update invoice
            $invoice->update([
                'contract_id' => $request->contract_id,
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'description' => $request->description,
                'base_total' => $baseTotal,
                'gst_percent' => $gstPercent,
                'gst_amount' => $gstAmount,
                'gst_total' => $gstAmount,
                'grand_total' => $grandTotal,
                'tds_percent' => $tdsPercent,
                'tds_amount' => $tdsAmount,
                'net_payable' => $netPayable,
                'include_timesheet' => !$isAdhoc && $request->include_timesheet ? true : false,
                'timesheet_path' => $timesheetPath,
                'timesheet_filename' => $timesheetFilename,
                'zoho_gst_tax_id' => $request->zoho_gst_tax_id,
                'status' => $newStatus,
                'rejection_reason' => null,
                'exceed_notes' => $request->exceed_notes,
            ]);

            // Delete old line items and create new ones
            $invoice->items()->delete();

            foreach ($items as $item) {
                $categoryId = $item['category_id'] ?? null;
                
                if (!$categoryId && !empty($item['contract_item_id'])) {
                    $contractItem = ContractItem::find($item['contract_item_id']);
                    $categoryId = $contractItem ? $contractItem->category_id : null;
                }

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'contract_item_id' => $item['contract_item_id'] ?? null,
                    'category_id' => $categoryId,
                    'particulars' => $item['particulars'] ?? null,
                    'sac' => $item['sac'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? null,
                    'rate' => $item['rate'],
                    'tax_percent' => $item['tax_percent'] ?? null,
                    'amount' => $item['amount'],
                    'tag_id' => $item['tag_id'] ?? null,
                    'tag_name' => $item['tag_name'] ?? null,
                ]);
            }

            // Update invoice attachment if new file uploaded
            if ($request->hasFile('invoice_attachment')) {
                // Delete old attachment
                $oldAttachment = $invoice->invoiceAttachment;
                if ($oldAttachment) {
                    Storage::disk('public')->delete($oldAttachment->file_path);
                    $oldAttachment->delete();
                }

                // Upload new
                $file = $request->file('invoice_attachment');
                $fileName = 'invoice_' . $invoice->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('invoices/' . $vendor->id, $fileName, 'public');

                InvoiceAttachment::create([
                    'invoice_id' => $invoice->id,
                    'attachment_type' => 'invoice',
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully.',
                'data' => $invoice->fresh()->load(['items.category', 'attachments'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Invoice Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // SUBMIT INVOICE
    // =====================================================

    /**
     * Submit invoice for approval
     * CEO approval required if invoice exceeds contract_value (Normal) or sow_value (ADHOC)
     */
    public function submit($id)
    {
        try {
            $vendor = $this->getVendor();

            $invoice = Invoice::with('contract')->where('vendor_id', $vendor->id)->findOrFail($id);

            // Check if can submit
            if (!$invoice->canSubmit()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This invoice cannot be submitted.'
                ], 403);
            }

            // Check if invoice attachment exists
            if (!$invoice->invoiceAttachment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please upload invoice document before submitting.'
                ], 422);
            }

            // Check if has line items
            if ($invoice->items()->count() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please add at least one line item before submitting.'
                ], 422);
            }

            // Determine if CEO approval is required
            $requiresCeo = false;
            $contract = $invoice->contract;
            
            if ($contract) {
                $isAdhoc = $invoice->invoice_type === 'adhoc';
                
                // Get total invoiced amount for this contract (excluding current invoice)
                $totalInvoiced = Invoice::where('contract_id', $contract->id)
                    ->where('id', '!=', $invoice->id)
                    ->whereNotIn('status', ['rejected', 'draft'])
                    ->sum('grand_total');
                
                $newTotal = $totalInvoiced + $invoice->grand_total;
                
                if ($isAdhoc) {
                    // ADHOC: Compare with SOW Value
                    $threshold = $contract->sow_value ?? 0;
                    $requiresCeo = $newTotal > $threshold;
                    Log::info("ADHOC Invoice #{$invoice->id}: Total={$newTotal}, SOW={$threshold}, RequiresCEO=" . ($requiresCeo ? 'Yes' : 'No'));
                } else {
                    // Normal: Compare with Contract Value
                    $threshold = $contract->contract_value ?? 0;
                    $requiresCeo = $newTotal > $threshold;
                    Log::info("Normal Invoice #{$invoice->id}: Total={$newTotal}, ContractValue={$threshold}, RequiresCEO=" . ($requiresCeo ? 'Yes' : 'No'));
                }
            }

            // Determine status (resubmitted if was rejected before)
          $newStatus = 'pending_rm';




// =====================================================
// ASSIGN TAGS TO ITEMS & FIND RM
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
    if ($item->contractItem && !empty($item->contractItem->tag_id)) {
        $firstTagId = $item->contractItem->tag_id;
        $firstTagName = $item->contractItem->tag_name;
        break;
    }
}

// Assign tags to all items
foreach ($invoice->items as $item) {
    $itemTagId = $item->tag_id;
    $itemTagName = $item->tag_name;
    
    if (empty($itemTagId) && $item->contractItem) {
        $itemTagId = $item->contractItem->tag_id;
        $itemTagName = $item->contractItem->tag_name;
    }
    
    if (empty($itemTagId)) {
        $itemTagId = $firstTagId;
        $itemTagName = $firstTagName;
    }
    
    $item->update([
        'tag_id' => $itemTagId,
        'tag_name' => $itemTagName,
        'rm_approved' => false,
        'rm_approved_by' => null,
        'rm_approved_at' => null,
    ]);
}

// Find RM for first tag
$assignedRmId = null;
if ($firstTagId) {
    $managerTag = \App\Models\ManagerTag::where('tag_id', $firstTagId)->first();
    if ($managerTag) {
        $assignedRmId = $managerTag->user_id;
    }
}


$invoice->update([
    'status' => 'pending_rm',
    'submitted_at' => now(),
    'rejection_reason' => null,
    'requires_ceo_approval' => $requiresCeo,
    'current_approver_role' => 'rm',
    'exceeds_contract' => $requiresCeo,
    'assigned_rm_id' => $assignedRmId,
    'assigned_tag_id' => $firstTagId,
    'assigned_tag_name' => $firstTagName,
]);
            return response()->json([
                'success' => true,
                'message' => 'Invoice submitted successfully.',
                'data' => $invoice->fresh(),
                'requires_ceo_approval' => $requiresCeo
            ]);

        } catch (\Exception $e) {
            Log::error('Submit Invoice Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    // =====================================================
    // DELETE INVOICE
    // =====================================================

    /**
     * Delete draft invoice
     */
    public function destroy($id)
    {
        try {
            $vendor = $this->getVendor();

            $invoice = Invoice::where('vendor_id', $vendor->id)->findOrFail($id);

            // Only draft invoices can be deleted
            if ($invoice->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft invoices can be deleted.'
                ], 403);
            }

            // Delete attachments from storage
            foreach ($invoice->attachments as $attachment) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            // Delete timesheet if exists
            if ($invoice->timesheet_path) {
                Storage::disk('public')->delete($invoice->timesheet_path);
            }

            // Delete invoice (cascade will delete items and attachments)
            $invoice->delete();

            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Delete Invoice Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

// =====================================================
// GET CATEGORIES (for Add Item dropdown)
// =====================================================

public function getCategories()
{
    try {
        $categories = \App\Models\Category::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'hsn_sac_code']);

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);

    } catch (\Exception $e) {
        Log::error('Get Categories Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to load categories'
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
            $vendor = $this->getVendor();

            $invoice = Invoice::where('vendor_id', $vendor->id)->findOrFail($invoiceId);
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
}