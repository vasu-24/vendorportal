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



/**
 * =====================================================
 * ADD THIS METHOD TO: VendorTravelInvoiceController.php
 * =====================================================
 * 
 * This method generates a dynamic Excel template with:
 * - Pre-filled employee data (from database)
 * - Pre-filled category data (from database)
 * - Dropdowns for Travel Type and Mode
 * - Empty columns for vendor to fill
 */

// =====================================================
// DOWNLOAD EXCEL TEMPLATE
// =====================================================

public function downloadExcelTemplate()
{
    try {
        $vendor = $this->getVendor();
        
        // Get LATEST employees from database
        $employees = TravelEmployee::where('is_active', true)
            ->select('id', 'employee_code', 'employee_name', 'department', 'tag_id', 'tag_name')
            ->orderBy('employee_name', 'asc')
            ->get();
        
        if ($employees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active employees found. Please add employees first.'
            ], 400);
        }
        
     $categories = \App\Models\Category::where('is_travel_category', 1)
    ->where('status', 'active')
    ->pluck('name')
    ->toArray();

        
        if (empty($categories)) {
            $categories = ['Domestic', 'International']; // Default if no categories
        }
        
        // Expense modes
        $modes = ['Flight', 'Train', 'Cabs', 'Accommodation', 'Insurance', 'Visa', 'Other'];
        
        // Create Excel using PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // =====================================================
        // SHEET 1: DATA ENTRY
        // =====================================================
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Entry');
        
        // Header row
        $headers = [
            'A' => 'Emp Code',
            'B' => 'Employee Name',
            'C' => 'Project/Tag',
            'D' => 'Invoice No',
            'E' => 'Invoice Date',
            'F' => 'Travel Type',
            'G' => 'Location',
            'H' => 'Travel Date',
            'I' => 'Mode',
            'J' => 'Particulars',
            'K' => 'Basic',
            'L' => 'Taxes',
            'M' => 'Service',
            'N' => 'GST',
            'O' => 'TDS %',
        ];
        
        // Set headers
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . '1', $header);
        }
        
        // Header styling
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A1:O1')->applyFromArray($headerStyle);
        
        // Pre-filled columns styling (locked - grey background)
        $lockedStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E5E7EB'],
            ],
            'font' => [
                'color' => ['rgb' => '374151'],
            ],
        ];
        
        // Editable columns styling (white background, blue border)
        $editableStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ];
        
        // Fill employee data
        $row = 2;
        foreach ($employees as $employee) {
            // Pre-filled columns (A, B, C) - Employee Info
            $sheet->setCellValue('A' . $row, $employee->employee_code);
            $sheet->setCellValue('B' . $row, $employee->employee_name);
            $sheet->setCellValue('C' . $row, $employee->tag_name ?? '');
            
            // Default TDS %
            $sheet->setCellValue('O' . $row, 5);
            
            // Apply locked style to pre-filled columns
            $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray($lockedStyle);
            
            // Apply editable style to vendor input columns
            $sheet->getStyle('D' . $row . ':O' . $row)->applyFromArray($editableStyle);
            
            $row++;
        }
        
        $lastRow = $row - 1;
        
        // =====================================================
        // ADD DROPDOWNS (Data Validation)
        // =====================================================
        
        // Travel Type dropdown (Column F)
        $travelTypeValidation = $sheet->getCell('F2')->getDataValidation();
        $travelTypeValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $travelTypeValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
        $travelTypeValidation->setAllowBlank(true);
        $travelTypeValidation->setShowDropDown(true);
        $travelTypeValidation->setFormula1('"' . implode(',', $categories) . '"');
        
        // Apply to all rows
        for ($i = 2; $i <= $lastRow; $i++) {
            $sheet->getCell('F' . $i)->setDataValidation(clone $travelTypeValidation);
        }
        
        // Mode dropdown (Column I)
        $modeValidation = $sheet->getCell('I2')->getDataValidation();
        $modeValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $modeValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
        $modeValidation->setAllowBlank(true);
        $modeValidation->setShowDropDown(true);
        $modeValidation->setFormula1('"' . implode(',', $modes) . '"');
        
        // Apply to all rows
        for ($i = 2; $i <= $lastRow; $i++) {
            $sheet->getCell('I' . $i)->setDataValidation(clone $modeValidation);
        }
        
        // =====================================================
        // COLUMN WIDTHS
        // =====================================================
        $sheet->getColumnDimension('A')->setWidth(12);  // Emp Code
        $sheet->getColumnDimension('B')->setWidth(25);  // Employee Name
        $sheet->getColumnDimension('C')->setWidth(20);  // Project
        $sheet->getColumnDimension('D')->setWidth(15);  // Invoice No
        $sheet->getColumnDimension('E')->setWidth(14);  // Invoice Date
        $sheet->getColumnDimension('F')->setWidth(15);  // Travel Type
        $sheet->getColumnDimension('G')->setWidth(20);  // Location
        $sheet->getColumnDimension('H')->setWidth(14);  // Travel Date
        $sheet->getColumnDimension('I')->setWidth(15);  // Mode
        $sheet->getColumnDimension('J')->setWidth(25);  // Particulars
        $sheet->getColumnDimension('K')->setWidth(12);  // Basic
        $sheet->getColumnDimension('L')->setWidth(10);  // Taxes
        $sheet->getColumnDimension('M')->setWidth(10);  // Service
        $sheet->getColumnDimension('N')->setWidth(10);  // GST
        $sheet->getColumnDimension('O')->setWidth(8);   // TDS %
        
        // Freeze header row
        $sheet->freezePane('A2');
        
        // =====================================================
        // SHEET 2: INSTRUCTIONS
        // =====================================================
        $instructionSheet = $spreadsheet->createSheet();
        $instructionSheet->setTitle('Instructions');
        
        $instructions = [
            ['TRAVEL INVOICE BULK UPLOAD - INSTRUCTIONS'],
            [''],
            ['HOW TO FILL:'],
            ['1. Employee Code, Name, and Project are pre-filled - DO NOT MODIFY'],
            ['2. Fill ONLY the rows where you want to create invoices'],
            ['3. Empty rows (without Invoice No or Basic amount) will be skipped'],
            [''],
            ['REQUIRED FIELDS:'],
            ['- Invoice No: Unique invoice number (e.g., TRV-001)'],
            ['- Invoice Date: Date in YYYY-MM-DD format'],
            ['- Location: Travel location (e.g., Mumbai, Delhi)'],
            ['- Basic: Basic amount (required, must be > 0)'],
            [''],
            ['OPTIONAL FIELDS:'],
            ['- Travel Type: Select from dropdown (Domestic/International)'],
            ['- Travel Date: Date of travel'],
            ['- Mode: Select from dropdown (Flight, Train, etc.)'],
            ['- Particulars: Description of expense'],
            ['- Taxes, Service, GST: Additional charges'],
            ['- TDS %: Default is 5%, change if needed'],
            [''],
            ['NOTES:'],
            ['- Each row = 1 Employee = 1 Invoice'],
            ['- Invoice will be assigned to correct manager based on employee\'s project'],
            ['- Download fresh template if employees or projects have changed'],
            [''],
            ['Generated on: ' . now()->format('d-M-Y H:i:s')],
        ];
        
        foreach ($instructions as $idx => $line) {
            $instructionSheet->setCellValue('A' . ($idx + 1), $line[0] ?? '');
        }
        
        // Style instruction header
        $instructionSheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E40AF']],
        ]);
        $instructionSheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true],
        ]);
        $instructionSheet->getStyle('A8')->applyFromArray([
            'font' => ['bold' => true],
        ]);
        $instructionSheet->getStyle('A16')->applyFromArray([
            'font' => ['bold' => true],
        ]);
        $instructionSheet->getStyle('A22')->applyFromArray([
            'font' => ['bold' => true],
        ]);
        
        $instructionSheet->getColumnDimension('A')->setWidth(70);
        
        // =====================================================
        // SHEET 3: EMPLOYEE MASTER (Reference)
        // =====================================================
        $empMasterSheet = $spreadsheet->createSheet();
        $empMasterSheet->setTitle('Employee Master');
        
        // Headers
        $empMasterSheet->setCellValue('A1', 'Emp Code');
        $empMasterSheet->setCellValue('B1', 'Employee Name');
        $empMasterSheet->setCellValue('C1', 'Department');
        $empMasterSheet->setCellValue('D1', 'Project/Tag');
        
        $empMasterSheet->getStyle('A1:D1')->applyFromArray($headerStyle);
        
        // Fill data
        $row = 2;
        foreach ($employees as $employee) {
            $empMasterSheet->setCellValue('A' . $row, $employee->employee_code);
            $empMasterSheet->setCellValue('B' . $row, $employee->employee_name);
            $empMasterSheet->setCellValue('C' . $row, $employee->department ?? '');
            $empMasterSheet->setCellValue('D' . $row, $employee->tag_name ?? '');
            $row++;
        }
        
        $empMasterSheet->getColumnDimension('A')->setWidth(12);
        $empMasterSheet->getColumnDimension('B')->setWidth(25);
        $empMasterSheet->getColumnDimension('C')->setWidth(15);
        $empMasterSheet->getColumnDimension('D')->setWidth(20);
        
        // Set active sheet back to Data Entry
        $spreadsheet->setActiveSheetIndex(0);
        
        // =====================================================
        // GENERATE FILE
        // =====================================================
        $fileName = 'Travel_Invoice_Template_' . now()->format('Y-m-d_His') . '.xlsx';
        $filePath = storage_path('app/temp/' . $fileName);
        
        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filePath);
        
        // Return file download
        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
        
    } catch (\Exception $e) {
        Log::error('Download Excel Template Error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to generate template: ' . $e->getMessage()
        ], 500);
    }
}



