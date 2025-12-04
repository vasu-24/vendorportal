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

class VendorRegistrationController extends Controller
{
    /**
     * 🔥 Show the registration wizard (UPDATED)
     */
    public function showWizard($token)
    {
        // Load vendor WITH all relationships for pre-filling
        $vendor = Vendor::with([
            'companyInfo',
            'contact',
            'statutoryInfo',
            'bankDetails',
            'taxInfo',
            'businessProfile',
            'documents'
        ])->where('token', $token)->firstOrFail();

        // Allow access if:
        // 1. Vendor accepted the invitation (status = accepted)
        // 2. OR Vendor was rejected and needs to correct (approval_status = rejected)
        // 3. OR Revision was requested (approval_status = revision_requested)
        
        $canAccess = ($vendor->status === 'accepted') || 
                     in_array($vendor->approval_status, ['rejected', 'revision_requested']);

        if (!$canAccess) {
            abort(403, 'Unauthorized access. Please accept the invitation first.');
        }

        // Don't redirect to success if rejected - let them edit
        if ($vendor->registration_completed && !in_array($vendor->approval_status, ['rejected', 'revision_requested'])) {
            return redirect()->route('vendor.registration.success', $token);
        }

        return view('pages.vendors.wizard.main', compact('vendor'));
    }

    /**
     * Show registration success page
     */
    public function showSuccess($token)
    {
        $vendor = Vendor::where('token', $token)->firstOrFail();

        if (!$vendor->registration_completed) {
            return redirect()->route('vendor.registration', $token);
        }

        return view('pages.vendors.wizard.success', compact('vendor'));
    }

    /**
     * Get vendor by token (UPDATED)
     */
    private function getVendor($token)
    {
        $vendor = Vendor::where('token', $token)->first();

        if (!$vendor) {
            return null;
        }

        // 🔥 Allow if accepted OR rejected (for corrections)
        $canAccess = ($vendor->status === 'accepted') || 
                     in_array($vendor->approval_status, ['rejected', 'revision_requested']);

        if (!$canAccess) {
            return null;
        }

        return $vendor;
    }

    /**
     * Upload file helper
     */
    private function uploadFile($file, $folder = 'vendor_documents')
    {
        if (!$file) {
            return null;
        }

        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename, 'public');

