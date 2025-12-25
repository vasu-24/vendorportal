<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractItem;
use App\Models\ManagerTag;
use App\Models\Category;
use App\Models\Organisation;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    // =====================================================
    // STATISTICS
    // =====================================================

    public function getStatistics()
    {
        try {
            $stats = [
                'total' => Contract::count(),
                'draft' => Contract::where('status', 'draft')->count(),
                'sent_for_signature' => Contract::where('status', 'sent_for_signature')->count(),
                'signed' => Contract::where('status', 'signed')->count(),
                'active' => Contract::where('status', 'active')->count(),
                'expired' => Contract::where('status', 'expired')->count(),
                'terminated' => Contract::where('status', 'terminated')->count(),
                'total_value' => Contract::sum('contract_value'),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Contract Statistics Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics'
            ], 500);
        }
    }

    // =====================================================
    // LIST ALL CONTRACTS
    // =====================================================

    public function index(Request $request)
    {
        try {
            $query = Contract::with(['vendor', 'company', 'items.category']);

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by vendor
            if ($request->has('vendor_id') && $request->vendor_id) {
                $query->where('vendor_id', $request->vendor_id);
            }

            // Search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('contract_number', 'like', "%{$search}%")
                      ->orWhere('vendor_name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%");
                });
            }

            // Order by latest
            $query->orderBy('created_at', 'desc');

            // Paginate
            $perPage = $request->get('per_page', 10);
            $contracts = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $contracts
            ]);

        } catch (\Exception $e) {
            Log::error('Contract Index Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load contracts'
            ], 500);
        }
    }

    // =====================================================
    // GET SINGLE CONTRACT
    // =====================================================

    public function show($id)
    {
        try {
            $contract = Contract::with(['vendor', 'company', 'items.category', 'invoices'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $contract
            ]);

        } catch (\Exception $e) {
            Log::error('Contract Show Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Contract not found'
            ], 404);
        }
    }

    // =====================================================
    // CREATE CONTRACT
    // =====================================================

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'template_file' => 'nullable|string',
                'company_id' => 'nullable|exists:organisations,id',
                'vendor_id' => 'required|exists:vendors,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'contract_value' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.category_id' => 'required|exists:categories,id',
                'items.*.quantity' => 'required|numeric|min:0',
                'items.*.unit' => 'required|string',
                'items.*.rate' => 'required|numeric|min:0',
                'items.*.tags' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Get company details
            $company = null;
            if ($request->company_id) {
                $company = Organisation::find($request->company_id);
            }

            // Get vendor details
            $vendor = Vendor::with(['companyInfo', 'statutoryInfo'])->find($request->vendor_id);

            // Create contract
            $contract = Contract::create([
                'contract_number' => Contract::generateContractNumber(),
                'template_file' => $request->template_file,
                'document_path' => null,
                'company_id' => $request->company_id,
                'company_name' => $company->company_name ?? null,
                'company_cin' => $company->cin ?? null,
                'company_address' => $company->address ?? null,
                'vendor_id' => $request->vendor_id,
                'vendor_name' => $vendor->companyInfo->legal_entity_name ?? $vendor->vendor_name,
                'vendor_cin' => $vendor->statutoryInfo->cin ?? null,
                'vendor_address' => $vendor->companyInfo->registered_address ?? null,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'contract_value' => $request->contract_value,
                'status' => 'draft',
                'is_visible_to_vendor' => true,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            // Create contract items with tag (stored in same table)
            foreach ($request->items as $item) {
                // Get tag info (single tag)
                $tagId = $item['tags'][0] ?? null;
                $tagName = null;
                
                if ($tagId) {
                    $managerTag = ManagerTag::where('tag_id', $tagId)->first();
                    $tagName = $managerTag->tag_name ?? null;
                }

                ContractItem::create([
                    'contract_id' => $contract->id,
                    'category_id' => $item['category_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'rate' => $item['rate'],
                    'tag_id' => $tagId,
                    'tag_name' => $tagName,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contract created successfully',
                'data' => $contract->load(['items.category'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Contract Store Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create contract',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // UPDATE CONTRACT
    // =====================================================

    public function update(Request $request, $id)
    {
        try {
            $contract = Contract::findOrFail($id);

            // Only draft contracts can be edited
            if ($contract->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft contracts can be edited'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'template_file' => 'nullable|string',
                'company_id' => 'nullable|exists:organisations,id',
                'vendor_id' => 'required|exists:vendors,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'contract_value' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.category_id' => 'required|exists:categories,id',
                'items.*.quantity' => 'required|numeric|min:0',
                'items.*.unit' => 'required|string',
                'items.*.rate' => 'required|numeric|min:0',
                'items.*.tags' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Get company details
            $company = null;
            if ($request->company_id) {
                $company = Organisation::find($request->company_id);
            }

            // Get vendor details
            $vendor = Vendor::with(['companyInfo', 'statutoryInfo'])->find($request->vendor_id);

            // Update contract
            $contract->update([
                'template_file' => $request->template_file,
                'company_id' => $request->company_id,
                'company_name' => $company->company_name ?? null,
                'company_cin' => $company->cin ?? null,
                'company_address' => $company->address ?? null,
                'vendor_id' => $request->vendor_id,
                'vendor_name' => $vendor->companyInfo->legal_entity_name ?? $vendor->vendor_name,
                'vendor_cin' => $vendor->statutoryInfo->cin ?? null,
                'vendor_address' => $vendor->companyInfo->registered_address ?? null,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'contract_value' => $request->contract_value,
                'notes' => $request->notes,
            ]);

            // Delete existing items
            $contract->items()->delete();

            // Create new contract items with tag
            foreach ($request->items as $item) {
                // Get tag info (single tag)
                $tagId = $item['tags'][0] ?? null;
                $tagName = null;
                
                if ($tagId) {
                    $managerTag = ManagerTag::where('tag_id', $tagId)->first();
                    $tagName = $managerTag->tag_name ?? null;
                }

                ContractItem::create([
                    'contract_id' => $contract->id,
                    'category_id' => $item['category_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'rate' => $item['rate'],
                    'tag_id' => $tagId,
                    'tag_name' => $tagName,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contract updated successfully',
                'data' => $contract->load(['items.category'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Contract Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update contract',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // DELETE CONTRACT
    // =====================================================

    public function destroy($id)
    {
        try {
            $contract = Contract::findOrFail($id);

            // Only draft contracts can be deleted
            if ($contract->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft contracts can be deleted'
                ], 400);
            }

            // Check if any invoices exist
            if ($contract->invoices()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete contract with existing invoices'
                ], 400);
            }

            // Delete document file if exists
            if ($contract->document_path && Storage::disk('public')->exists($contract->document_path)) {
                Storage::disk('public')->delete($contract->document_path);
            }

            $contract->items()->delete();
            $contract->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contract deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Contract Delete Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete contract'
            ], 500);
        }
    }

    // =====================================================
    // UPDATE STATUS
    // =====================================================

    public function updateStatus(Request $request, $id)
    {
        try {
            $contract = Contract::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:draft,sent_for_signature,signed,active,expired,terminated,inactive',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status'
                ], 422);
            }

            $contract->status = $request->status;
            $contract->is_visible_to_vendor = true;
            $contract->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $contract
            ]);

        } catch (\Exception $e) {
            Log::error('Contract Status Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    // =====================================================
    // UPLOAD CONTRACT DOCUMENT
    // =====================================================

    public function uploadDocument(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'contract_id' => 'required|exists:contracts,id',
                'contract_file' => 'required|file|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('contract_file');
            $extension = strtolower($file->getClientOriginalExtension());
            
            $allowedExtensions = ['doc', 'docx', 'pdf'];
            if (!in_array($extension, $allowedExtensions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please upload a Word (.doc, .docx) or PDF file.'
                ], 422);
            }

            $contract = Contract::findOrFail($request->contract_id);
            
            if ($contract->document_path && Storage::disk('public')->exists($contract->document_path)) {
                Storage::disk('public')->delete($contract->document_path);
            }
            
            $fileName = $contract->contract_number . '_' . time() . '.' . $extension;
            $filePath = $file->storeAs('contracts/documents', $fileName, 'public');
            
            $contract->update([
                'document_path' => $filePath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => [
                    'file_name' => $file->getClientOriginalName(),
                    'path' => $filePath,
                    'preview_url' => asset('storage/' . $filePath),
                    'contract' => $contract->fresh()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Contract Upload Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload: ' . $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // GET CONTRACT ITEMS WITH TAGS (For Invoice Comparison)
    // =====================================================

    public function getContractItemsWithTags($id)
    {
        try {
            $contract = Contract::with(['items.category'])->findOrFail($id);

            $items = $contract->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'category_id' => $item->category_id,
                    'category_name' => $item->category->name ?? '',
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'rate' => $item->rate,
                    'tag_id' => $item->tag_id,
                    'tag_name' => $item->tag_name,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'contract' => [
                        'id' => $contract->id,
                        'contract_number' => $contract->contract_number,
                        'contract_value' => $contract->contract_value,
                        'vendor_name' => $contract->vendor_name,
                    ],
                    'items' => $items,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Contract Items Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Contract not found'
            ], 404);
        }
    }

    // =====================================================
    // GET REPORTING TAGS (Only Manager-Linked Tags)
    // =====================================================

    public function getReportingTags()
    {
        try {
            $tags = ManagerTag::select('tag_id', 'tag_name')
                ->distinct()
                ->orderBy('tag_name')
                ->get()
                ->map(fn($t) => [
                    'tag_id' => $t->tag_id,
                    'tag_name' => $t->tag_name,
                ])
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => $tags
            ]);

        } catch (\Exception $e) {
            Log::error('Reporting Tags Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load tags'
            ], 500);
        }
    }

    // =====================================================
    // GET VENDORS
    // =====================================================

    public function getVendors()
    {
        try {
            $vendors = Vendor::with(['companyInfo', 'statutoryInfo'])
                ->where('approval_status', 'approved')
                ->where('registration_completed', true)
                ->orderBy('vendor_name')
                ->get()
                ->map(function ($vendor) {
                    return [
                        'id' => $vendor->id,
                        'vendor_name' => $vendor->companyInfo->legal_entity_name ?? $vendor->vendor_name,
                        'vendor_cin' => $vendor->statutoryInfo->cin ?? '',
                        'vendor_address' => $vendor->companyInfo->registered_address ?? '',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $vendors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load vendors'
            ], 500);
        }
    }

    // =====================================================
    // GET ORGANISATIONS
    // =====================================================

    public function getOrganisations()
    {
        try {
            $organisations = Organisation::orderBy('company_name')
                ->get(['id', 'company_name', 'cin', 'address']);

            return response()->json([
                'success' => true,
                'data' => $organisations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load organisations'
            ], 500);
        }
    }

    // =====================================================
    // GET CATEGORIES
    // =====================================================

    public function getCategories()
    {
        try {
            $categories = Category::where('status', 'active')
                ->orderBy('name')
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
    // GET TEMPLATES
    // =====================================================

    public function getTemplates()
    {
        try {
            $files = collect(File::files(public_path('agreements')))
                ->filter(function ($file) {
                    return in_array(strtolower($file->getExtension()), ['doc', 'docx', 'pdf']);
                })
                ->map(function ($file) {
                    return $file->getFilename();
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => $files
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load templates'
            ], 500);
        }
    }

    // =====================================================
    // GET UNITS
    // =====================================================

    public function getUnits()
    {
        $units = [
            ['value' => 'hrs', 'label' => 'Hours'],
            ['value' => 'days', 'label' => 'Days'],
            ['value' => 'months', 'label' => 'Months'],
            ['value' => 'nos', 'label' => 'Nos'],
            ['value' => 'lot', 'label' => 'Lot'],
        ];

        return response()->json([
            'success' => true,
            'data' => $units
        ]);
    }
}