/**
 * =====================================================
 * ADD THIS METHOD TO: VendorTravelInvoiceController.php
 * =====================================================
 * 
 * This method parses uploaded Excel and creates invoices
 * ALL LINKING HAPPENS FROM DATABASE (not from Excel)
 */

// =====================================================
// UPLOAD EXCEL & CREATE INVOICES
// =====================================================

public function uploadExcelTemplate(Request $request)
{
    try {
        $vendor = $this->getVendor();
        
        // Validate file
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file. Please upload a valid Excel file (.xlsx or .xls)',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Load Excel file
        $file = $request->file('excel_file');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getSheet(0); // First sheet (Data Entry)
        
        $rows = $sheet->toArray(null, true, true, true);
        
        // Remove header row
        $headerRow = array_shift($rows);
        
        // Validate header structure
        $expectedHeaders = ['Emp Code', 'Employee Name', 'Project/Tag', 'Invoice No', 'Invoice Date', 
                           'Travel Type', 'Location', 'Travel Date', 'Mode', 'Particulars', 
                           'Basic', 'Taxes', 'Service', 'GST', 'TDS %'];
        
        $headerValues = array_values(array_filter($headerRow));
        
        // Check if first few headers match
        if (strtolower(trim($headerRow['A'])) !== 'emp code') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid template format. Please download a fresh template.'
            ], 422);
        }
        
        // Process rows
        $created = [];
        $errors = [];
        $skipped = 0;
        $rowNum = 1; // Start from 1 (after header)
        
        DB::beginTransaction();
        
        // Create batch
        $batch = TravelBatch::create([
            'batch_number' => TravelBatch::generateBatchNumber($vendor->id),
            'vendor_id' => $vendor->id,
            'total_invoices' => 0,
            'total_amount' => 0,
            'status' => 'draft',
        ]);
        
        foreach ($rows as $row) {
            $rowNum++;
            
            $empCode = trim($row['A'] ?? '');
            $invoiceNo = trim($row['D'] ?? '');
            $basicAmount = floatval($row['K'] ?? 0);
            
            // Skip empty rows (no invoice number OR no basic amount)
            if (empty($invoiceNo) || $basicAmount <= 0) {
                $skipped++;
                continue;
            }
            
            // =====================================================
            // GET EMPLOYEE FROM DATABASE (NOT from Excel!)
            // This ensures ALL linking is from LATEST database data
            // =====================================================
            $employee = TravelEmployee::where('employee_code', $empCode)
                ->where('is_active', true)
                ->first();
            
            if (!$employee) {
                $errors[] = [
                    'row' => $rowNum,
                    'emp_code' => $empCode,
                    'invoice_no' => $invoiceNo,
                    'error' => "Employee code '{$empCode}' not found or inactive"
                ];
                continue;
            }
            
            // Check for duplicate invoice number in this batch
            $existingInvoice = TravelInvoice::where('batch_id', $batch->id)
                ->where('invoice_number', $invoiceNo)
                ->exists();
            
            if ($existingInvoice) {
                $errors[] = [
                    'row' => $rowNum,
                    'emp_code' => $empCode,
                    'invoice_no' => $invoiceNo,
                    'error' => "Duplicate invoice number '{$invoiceNo}' in this batch"
                ];
                continue;
            }
            
            // =====================================================
            // TRAVEL TYPE IS REQUIRED
            // =====================================================
            $travelType = trim($row['F'] ?? '');
            if (empty($travelType)) {
                $errors[] = [
                    'row' => $rowNum,
                    'emp_code' => $empCode,
                    'invoice_no' => $invoiceNo,
                    'error' => "Travel Type is required"
                ];
                continue;
            }
            
            
            // Get category using smart matching
            $category = $this->findCategoryByName($travelType);
            
            if (!$category) {
                $errors[] = [
                    'row' => $rowNum,
                    'emp_code' => $empCode,
                    'invoice_no' => $invoiceNo,
                    'error' => "Travel Type '{$travelType}' not found in Category Master. Please use exact category name from dropdown."
                ];
                continue;
            }
            
            // Parse dates
            $invoiceDate = $this->parseExcelDate($row['E'] ?? '');
            $travelDate = $this->parseExcelDate($row['H'] ?? '');
            
            if (!$invoiceDate) {
                $errors[] = [
                    'row' => $rowNum,
                    'emp_code' => $empCode,
                    'invoice_no' => $invoiceNo,
                    'error' => "Invalid invoice date"
                ];
                continue;
            }
            
            // =====================================================
            // CREATE INVOICE WITH ALL DB LINKS
            // =====================================================
            $invoice = TravelInvoice::create([
                'batch_id'        => $batch->id,
                'vendor_id'       => $vendor->id,
                
                // ✅ ALL FROM DATABASE (Latest data!)
                'employee_id'     => $employee->id,
                'tag_id'          => $employee->tag_id,
                'tag_name'        => $employee->tag_name,
                'project_code'    => $employee->tag_id,
                'assigned_rm_id'  => $employee->getManagerId(),
                
                // ✅ FROM EXCEL
                'invoice_number'  => $invoiceNo,
                'invoice_date'    => $invoiceDate,
                'invoice_type'    => 'tax_invoice',
                'category_id'     => $category->id, // ✅ Guaranteed to exist
                'travel_type'     => $category->name, // ✅ Use actual category name from master
                'location'        => trim($row['G'] ?? ''),
                'travel_date'     => $travelDate,
                'tds_percent'     => floatval($row['O'] ?? 5),
                'status'          => 'draft',
            ]);
            
            // =====================================================
            // CREATE EXPENSE ITEM
            // =====================================================
            $basic = floatval($row['K'] ?? 0);
            $taxes = floatval($row['L'] ?? 0);
            $service = floatval($row['M'] ?? 0);
            $gst = floatval($row['N'] ?? 0);
            $gross = $basic + $taxes + $service + $gst;
            
            TravelInvoiceItem::create([
                'travel_invoice_id' => $invoice->id,
                'mode'              => strtolower(trim($row['I'] ?? 'other')),
                'particulars'       => trim($row['J'] ?? ''),
                'basic'             => $basic,
                'taxes'             => $taxes,
                'service_charge'    => $service,
                'gst'               => $gst,
                'gross_amount'      => $gross,
            ]);
            
            // =====================================================
            // CALCULATE INVOICE TOTALS
            // =====================================================
            $tdsAmount = ($basic * $invoice->tds_percent) / 100;
            $netAmount = $gross - $tdsAmount;
            
            $invoice->update([
                'basic_total'          => $basic,
                'taxes_total'          => $taxes,
                'service_charge_total' => $service,
                'gst_total'            => $gst,
                'gross_amount'         => $gross,
                'tds_amount'           => $tdsAmount,
                'net_amount'           => $netAmount,
            ]);
            
            // =====================================================
            // HANDLE BILL FILE UPLOAD FOR THIS ROW
            // =====================================================
            if ($request->hasFile("bills.{$rowNum}")) {
                $billFile = $request->file("bills.{$rowNum}");
                
                $fileName = 'bill_' . $invoice->id . '_' . time() . '_' . uniqid() . '.' . $billFile->getClientOriginalExtension();
                $path = $billFile->storeAs('travel_invoices/' . $vendor->id . '/bills', $fileName, 'public');
                
                TravelInvoiceBill::create([
                    'travel_invoice_id' => $invoice->id,
                    'file_name' => $billFile->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $billFile->getClientOriginalExtension(),
                    'file_size' => $billFile->getSize(),
                ]);
            }
            
            $created[] = [
                'id' => $invoice->id,
                'row' => $rowNum,
                'invoice_no' => $invoiceNo,
                'employee' => $employee->employee_name,
                'amount' => $gross,
            ];
        }
        
        // Update batch totals
        $batch->updateTotals();
        
        // If no invoices created, rollback
        if (count($created) === 0) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'No invoices created. Please check the errors.',
                'data' => [
                    'created_count' => 0,
                    'error_count' => count($errors),
                    'skipped_count' => $skipped,
                    'errors' => $errors,
                ]
            ], 422);
        }
        
        DB::commit();
        
        Log::info('Excel Upload Successful', [
            'vendor_id' => $vendor->id,
            'batch_id' => $batch->id,
            'created' => count($created),
            'errors' => count($errors),
            'skipped' => $skipped,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => count($created) . ' invoice(s) created successfully!',
            'data' => [
                'batch' => $batch->fresh(),
                'created_count' => count($created),
                'error_count' => count($errors),
                'skipped_count' => $skipped,
                'created' => $created,
                'errors' => $errors,
            ]
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Upload Excel Error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to process Excel: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Parse Excel date (handles various formats)
 */
private function parseExcelDate($value)
{
    if (empty($value)) {
        return null;
    }
    
    // If it's already a date object
    if ($value instanceof \DateTime) {
        return $value->format('Y-m-d');
    }
    
    // If it's an Excel serial date number
    if (is_numeric($value)) {
        try {
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
    
    // If it's a string date
    $value = trim($value);
    
    // Try various formats
    $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'm/d/Y', 'Y/m/d', 'd-M-Y', 'd M Y'];
    
    foreach ($formats as $format) {
        $date = \DateTime::createFromFormat($format, $value);
        if ($date && $date->format($format) === $value) {
            return $date->format('Y-m-d');
        }
    }
    
    // Try strtotime as last resort
    $timestamp = strtotime($value);
    if ($timestamp) {
        return date('Y-m-d', $timestamp);
    }
    
    return null;
}


// =====================================================
// PREVIEW EXCEL (Without Creating)
// =====================================================

public function previewExcelTemplate(Request $request)
{
    try {
        $vendor = $this->getVendor();
        
        // Validate file
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Load Excel file
        $file = $request->file('excel_file');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getSheet(0);
        
        $rows = $sheet->toArray(null, true, true, true);
        array_shift($rows); // Remove header
        
        $preview = [];
        $valid = 0;
        $invalid = 0;
        $skipped = 0;
        $rowNum = 1;
        
        foreach ($rows as $row) {
            $rowNum++;
            
            $empCode = trim($row['A'] ?? '');
            $invoiceNo = trim($row['D'] ?? '');
            $basicAmount = floatval($row['K'] ?? 0);
            
            // Skip empty rows
            if (empty($invoiceNo) || $basicAmount <= 0) {
                $skipped++;
                continue;
            }
            
            // Check employee exists
            $employee = TravelEmployee::where('employee_code', $empCode)
                ->where('is_active', true)
                ->first();
            
            $basic = floatval($row['K'] ?? 0);
            $taxes = floatval($row['L'] ?? 0);
            $service = floatval($row['M'] ?? 0);
            $gst = floatval($row['N'] ?? 0);
            $gross = $basic + $taxes + $service + $gst;
            
            // Get travel type
            $travelType = trim($row['F'] ?? '');
            
            $status = 'valid';
            $error = null;
            
            if (!$employee) {
                $status = 'invalid';
                $error = "Employee not found";
                $invalid++;
            } elseif (empty($travelType)) {
                $status = 'invalid';
                $error = "Travel Type is required";
                $invalid++;
            } else {
                $valid++;
            }
            
            $preview[] = [
                'row' => $rowNum,
                'emp_code' => $empCode,
                'employee_name' => $employee?->employee_name ?? $row['B'] ?? '-',
                'project' => $employee?->tag_name ?? $row['C'] ?? '-',
                'invoice_no' => $invoiceNo,
                'invoice_date' => $row['E'] ?? '',
                'travel_type' => $travelType,
                'location' => $row['G'] ?? '',
                'mode' => $row['I'] ?? '',
                'amount' => $gross,
                'status' => $status,
                'error' => $error,
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'preview' => $preview,
                'summary' => [
                    'total_rows' => count($preview),
                    'valid' => $valid,
                    'invalid' => $invalid,
                    'skipped' => $skipped,
                ]
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Preview Excel Error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to preview Excel: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Smart category matching - handles variations
     * Matches: "dom", "domestic", "Domestic", "DOMESTIC", "domestic travel", etc.
     */
    private function findCategoryByName($inputName)
    {
        if (empty($inputName)) {
            return null;
        }
        
        // Normalize input
        $normalized = strtolower(trim($inputName));
        
        // Get all travel categories
        $categories = \App\Models\Category::where('is_travel_category', 1)
            ->where('status', 'active')
            ->get();
        
        // Priority 1: Exact match (case insensitive)
        foreach ($categories as $category) {
            if (strtolower($category->name) === $normalized) {
                return $category;
            }
        }
        
        // Priority 2: Common variations mapping
        $variations = [
            'dom' => ['domestic'],
            'domestic' => ['domestic'],
            'int' => ['international'],
            'intl' => ['international'],
            'international' => ['international'],
        ];
        
        foreach ($variations as $pattern => $searchTerms) {
            if (str_contains($normalized, $pattern)) {
                foreach ($categories as $category) {
                    $catLower = strtolower($category->name);
                    foreach ($searchTerms as $term) {
                        if (str_contains($catLower, $term)) {
                            return $category;
                        }
                    }
                }
            }
        }
        
        // Priority 3: Partial match (contains)
        foreach ($categories as $category) {
            $catLower = strtolower($category->name);
            // Check if category name contains input OR input contains category name
            if (str_contains($catLower, $normalized) || str_contains($normalized, $catLower)) {
                return $category;
            }
        }
        
        return null;
    }

}