        return $path;
    }

    /**
     * Delete old file helper
     */
    private function deleteOldFile($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Step 1: Save Company Info + Contact Details
     */
    public function saveStep1(Request $request, $token)
    {
        try {
            $vendor = $this->getVendor($token);

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or unauthorized vendor token.'
                ], 403);
            }

            // Validation - All fields optional now
            $validator = Validator::make($request->all(), [
                'legal_entity_name' => 'nullable|string|max:255',
                'business_type' => 'nullable|string|max:100',
                'incorporation_date' => 'nullable|date',
                'registered_address' => 'nullable|string|max:1000',
                'corporate_address' => 'nullable|string|max:1000',
                'website' => 'nullable|max:255',
                'parent_company' => 'nullable|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'designation' => 'nullable|string|max:100',
                'mobile' => 'nullable|string|max:15',
                'contact_email' => 'nullable|email|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Save Company Info
            VendorCompanyInfo::updateOrCreate(
                ['vendor_id' => $vendor->id],
                [
                    'legal_entity_name' => $request->legal_entity_name,
                    'business_type' => $request->business_type,
                    'incorporation_date' => $request->incorporation_date,
                    'registered_address' => $request->registered_address,
                    'corporate_address' => $request->corporate_address,
                    'website' => $request->website,
                    'parent_company' => $request->parent_company,
                ]
            );

            // Save Contact Details
            VendorContact::updateOrCreate(
                ['vendor_id' => $vendor->id],
                [
                    'contact_person' => $request->contact_person,
                    'designation' => $request->designation,
                    'mobile' => $request->mobile,
                    'email' => $request->contact_email,
                ]
            );

            // Update vendor current step
            $vendor->update(['current_step' => 1]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Company and contact information saved successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vendor Registration Step 1 Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Step 2: Save Statutory Info + Banking Details
     */
    public function saveStep2(Request $request, $token)
    {
        try {
            $vendor = $this->getVendor($token);

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or unauthorized vendor token.'
                ], 403);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'pan_number' => 'nullable|string|max:10',
                'tan_number' => 'nullable|string|max:10',
                'gstin' => 'nullable|string|max:15',
                'cin' => 'nullable|string|max:21',
                'msme_registered' => 'nullable|string|max:10',
                'udyam_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'bank_name' => 'nullable|string|max:255',
                'branch_address' => 'nullable|string|max:500',
                'account_holder_name' => 'nullable|string|max:255',
                'account_number' => 'nullable|string|max:20',
                'ifsc_code' => 'nullable|string|max:11',
                'account_type' => 'nullable|string|max:50',
                'cancelled_cheque' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Get existing statutory info for file handling
            $existingStatutory = VendorStatutoryInfo::where('vendor_id', $vendor->id)->first();

            // Handle Udyam Certificate Upload
            $udyamPath = $existingStatutory->udyam_certificate_path ?? null;
            if ($request->hasFile('udyam_certificate')) {
                $this->deleteOldFile($udyamPath);
                $udyamPath = $this->uploadFile($request->file('udyam_certificate'), 'vendor_documents/udyam');
            }

            // Save Statutory Info
            VendorStatutoryInfo::updateOrCreate(
                ['vendor_id' => $vendor->id],
                [
                    'pan_number' => strtoupper($request->pan_number),
                    'tan_number' => strtoupper($request->tan_number),
                    'gstin' => strtoupper($request->gstin),
                    'cin' => strtoupper($request->cin),
                    'msme_registered' => $request->msme_registered,
                    'udyam_certificate_path' => $udyamPath,
                ]
            );

            // Get existing bank details for file handling
            $existingBank = VendorBankDetail::where('vendor_id', $vendor->id)->first();

            // Handle Cancelled Cheque Upload
            $chequePath = $existingBank->cancelled_cheque_path ?? null;
            if ($request->hasFile('cancelled_cheque')) {
                $this->deleteOldFile($chequePath);
                $chequePath = $this->uploadFile($request->file('cancelled_cheque'), 'vendor_documents/cheques');
            }

            // Save Bank Details
            VendorBankDetail::updateOrCreate(
                ['vendor_id' => $vendor->id],
                [
                    'bank_name' => $request->bank_name,
                    'branch_address' => $request->branch_address,
                    'account_holder_name' => $request->account_holder_name,
                    'account_number' => $request->account_number,
                    'ifsc_code' => strtoupper($request->ifsc_code),
                    'account_type' => $request->account_type,
                    'cancelled_cheque_path' => $chequePath,
                ]
            );

            // Update vendor current step
            $vendor->update(['current_step' => 2]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Statutory and banking information saved successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vendor Registration Step 2 Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Step 3: Save Tax Info + Business Profile
     */
    public function saveStep3(Request $request, $token)
    {
        try {
            $vendor = $this->getVendor($token);

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or unauthorized vendor token.'
                ], 403);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'tax_residency' => 'nullable|string|max:50',
                'gst_reverse_charge' => 'nullable|string|max:10',
                'sez_status' => 'nullable|string|max:10',
                'tds_exemption_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
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

            DB::beginTransaction();

            // Get existing tax info for file handling
            $existingTax = VendorTaxInfo::where('vendor_id', $vendor->id)->first();

            // Handle TDS Exemption Certificate Upload
            $tdsPath = $existingTax->tds_exemption_path ?? null;
            if ($request->hasFile('tds_exemption_certificate')) {
                $this->deleteOldFile($tdsPath);
                $tdsPath = $this->uploadFile($request->file('tds_exemption_certificate'), 'vendor_documents/tds');
            }

            // Save Tax Info
            VendorTaxInfo::updateOrCreate(
                ['vendor_id' => $vendor->id],
                [
                    'tax_residency' => $request->tax_residency,
                    'gst_reverse_charge' => $request->gst_reverse_charge,
                    'sez_status' => $request->sez_status,
                    'tds_exemption_path' => $tdsPath,
                ]
            );

            // Save Business Profile
            VendorBusinessProfile::updateOrCreate(
                ['vendor_id' => $vendor->id],
                [
                    'core_activities' => $request->core_activities,
                    'employee_count' => $request->employee_count,
                    'credit_period' => $request->credit_period,
                    'turnover_fy1' => $request->turnover_fy1,
                    'turnover_fy2' => $request->turnover_fy2,
                    'turnover_fy3' => $request->turnover_fy3,
                ]
            );

            // Update vendor current step
            $vendor->update(['current_step' => 3]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tax and business profile saved successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vendor Registration Step 3 Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Step 4: Save KYC Documents + Complete Registration (UPDATED)
     */
    public function saveStep4(Request $request, $token)
    {
        try {
            $vendor = $this->getVendor($token);

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or unauthorized vendor token.'
                ], 403);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'doc_pan_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'doc_gst_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'doc_incorporation_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'doc_moa_aoa' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'doc_msme_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'doc_other' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'declaration_accurate' => 'nullable|string',
                'declaration_authorized' => 'nullable|string',
                'declaration_terms' => 'nullable|string',
                'digital_signature' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Document types mapping
            $documentTypes = [
                'doc_pan_card' => 'PAN Card',
                'doc_gst_certificate' => 'GST Certificate',
                'doc_incorporation_certificate' => 'Certificate of Incorporation',
                'doc_moa_aoa' => 'MOA/AOA/Partnership Deed',
                'doc_msme_certificate' => 'MSME Certificate',
                'doc_other' => 'Other Document',
            ];

            // Upload each document
            foreach ($documentTypes as $inputName => $documentType) {
                if ($request->hasFile($inputName)) {
                    // Delete old document if exists
                    $existingDoc = VendorDocument::where('vendor_id', $vendor->id)
                        ->where('document_type', $documentType)
                        ->first();

                    if ($existingDoc) {
                        $this->deleteOldFile($existingDoc->document_path);
                        $existingDoc->delete();
                    }

                    // Upload new document
                    $path = $this->uploadFile($request->file($inputName), 'vendor_documents/kyc');

                    // Save document record
                    VendorDocument::create([
                        'vendor_id' => $vendor->id,
                        'document_type' => $documentType,
                        'document_path' => $path,
                        'original_name' => $request->file($inputName)->getClientOriginalName(),
                        'file_size' => $request->file($inputName)->getSize(),
                    ]);
                }
            }

            // 🔥 Check if this is a resubmission (vendor was rejected)
            $isResubmission = in_array($vendor->approval_status, ['rejected', 'revision_requested']);
            $historyAction = $isResubmission ? 'resubmitted' : 'submitted';
            $historyNotes = $isResubmission ? 'Vendor resubmitted registration after corrections' : 'Vendor submitted registration form';

            // Update vendor as registration completed + set pending_approval
            $vendor->update([
                'current_step' => 4,
                'registration_completed' => true,
                'registration_completed_at' => now(),
                'approval_status' => 'pending_approval',
                'digital_signature' => $request->digital_signature,
                'declaration_accurate' => $request->declaration_accurate ? true : false,
                'declaration_authorized' => $request->declaration_authorized ? true : false,
                'declaration_terms' => $request->declaration_terms ? true : false,
                // 🔥 Clear rejection fields on resubmission
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
                'revision_requested_by' => null,
                'revision_requested_at' => null,
                'revision_notes' => null,
            ]);

            // Add history entry for submission/resubmission
            VendorApprovalHistory::create([
                'vendor_id' => $vendor->id,
                'action' => $historyAction,
                'action_by_type' => 'vendor',
                'action_by_id' => $vendor->id,
                'action_by_name' => $vendor->vendor_name,
                'notes' => $historyNotes,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registration completed successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vendor Registration Step 4 Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Get registration data (for review/edit)
     */
    public function getRegistrationData($token)
    {
        try {
            $vendor = $this->getVendor($token);

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or unauthorized vendor token.'
                ], 403);
            }

            $data = [
                'vendor' => $vendor,
                'company_info' => VendorCompanyInfo::where('vendor_id', $vendor->id)->first(),
                'contact' => VendorContact::where('vendor_id', $vendor->id)->first(),
                'statutory_info' => VendorStatutoryInfo::where('vendor_id', $vendor->id)->first(),
                'bank_details' => VendorBankDetail::where('vendor_id', $vendor->id)->first(),
                'tax_info' => VendorTaxInfo::where('vendor_id', $vendor->id)->first(),
                'business_profile' => VendorBusinessProfile::where('vendor_id', $vendor->id)->first(),
                'documents' => VendorDocument::where('vendor_id', $vendor->id)->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Get Registration Data Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }
}