<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TravelBatch;
use App\Models\TravelInvoice;
use App\Models\TravelInvoiceItem;
use App\Models\TravelInvoiceBill;
use App\Models\TravelEmployee;
use App\Models\ManagerTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VendorTravelInvoiceController extends Controller
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

    public function getStatistics()
    {
        try {
            $vendor = $this->getVendor();

            $stats = [
                'total_batches' => TravelBatch::where('vendor_id', $vendor->id)->count(),
                'total_invoices' => TravelInvoice::where('vendor_id', $vendor->id)->count(),
                'draft' => TravelInvoice::where('vendor_id', $vendor->id)->where('status', 'draft')->count(),
                'pending' => TravelInvoice::where('vendor_id', $vendor->id)
                    ->whereIn('status', ['submitted', 'resubmitted', 'pending_rm', 'pending_vp', 'pending_ceo', 'pending_finance'])->count(),
                'approved' => TravelInvoice::where('vendor_id', $vendor->id)->where('status', 'approved')->count(),
                'rejected' => TravelInvoice::where('vendor_id', $vendor->id)->where('status', 'rejected')->count(),
                'paid' => TravelInvoice::where('vendor_id', $vendor->id)->where('status', 'paid')->count(),
                'total_amount' => TravelInvoice::where('vendor_id', $vendor->id)->sum('gross_amount'),
                'total_approved' => TravelInvoice::where('vendor_id', $vendor->id)->where('status', 'approved')->sum('gross_amount'),
                'total_paid' => TravelInvoice::where('vendor_id', $vendor->id)->where('status', 'paid')->sum('gross_amount'),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Vendor Travel Statistics Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // GET BATCHES LIST (FIXED - handles pending status)
    // =====================================================

public function getBatches(Request $request)
{
    try {
        $vendor = $this->getVendor();

        $query = TravelBatch::where('vendor_id', $vendor->id)
            ->withCount('invoices')
            ->with(['invoices:id,batch_id,employee_id,location', 'invoices.employee:id,employee_name']);

        // Filter by status - handle special cases
        if ($request->filled('status') && $request->status !== 'all' && $request->status !== '') {
            $status = $request->status;
            
            if ($status === 'pending') {
                $query->whereIn('status', ['submitted', 'resubmitted', 'pending', 'pending_rm', 'pending_vp', 'pending_ceo', 'pending_finance']);
            } elseif ($status === 'rejected') {
                $query->where(function($q) {
                    $q->where('status', 'rejected')
                      ->orWhereHas('invoices', function($q2) {
                          $q2->where('status', 'rejected');
                      });
                });
            } elseif ($status === 'approved') {
                $query->where(function($q) {
                    $q->where('status', 'approved')
                      ->orWhereHas('invoices', function($q2) {
                          $q2->where('status', 'approved');
                      });
                });
            } elseif ($status === 'paid') {
                $query->where(function($q) {
                    $q->where('status', 'paid')
                      ->orWhereHas('invoices', function($q2) {
                          $q2->where('status', 'paid');
                      });
                });
            } else {
                $query->where('status', $status);
            }
        }

        // Search
        if ($request->filled('search')) {
            $query->where('batch_number', 'like', "%{$request->search}%");
        }

        $batches = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        // Add employee and location summary to each batch
        $batches->getCollection()->transform(function ($batch) {
            $invoices = $batch->invoices;
            
            // Employee summary
            $employees = $invoices->pluck('employee.employee_name')->filter()->unique()->values();
            if ($employees->count() === 0) {
                $batch->employee_summary = '-';
            } elseif ($employees->count() === 1) {
                $batch->employee_summary = $employees->first();
            } else {
                $batch->employee_summary = $employees->first() . ', +' . ($employees->count() - 1) . ' more';
            }

            // Location summary
            $locations = $invoices->pluck('location')->filter()->unique()->values();
            if ($locations->count() === 0) {
                $batch->location_summary = '-';
            } elseif ($locations->count() === 1) {
                $batch->location_summary = $locations->first();
            } else {
                $batch->location_summary = $locations->first() . ', +' . ($locations->count() - 1) . ' more';
            }

            // Remove invoices from response to keep it light
            unset($batch->invoices);

            return $batch;
        });

        return response()->json([
            'success' => true,
            'data' => $batches
        ]);

    } catch (\Exception $e) {
        Log::error('Vendor Get Batches Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.'
        ], 500);
    }
}

    // =====================================================
    // GET BATCH DETAILS WITH INVOICES
    // =====================================================

    public function getBatchDetails($batchId)
    {
        try {
            $vendor = $this->getVendor();

            $batch = TravelBatch::where('vendor_id', $vendor->id)
                ->findOrFail($batchId);

            $invoices = TravelInvoice::with(['employee', 'items', 'bills'])
                ->where('batch_id', $batchId)
                ->get()
                ->map(function ($invoice) {
                    // Vendor sees simple status
                    $invoice->display_status = $invoice->vendor_status;
                    return $invoice;
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'batch' => $batch,
                    'invoices' => $invoices,
                    'summary' => [
                        'total_invoices' => $invoices->count(),
                        'total_amount' => $invoices->sum('gross_amount'),
                        'pending' => $invoices->whereIn('status', ['submitted', 'resubmitted', 'pending_rm', 'pending_vp', 'pending_ceo', 'pending_finance'])->count(),
                        'approved' => $invoices->where('status', 'approved')->count(),
                        'rejected' => $invoices->where('status', 'rejected')->count(),
                        'paid' => $invoices->where('status', 'paid')->count(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Vendor Get Batch Details Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Batch not found.'
            ], 404);
        }
    }

    // =====================================================
    // LIST ALL INVOICES
    // =====================================================

    public function index(Request $request)
    {
        try {
            $vendor = $this->getVendor();

            $query = TravelInvoice::with(['batch', 'employee', 'items', 'bills'])
                ->where('vendor_id', $vendor->id);

            // Filter by status (vendor sees simple status)
            if ($request->has('status') && $request->status !== 'all') {
                if ($request->status === 'pending') {
                    $query->whereIn('status', ['submitted', 'resubmitted', 'pending_rm', 'pending_vp', 'pending_ceo', 'pending_finance']);
                } else {
                    $query->where('status', $request->status);
                }
            }

            // Filter by batch
            if ($request->has('batch_id') && $request->batch_id) {
                $query->where('batch_id', $request->batch_id);
            }

            // Filter by invoice type
            if ($request->has('invoice_type') && $request->invoice_type !== 'all') {
                $query->where('invoice_type', $request->invoice_type);
            }

            // Search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%")
                      ->orWhereHas('employee', function ($q2) use ($search) {
                          $q2->where('employee_name', 'like', "%{$search}%");
                      });
                });
            }

            $invoices = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 10));

            // Transform to show vendor-friendly status
            $invoices->getCollection()->transform(function ($invoice) {
                $invoice->display_status = $invoice->vendor_status;
                return $invoice;
            });

            return response()->json([
                'success' => true,
                'data' => $invoices
            ]);

        } catch (\Exception $e) {
            Log::error('Vendor Get Travel Invoices Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // GET INVOICE DETAILS
    // =====================================================

    public function show($id)
    {
        try {
            $vendor = $this->getVendor();

            $invoice = TravelInvoice::with(['batch', 'employee', 'items', 'bills', 'referenceInvoice'])
                ->where('vendor_id', $vendor->id)
                ->findOrFail($id);

            $invoice->display_status = $invoice->vendor_status;

            return response()->json([
                'success' => true,
                'data' => $invoice
            ]);

        } catch (\Exception $e) {
            Log::error('Vendor Get Travel Invoice Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Invoice not found.'
            ], 404);
        }
    }

    // =====================================================
    // GET EMPLOYEES DROPDOWN
    // =====================================================

    public function getEmployees()
    {
        try {
            $employees = TravelEmployee::where('is_active', true)
                ->select('id', 'employee_name', 'employee_code', 'tag_id', 'tag_name')
                ->orderBy('employee_name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $employees
            ]);

        } catch (\Exception $e) {
            Log::error('Get Employees Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }




public function getSubmittedInvoices()
{
    try {
        $vendor = $this->getVendor();
        
        $invoices = TravelInvoice::where('vendor_id', $vendor->id)
            ->where('invoice_type', 'tax_invoice')
            ->whereIn('status', ['submitted', 'resubmitted', 'pending_rm', 'pending_vp', 'pending_ceo', 'pending_finance', 'approved'])
            ->with(['batch:id,batch_number', 'employee:id,employee_name'])
            ->select('id', 'batch_id', 'employee_id', 'invoice_number', 'invoice_type', 
                     'location', 'travel_type', 'travel_date', 'gross_amount', 'tds_percent', 'tag_name')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $invoices,
            'debug_vendor_id' => $vendor->id // 👈 ADD THIS
        ]);

    } catch (\Exception $e) {
        Log::error('Get Submitted Invoices Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.'
        ], 500);
    }
}












    // =====================================================
    // GET NEXT BATCH NUMBER (Preview without creating)
    // =====================================================

    public function getNextBatchNumber()
    {
        try {
            $vendor = $this->getVendor();

            // Get next batch number without creating
            $lastBatch = TravelBatch::where('vendor_id', $vendor->id)
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = 1;
            if ($lastBatch) {
                // Extract number from batch_number (e.g., "BATCH-001" -> 1)
                preg_match('/(\d+)$/', $lastBatch->batch_number, $matches);
                if (isset($matches[1])) {
                    $nextNumber = intval($matches[1]) + 1;
                }
            }

            $batchNumber = 'BATCH-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            return response()->json([
                'success' => true,
                'data' => [
                    'batch_number' => $batchNumber
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get Next Batch Number Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // CREATE BATCH (Start new submission)
    // =====================================================

    public function createBatch()
    {
        try {
            $vendor = $this->getVendor();

            $batchNumber = TravelBatch::generateBatchNumber($vendor->id);

            $batch = TravelBatch::create([
                'batch_number' => $batchNumber,
                'vendor_id' => $vendor->id,
                'total_invoices' => 0,
                'total_amount' => 0,
                'status' => 'draft',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Batch created successfully.',
                'data' => $batch
            ]);

        } catch (\Exception $e) {
            Log::error('Create Batch Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // CREATE INVOICE (Add to batch)
    // =====================================================

    public function store(Request $request)
    {
        try {
            $vendor = $this->getVendor();

            // Decode items if sent as JSON string
            $data = $request->all();
            if (isset($data['items']) && is_string($data['items'])) {
                $data['items'] = json_decode($data['items'], true);
            }

            $validator = Validator::make($data, [
                'batch_id' => 'required|exists:travel_batches,id',
                'employee_id' => 'required|exists:travel_employees,id',
                'invoice_number' => 'required|string|max:50',
                'invoice_type' => 'required|in:tax_invoice,credit_note',
                'invoice_date' => 'required|date',
                'reference_invoice_id' => 'nullable|exists:travel_invoices,id',
                'location' => 'nullable|string|max:255',
             'travel_type' => 'nullable|string|max:255',
                'category_id' => 'nullable|exists:categories,id',
                'travel_date' => 'nullable|date',
                'description' => 'nullable|string|max:1000',
                'tds_percent' => 'nullable|numeric|min:0|max:100',
                'items' => 'required|array|min:1',
                'items.*.mode' => 'required|in:flight,cabs,train,insurance,accommodation,visa,other',
                'items.*.mode_other' => 'nullable|string|max:100',
                'items.*.particulars' => 'nullable|string|max:500',
                'items.*.expense_date' => 'nullable|date',
                'items.*.basic' => 'required|numeric|min:0',
                'items.*.taxes' => 'nullable|numeric|min:0',
                'items.*.service_charge' => 'nullable|numeric|min:0',
                'items.*.gst' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check batch belongs to vendor
            $batch = TravelBatch::where('vendor_id', $vendor->id)
                ->where('id', $request->batch_id)
                ->first();

            if (!$batch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid batch.'
                ], 400);
            }

            // Check if credit note has reference invoice
            if ($request->invoice_type === 'credit_note' && !$request->reference_invoice_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credit note must reference an original invoice.'
                ], 422);
            }

            // Get employee details
            $employee = TravelEmployee::findOrFail($request->employee_id);

            DB::beginTransaction();

            // Create invoice
            $invoice = TravelInvoice::create([
                'batch_id' => $request->batch_id,
                'vendor_id' => $vendor->id,
                'employee_id' => $request->employee_id,
                'invoice_number' => $request->invoice_number,
                'invoice_type' => $request->invoice_type,
                'invoice_date' => $request->invoice_date,
                'reference_invoice_id' => $request->reference_invoice_id,
                'tag_id' => $employee->tag_id,
                'tag_name' => $employee->tag_name,
                'project_code' => $employee->tag_id,
                'location' => $request->location,
                'travel_type' => $request->travel_type,
                'category_id' => $request->category_id,
                'travel_date' => $request->travel_date,
                'description' => $request->description,
                'tds_percent' => $request->tds_percent ?? 5,
                'status' => 'draft',
            ]);

            // Auto-assign RM
            $invoice->assigned_rm_id = $employee->getManagerId();
            $invoice->save();

            // Create items
            $basicTotal = 0;
            $taxesTotal = 0;
            $serviceChargeTotal = 0;
            $gstTotal = 0;
            $grossTotal = 0;

            foreach ($data['items'] as $itemData) {
                $basic = floatval($itemData['basic'] ?? 0);
                $taxes = floatval($itemData['taxes'] ?? 0);
                $serviceCharge = floatval($itemData['service_charge'] ?? 0);
                $gst = floatval($itemData['gst'] ?? 0);
                $grossAmount = $basic + $taxes + $serviceCharge + $gst;

                TravelInvoiceItem::create([
                    'travel_invoice_id' => $invoice->id,
                    'mode' => $itemData['mode'],
                    'mode_other' => $itemData['mode_other'] ?? null,
                    'particulars' => $itemData['particulars'] ?? null,
                    'expense_date' => $itemData['expense_date'] ?? null,
                    'basic' => $basic,
                    'taxes' => $taxes,
                    'service_charge' => $serviceCharge,
                    'gst' => $gst,
                    'gross_amount' => $grossAmount,
                ]);

                $basicTotal += $basic;
                $taxesTotal += $taxes;
                $serviceChargeTotal += $serviceCharge;
                $gstTotal += $gst;
                $grossTotal += $grossAmount;
            }

            // Update invoice totals
            $tdsAmount = ($basicTotal * $invoice->tds_percent) / 100;
            $netAmount = $grossTotal - $tdsAmount;

            $invoice->update([
                'basic_total' => $basicTotal,
                'taxes_total' => $taxesTotal,
                'service_charge_total' => $serviceChargeTotal,
                'gst_total' => $gstTotal,
                'gross_amount' => $grossTotal,
                'tds_amount' => $tdsAmount,
                'net_amount' => $netAmount,
            ]);

            // Update batch totals
            $batch->updateTotals();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully.',
                'data' => $invoice->fresh()->load(['employee', 'items'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create Travel Invoice Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // UPDATE INVOICE
    // =====================================================

    public function update(Request $request, $id)
    {
        try {
            $vendor = $this->getVendor();

            $invoice = TravelInvoice::where('vendor_id', $vendor->id)
                ->findOrFail($id);

            if (!$invoice->isEditable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This invoice cannot be edited.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:travel_employees,id',
                'invoice_number' => 'required|string|max:50',
                'invoice_type' => 'required|in:tax_invoice,credit_note',
                'invoice_date' => 'required|date',
                'reference_invoice_id' => 'nullable|exists:travel_invoices,id',
                'location' => 'nullable|string|max:255',
              'travel_type' => 'nullable|string|max:255',
                'category_id' => 'nullable|exists:categories,id',
                'travel_date' => 'nullable|date',
                'description' => 'nullable|string|max:1000',
                'tds_percent' => 'nullable|numeric|min:0|max:100',
                'items' => 'required|array|min:1',
                'items.*.mode' => 'required|in:flight,cabs,train,insurance,accommodation,visa,other',
                'items.*.mode_other' => 'nullable|string|max:100',
                'items.*.particulars' => 'nullable|string|max:500',
                'items.*.expense_date' => 'nullable|date',
                'items.*.basic' => 'required|numeric|min:0',
                'items.*.taxes' => 'nullable|numeric|min:0',
                'items.*.service_charge' => 'nullable|numeric|min:0',
                'items.*.gst' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get employee details
            $employee = TravelEmployee::findOrFail($request->employee_id);

            DB::beginTransaction();

            // Determine new status
            $newStatus = $invoice->status;
            if ($invoice->status === 'rejected') {
                $newStatus = 'draft'; // Will become resubmitted when submitted
            }

            // Update invoice
            $invoice->update([
                'employee_id' => $request->employee_id,
                'invoice_number' => $request->invoice_number,
                'invoice_type' => $request->invoice_type,
                'invoice_date' => $request->invoice_date,
                'reference_invoice_id' => $request->reference_invoice_id,
                'tag_id' => $employee->tag_id,
                'tag_name' => $employee->tag_name,
                'project_code' => $employee->tag_id,
                'location' => $request->location,
                'travel_type' => $request->travel_type,
                'category_id' => $request->category_id,
                'travel_date' => $request->travel_date,
                'description' => $request->description,
                'tds_percent' => $request->tds_percent ?? 5,
                'status' => $newStatus,
                'assigned_rm_id' => $employee->getManagerId(),
                'rejection_reason' => null,
            ]);

            // Delete old items and create new ones
            $invoice->items()->delete();

            $basicTotal = 0;
            $taxesTotal = 0;
            $serviceChargeTotal = 0;
            $gstTotal = 0;
            $grossTotal = 0;

            foreach ($request->items as $itemData) {
                $basic = floatval($itemData['basic'] ?? 0);
                $taxes = floatval($itemData['taxes'] ?? 0);
                $serviceCharge = floatval($itemData['service_charge'] ?? 0);
                $gst = floatval($itemData['gst'] ?? 0);
                $grossAmount = $basic + $taxes + $serviceCharge + $gst;

                TravelInvoiceItem::create([
                    'travel_invoice_id' => $invoice->id,
                    'mode' => $itemData['mode'],
                    'mode_other' => $itemData['mode_other'] ?? null,
                    'particulars' => $itemData['particulars'] ?? null,
                    'expense_date' => $itemData['expense_date'] ?? null,
                    'basic' => $basic,
                    'taxes' => $taxes,
                    'service_charge' => $serviceCharge,
                    'gst' => $gst,
                    'gross_amount' => $grossAmount,
                ]);

                $basicTotal += $basic;
                $taxesTotal += $taxes;
                $serviceChargeTotal += $serviceCharge;
                $gstTotal += $gst;
                $grossTotal += $grossAmount;
            }

            // Update invoice totals
            $tdsAmount = ($basicTotal * $invoice->tds_percent) / 100;
            $netAmount = $grossTotal - $tdsAmount;

            $invoice->update([
                'basic_total' => $basicTotal,
                'taxes_total' => $taxesTotal,
                'service_charge_total' => $serviceChargeTotal,
                'gst_total' => $gstTotal,
                'gross_amount' => $grossTotal,
                'tds_amount' => $tdsAmount,
                'net_amount' => $netAmount,
            ]);

            // Update batch totals
            $invoice->batch->updateTotals();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully.',
                'data' => $invoice->fresh()->load(['employee', 'items'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Travel Invoice Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // UPLOAD BILLS
    // =====================================================

    public function uploadBills(Request $request, $id)
    {
        try {
            $vendor = $this->getVendor();

            $invoice = TravelInvoice::where('vendor_id', $vendor->id)
                ->findOrFail($id);

            if (!in_array($invoice->status, ['draft', 'rejected'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bills can only be uploaded for draft or rejected invoices.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'bills' => 'required|array|min:1',
                'bills.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $uploadedBills = [];

            foreach ($request->file('bills') as $file) {
                $fileName = 'bill_' . $invoice->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('travel_invoices/' . $vendor->id . '/bills', $fileName, 'public');

                $bill = TravelInvoiceBill::create([
                    'travel_invoice_id' => $invoice->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                ]);

                $uploadedBills[] = $bill;
            }

            return response()->json([
                'success' => true,
                'message' => count($uploadedBills) . ' bill(s) uploaded successfully.',
                'data' => $uploadedBills
            ]);

        } catch (\Exception $e) {
            Log::error('Upload Bills Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // DELETE BILL
    // =====================================================

    public function deleteBill($invoiceId, $billId)
    {
        try {
            $vendor = $this->getVendor();

            $invoice = TravelInvoice::where('vendor_id', $vendor->id)
                ->findOrFail($invoiceId);

            if (!in_array($invoice->status, ['draft', 'rejected'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bills can only be deleted for draft or rejected invoices.'
                ], 403);
            }

            $bill = TravelInvoiceBill::where('travel_invoice_id', $invoiceId)
                ->findOrFail($billId);

            // Delete file from storage
            $bill->deleteFile();
            $bill->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bill deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete Bill Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // DELETE INVOICE
    // =====================================================

    public function destroy($id)
    {
        try {
            $vendor = $this->getVendor();

            $invoice = TravelInvoice::where('vendor_id', $vendor->id)
                ->findOrFail($id);

            if ($invoice->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft invoices can be deleted.'
                ], 403);
            }

            $batch = $invoice->batch;

            // Delete bills from storage
            foreach ($invoice->bills as $bill) {
                $bill->deleteFile();
            }

            // Delete invoice (cascade will delete items and bills)
            $invoice->delete();

            // Update batch totals
            $batch->updateTotals();

            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete Travel Invoice Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // =====================================================
    // SUBMIT BATCH
    // =====================================================

    public function submitBatch($batchId)
{
    try {
        $vendor = $this->getVendor();

        $batch = TravelBatch::where('vendor_id', $vendor->id)
            ->findOrFail($batchId);

        if ($batch->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft batches can be submitted.'
            ], 400);
        }

        // Check if batch has invoices
        $invoiceCount = $batch->invoices()->count();
        if ($invoiceCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Please add at least one invoice before submitting.'
            ], 422);
        }

        // Check if all invoices have bills
        $invoicesWithoutBills = $batch->invoices()
            ->whereDoesntHave('bills')
            ->count();

        if ($invoicesWithoutBills > 0) {
            return response()->json([
                'success' => false,
                'message' => "{$invoicesWithoutBills} invoice(s) don't have bills attached."
            ], 422);
        }

        DB::beginTransaction();

        // Update all draft invoices in batch to pending_rm directly
        $batch->invoices()
            ->where('status', 'draft')
            ->update([
                'status' => 'pending_rm',
                'submitted_at' => now(),
                'current_approver_role' => 'rm',
            ]);

        // Update rejected invoices to pending_rm directly
        $batch->invoices()
            ->where('status', 'rejected')
            ->update([
                'status' => 'pending_rm',
                'submitted_at' => now(),
                'current_approver_role' => 'rm',
                'rejection_reason' => null,
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

        // Update batch status
        $batch->update([
            'status' => 'pending_rm',
            'submitted_at' => now(),
        ]);

        DB::commit();

        Log::info('Travel Batch submitted', [
            'batch_id' => $batch->id,
            'vendor_id' => $vendor->id,
            'invoice_count' => $invoiceCount,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Batch submitted successfully with {$invoiceCount} invoice(s).",
            'data' => $batch->fresh()
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Submit Batch Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.'
        ], 500);
    }
}

    // =====================================================
    // DOWNLOAD BILL
    // =====================================================

    public function downloadBill($invoiceId, $billId)
    {
        try {
            $vendor = $this->getVendor();

            $invoice = TravelInvoice::where('vendor_id', $vendor->id)
                ->findOrFail($invoiceId);

            $bill = TravelInvoiceBill::where('travel_invoice_id', $invoiceId)
                ->findOrFail($billId);

            if (!Storage::disk('public')->exists($bill->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found.'
                ], 404);
            }

            return Storage::disk('public')->download($bill->file_path, $bill->file_name);

        } catch (\Exception $e) {
            Log::error('Download Bill Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }
}