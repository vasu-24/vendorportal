<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorCompanyInfo;
use App\Models\VendorContact;
use App\Models\VendorStatutoryInfo;
use App\Models\VendorBankDetail;
use App\Models\VendorTaxInfo;
use App\Models\VendorBusinessProfile;
use App\Models\VendorDocument;
use App\Models\VendorApprovalHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\VendorRejectionMail;
use App\Mail\VendorSetPasswordMail;
use App\Services\ZohoService; // 🔥 ZOHO SERVICE

class VendorApprovalController extends Controller
{
    // =====================================================
    // GET STATISTICS
    // =====================================================
    
    public function getStatistics()
    {
        try {
            $stats = [
                'total' => Vendor::where('registration_completed', true)->count(),
                'pending_approval' => Vendor::where('approval_status', 'pending_approval')->count(),
                'approved' => Vendor::where('approval_status', 'approved')->count(),
                'rejected' => Vendor::where('approval_status', 'rejected')->count(),
                'revision_requested' => Vendor::where('approval_status', 'revision_requested')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Get Statistics Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    // =====================================================
    // GET PENDING VENDORS
    // =====================================================
    
    public function getPendingVendors(Request $request)
    {
        try {
            $vendors = Vendor::with(['companyInfo', 'contact'])
                ->where('registration_completed', true)
                ->where('approval_status', 'pending_approval')
                ->orderBy('registration_completed_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $vendors,
                'count' => $vendors->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Get Pending Vendors Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    // =====================================================
    // GET VENDORS BY STATUS
    // =====================================================
    
    public function getVendorsByStatus(Request $request, $status)
    {
        try {
            $allowedStatuses = ['draft', 'pending_approval', 'approved', 'rejected', 'revision_requested'];

            if (!in_array($status, $allowedStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status.'
                ], 400);
            }

            $vendors = Vendor::with(['companyInfo', 'contact'])
                ->where('approval_status', $status)
                ->orderBy('updated_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $vendors,
                'count' => $vendors->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Get Vendors By Status Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    // =====================================================
    // GET VENDOR DETAILS
    // =====================================================
    
    public function getVendorDetails($id)
    {
        try {
            $vendor = Vendor::with([
                'companyInfo',
                'contact',
                'statutoryInfo',
                'bankDetails',
                'taxInfo',
                'businessProfile',
                'documents',
                'approvalHistory' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'approvedByUser',
                'rejectedByUser',
                'revisionRequestedByUser'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $vendor
            ]);

        } catch (\Exception $e) {
            Log::error('Get Vendor Details Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Vendor not found.'
            ], 404);
        }
    }

    // =====================================================
    // GET VENDOR HISTORY
    // =====================================================
    
    public function getVendorHistory($id)
    {
        try {
            $vendor = Vendor::findOrFail($id);

            $history = VendorApprovalHistory::where('vendor_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            Log::error('Get Vendor History Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    // =====================================================
    // 🔥 APPROVE VENDOR (WITH EMAIL + ZOHO SYNC)
    // =====================================================
    
    public function approveVendor(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $vendor = Vendor::findOrFail($id);

            if (!$vendor->registration_completed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor registration is not completed yet.'
                ], 400);
            }

            if ($vendor->approval_status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor is already approved.'
                ], 400);
            }

            DB::beginTransaction();

            $userId = Auth::id() ?? 1;
            $userName = Auth::check() ? Auth::user()->name : 'System';

            $dataSnapshot = $this->getVendorDataSnapshot($vendor);

            // Update vendor status
            $vendor->update([
                'approval_status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
                'revision_requested_by' => null,
                'revision_requested_at' => null,
                'revision_notes' => null,
            ]);

            // Create approval history
            VendorApprovalHistory::create([
                'vendor_id' => $vendor->id,
                'action' => 'approved',
                'action_by_type' => 'user',
                'action_by_id' => $userId,
                'action_by_name' => $userName,
                'notes' => $request->notes,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'data_snapshot' => $dataSnapshot,
            ]);

            DB::commit();

            // =====================================================
            // 🔥 STEP 1: SEND SET PASSWORD EMAIL TO VENDOR
            // =====================================================
            $emailSent = false;
            try {
                $setPasswordUrl = route('vendor.password.show', $vendor->token);
                
                Mail::to($vendor->vendor_email)->send(
                    new VendorSetPasswordMail(
                        $vendor->vendor_name,
                        $vendor->vendor_email,
                        $setPasswordUrl
                    )
                );
                $emailSent = true;
                
            } catch (\Exception $e) {
                Log::error('Set Password Email Error: ' . $e->getMessage());
            }

            // =====================================================
            // 🔥 STEP 2: PUSH TO ZOHO BOOKS
            // =====================================================
            $zohoSynced = false;
            try {
                $zohoService = app(ZohoService::class);
                
                // Check if Zoho is connected
                if ($zohoService->isConnected()) {
                    // Create vendor in Zoho Books
                    $zohoService->createVendor($vendor);
                    $zohoSynced = $vendor->fresh()->zoho_contact_id ? true : false;
                    
                    Log::info('Vendor pushed to Zoho Books', [
                        'vendor_id' => $vendor->id,
                        'zoho_contact_id' => $vendor->fresh()->zoho_contact_id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to push vendor to Zoho', [
                    'vendor_id' => $vendor->id,
                    'error' => $e->getMessage(),
                ]);
            }
            // =====================================================

            // Build response message
            $message = 'Vendor approved successfully.';
            if ($emailSent) {
                $message .= ' Set password email sent.';
            }
            if ($zohoSynced) {
                $message .= ' Synced to Zoho Books.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'email_sent' => $emailSent,
                'zoho_synced' => $zohoSynced,
                'data' => $vendor->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approve Vendor Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    // =====================================================
    // 🔥 MANUAL SYNC TO ZOHO (Separate Button)
    // =====================================================
    
    public function syncToZoho($id)
    {
        try {
            $vendor = Vendor::findOrFail($id);
            
            if ($vendor->approval_status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor must be approved before syncing to Zoho',
                ], 400);
            }

            $zohoService = app(ZohoService::class);
            
            if (!$zohoService->isConnected()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zoho Books is not connected. Please connect first in Settings.',
                ], 400);
            }

            // Create or update in Zoho
            if ($vendor->zoho_contact_id) {
                $zohoService->updateVendor($vendor);
                $message = 'Vendor updated in Zoho Books';
            } else {
                $zohoService->createVendor($vendor);
                $message = 'Vendor created in Zoho Books';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'zoho_contact_id' => $vendor->fresh()->zoho_contact_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Sync to Zoho Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =====================================================
    // 🔥 REJECT VENDOR (WITH EMAIL)
    // =====================================================
    
    public function rejectVendor(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'rejection_reason' => 'required|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $vendor = Vendor::findOrFail($id);

            if ($vendor->approval_status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor is already rejected.'
                ], 400);
            }

            DB::beginTransaction();

            $userId = Auth::id() ?? 1;
            $userName = Auth::check() ? Auth::user()->name : 'System';

            $dataSnapshot = $this->getVendorDataSnapshot($vendor);

            // Update vendor status
            $vendor->update([
                'approval_status' => 'rejected',
                'rejected_by' => $userId,
                'rejected_at' => now(),
                'rejection_reason' => $request->rejection_reason,
                'approved_by' => null,
                'approved_at' => null,
                'revision_requested_by' => null,
                'revision_requested_at' => null,
                'revision_notes' => null,
                'registration_completed' => false,
            ]);

            // Create history entry
            VendorApprovalHistory::create([
                'vendor_id' => $vendor->id,
                'action' => 'rejected',
                'action_by_type' => 'user',
                'action_by_id' => $userId,
                'action_by_name' => $userName,
                'notes' => $request->rejection_reason,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'data_snapshot' => $dataSnapshot,
            ]);

            DB::commit();

            // Send rejection email
            $emailSent = false;
            try {
                $correctionUrl = route('vendor.registration', $vendor->token);

                Mail::to($vendor->vendor_email)->send(
                    new VendorRejectionMail(
                        $vendor,
                        $request->rejection_reason,
                        $correctionUrl
                    )
                );
                $emailSent = true;
                
            } catch (\Exception $e) {
                Log::error('Rejection Email Error: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => $emailSent 
                    ? 'Vendor rejected successfully. Email notification sent to vendor.' 
                    : 'Vendor rejected successfully. (Email could not be sent)',
                'email_sent' => $emailSent,
                'data' => $vendor->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reject Vendor Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    // =====================================================
    // REQUEST REVISION
    // =====================================================
    
    public function requestRevision(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'revision_notes' => 'required|string|max:2000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $vendor = Vendor::findOrFail($id);

            if ($vendor->approval_status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot request revision for approved vendor.'
                ], 400);
            }

            DB::beginTransaction();

            $userId = Auth::id() ?? 1;
            $userName = Auth::check() ? Auth::user()->name : 'System';

            $dataSnapshot = $this->getVendorDataSnapshot($vendor);

            $vendor->update([
                'approval_status' => 'revision_requested',
                'revision_requested_by' => $userId,
                'revision_requested_at' => now(),
                'revision_notes' => $request->revision_notes,
                'approved_by' => null,
                'approved_at' => null,
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);

            VendorApprovalHistory::create([
                'vendor_id' => $vendor->id,
                'action' => 'revision_requested',
                'action_by_type' => 'user',
                'action_by_id' => $userId,
                'action_by_name' => $userName,
                'notes' => $request->revision_notes,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'data_snapshot' => $dataSnapshot,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Revision request sent successfully.',
                'data' => $vendor->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Request Revision Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    // =====================================================
    // UPDATE COMPANY INFO
    // =====================================================
    
    public function updateCompanyInfo(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'legal_entity_name' => 'nullable|string|max:255',
                'business_type' => 'nullable|string|max:100',
                'incorporation_date' => 'nullable|date',
                'registered_address' => 'nullable|string|max:1000',
                'corporate_address' => 'nullable|string|max:1000',
                'website' => 'nullable|max:255',
                'parent_company' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $vendor = Vendor::findOrFail($id);

            DB::beginTransaction();

            $oldData = $vendor->companyInfo ? $vendor->companyInfo->toArray() : [];

            $companyInfo = VendorCompanyInfo::updateOrCreate(
                ['vendor_id' => $vendor->id],
                $request->only([
                    'legal_entity_name',
                    'business_type',
                    'incorporation_date',
                    'registered_address',
                    'corporate_address',
                    'website',
                    'parent_company',
                ])
            );

            $newData = $companyInfo->fresh()->toArray();
            $changedFields = $this->getChangedFields($oldData, $newData);

            $userId = Auth::id() ?? 1;
            $userName = Auth::check() ? Auth::user()->name : 'System';

            if (!empty($changedFields)) {
                VendorApprovalHistory::create([
                    'vendor_id' => $vendor->id,
                    'action' => 'data_updated',
                    'action_by_type' => 'user',
                    'action_by_id' => $userId,
                    'action_by_name' => $userName,
                    'notes' => 'Company information updated',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'changed_fields' => $changedFields,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Company information updated successfully.',
                'data' => $companyInfo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Company Info Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    // =====================================================
    // UPDATE CONTACT
    // =====================================================
    
    public function updateContact(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'contact_person' => 'nullable|string|max:255',
                'designation' => 'nullable|string|max:100',
                'mobile' => 'nullable|string|max:15',
                'email' => 'nullable|email|max:255',
                'alternate_mobile' => 'nullable|string|max:15',
                'alternate_email' => 'nullable|email|max:255',
                'landline' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $vendor = Vendor::findOrFail($id);

            DB::beginTransaction();

            $oldData = $vendor->contact ? $vendor->contact->toArray() : [];

            $contact = VendorContact::updateOrCreate(
                ['vendor_id' => $vendor->id],
                $request->only([
                    'contact_person',
                    'designation',
                    'mobile',
                    'email',
                    'alternate_mobile',
                    'alternate_email',
                    'landline',
                ])
            );

            $newData = $contact->fresh()->toArray();
            $changedFields = $this->getChangedFields($oldData, $newData);

            $userId = Auth::id() ?? 1;
            $userName = Auth::check() ? Auth::user()->name : 'System';

            if (!empty($changedFields)) {
                VendorApprovalHistory::create([
                    'vendor_id' => $vendor->id,
                    'action' => 'data_updated',
                    'action_by_type' => 'user',
                    'action_by_id' => $userId,
                    'action_by_name' => $userName,
                    'notes' => 'Contact details updated',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'changed_fields' => $changedFields,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contact details updated successfully.',
                'data' => $contact
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Contact Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    // =====================================================
    // UPDATE STATUTORY INFO
    // =====================================================
    
    public function updateStatutoryInfo(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'pan_number' => 'nullable|string|max:10',
                'tan_number' => 'nullable|string|max:10',
                'gstin' => 'nullable|string|max:15',
                'cin' => 'nullable|string|max:21',
                'msme_registered' => 'nullable|string|max:10',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $vendor = Vendor::findOrFail($id);

            DB::beginTransaction();

            $oldData = $vendor->statutoryInfo ? $vendor->statutoryInfo->toArray() : [];

            $statutoryInfo = VendorStatutoryInfo::updateOrCreate(
                ['vendor_id' => $vendor->id],
                [
                    'pan_number' => strtoupper($request->pan_number),
                    'tan_number' => strtoupper($request->tan_number),
                    'gstin' => strtoupper($request->gstin),
                    'cin' => strtoupper($request->cin),
                    'msme_registered' => $request->msme_registered,
                ]
            );

            $newData = $statutoryInfo->fresh()->toArray();
            $changedFields = $this->getChangedFields($oldData, $newData);

            $userId = Auth::id() ?? 1;
            $userName = Auth::check() ? Auth::user()->name : 'System';

            if (!empty($changedFields)) {
                VendorApprovalHistory::create([
                    'vendor_id' => $vendor->id,
                    'action' => 'data_updated',
                    'action_by_type' => 'user',
                    'action_by_id' => $userId,
                    'action_by_name' => $userName,
                    'notes' => 'Statutory information updated',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'changed_fields' => $changedFields,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Statutory information updated successfully.',
                'data' => $statutoryInfo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Statutory Info Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    // =====================================================
    // UPDATE BANK DETAILS
    // =====================================================
    
    public function updateBankDetails(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'bank_name' => 'nullable|string|max:255',
                'branch_address' => 'nullable|string|max:500',
                'account_holder_name' => 'nullable|string|max:255',
                'account_number' => 'nullable|string|max:20',
                'ifsc_code' => 'nullable|string|max:11',
                'account_type' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $vendor = Vendor::findOrFail($id);

            DB::beginTransaction();

            $oldData = $vendor->bankDetails ? $vendor->bankDetails->toArray() : [];

            $bankDetails = VendorBankDetail::updateOrCreate(
                ['vendor_id' => $vendor->id],
                [
                    'bank_name' => $request->bank_name,
                    'branch_address' => $request->branch_address,
                    'account_holder_name' => $request->account_holder_name,
                    'account_number' => $request->account_number,
                    'ifsc_code' => strtoupper($request->ifsc_code),
                    'account_type' => $request->account_type,
                ]
            );

            $newData = $bankDetails->fresh()->toArray();
            $changedFields = $this->getChangedFields($oldData, $newData);

            $userId = Auth::id() ?? 1;
            $userName = Auth::check() ? Auth::user()->name : 'System';

            if (!empty($changedFields)) {
                VendorApprovalHistory::create([
                    'vendor_id' => $vendor->id,
                    'action' => 'data_updated',
                    'action_by_type' => 'user',
                    'action_by_id' => $userId,
                    'action_by_name' => $userName,
                    'notes' => 'Bank details updated',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'changed_fields' => $changedFields,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bank details updated successfully.',
                'data' => $bankDetails
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Bank Details Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    // =====================================================
    // UPDATE TAX INFO
    // =====================================================
    
    public function updateTaxInfo(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tax_residency' => 'nullable|string|max:50',
                'gst_reverse_charge' => 'nullable|string|max:10',
                'sez_status' => 'nullable|string|max:10',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $vendor = Vendor::findOrFail($id);

            DB::beginTransaction();

            $oldData = $vendor->taxInfo ? $vendor->taxInfo->toArray() : [];

            $taxInfo = VendorTaxInfo::updateOrCreate(
                ['vendor_id' => $vendor->id],
                $request->only([
                    'tax_residency',
                    'gst_reverse_charge',
                    'sez_status',
                ])
            );

            $newData = $taxInfo->fresh()->toArray();
            $changedFields = $this->getChangedFields($oldData, $newData);

            $userId = Auth::id() ?? 1;
            $userName = Auth::check() ? Auth::user()->name : 'System';

            if (!empty($changedFields)) {
                VendorApprovalHistory::create([
                    'vendor_id' => $vendor->id,
                    'action' => 'data_updated',
                    'action_by_type' => 'user',
                    'action_by_id' => $userId,
                    'action_by_name' => $userName,
                    'notes' => 'Tax information updated',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'changed_fields' => $changedFields,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tax information updated successfully.',
                'data' => $taxInfo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Tax Info Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    // =====================================================
    // UPDATE BUSINESS PROFILE
    // =====================================================
    
    public function updateBusinessProfile(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'core_activities' => 'nullable|string|max:2000',
                'employee_count' => 'nullable|string|max:50',
                'credit_period' => 'nullable|string|max:50',
                'turnover_fy1' => 'nullable|string|max:50',
                'turnover_fy2' => 'nullable|string|max:50',
                'turnover_fy3' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $vendor = Vendor::findOrFail($id);

            DB::beginTransaction();

            $oldData = $vendor->businessProfile ? $vendor->businessProfile->toArray() : [];

            $businessProfile = VendorBusinessProfile::updateOrCreate(
                ['vendor_id' => $vendor->id],
                $request->only([
                    'core_activities',
                    'employee_count',
                    'credit_period',
                    'turnover_fy1',
                    'turnover_fy2',
                    'turnover_fy3',
                ])
            );

            $newData = $businessProfile->fresh()->toArray();
            $changedFields = $this->getChangedFields($oldData, $newData);

            $userId = Auth::id() ?? 1;
            $userName = Auth::check() ? Auth::user()->name : 'System';

            if (!empty($changedFields)) {
                VendorApprovalHistory::create([
                    'vendor_id' => $vendor->id,
                    'action' => 'data_updated',
                    'action_by_type' => 'user',
                    'action_by_id' => $userId,
                    'action_by_name' => $userName,
                    'notes' => 'Business profile updated',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'changed_fields' => $changedFields,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Business profile updated successfully.',
                'data' => $businessProfile
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Business Profile Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    // =====================================================
    // HELPER FUNCTIONS
    // =====================================================
    
    private function getVendorDataSnapshot($vendor)
    {
        $vendor->load([
            'companyInfo',
            'contact',
            'statutoryInfo',
            'bankDetails',
            'taxInfo',
            'businessProfile',
        ]);

        return [
            'vendor' => $vendor->only(['vendor_name', 'vendor_email', 'status', 'approval_status']),
            'company_info' => $vendor->companyInfo ? $vendor->companyInfo->toArray() : null,
            'contact' => $vendor->contact ? $vendor->contact->toArray() : null,
            'statutory_info' => $vendor->statutoryInfo ? $vendor->statutoryInfo->toArray() : null,
            'bank_details' => $vendor->bankDetails ? $vendor->bankDetails->toArray() : null,
            'tax_info' => $vendor->taxInfo ? $vendor->taxInfo->toArray() : null,
            'business_profile' => $vendor->businessProfile ? $vendor->businessProfile->toArray() : null,
        ];
    }

    private function getChangedFields($oldData, $newData)
    {
        $changedFields = [];
        $ignoreFields = ['id', 'vendor_id', 'created_at', 'updated_at', 'deleted_at'];

        foreach ($newData as $key => $newValue) {
            if (in_array($key, $ignoreFields)) {
                continue;
            }

            $oldValue = $oldData[$key] ?? null;

            if ($oldValue !== $newValue) {
                $changedFields[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changedFields;
    }
}