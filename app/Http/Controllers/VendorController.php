<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\MailTemplate;
use App\Services\VendorImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VendorMail;

class VendorController extends Controller
{
    // Show create vendor form
    public function create()
    {
        $templates = MailTemplate::where('status', 'active')->get();
        return view('pages.vendors.create', compact('templates'));
    }

    // Store new vendor
public function store(Request $request)
{
    $request->validate([
        'vendor_name' => 'required|string|max:255|unique:vendors,vendor_name',
        'vendor_email' => 'required|email|max:255',
        'template_id' => 'nullable|exists:mail_templates,id'
    ], [
        'vendor_name.unique' => 'A vendor with this name already exists!',
    ]);

    $vendor = Vendor::create([
        'vendor_name' => $request->vendor_name,
        'vendor_email' => $request->vendor_email,
        'template_id' => $request->template_id,
        'status' => 'pending',
        'token' => Vendor::generateToken(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Vendor created successfully!',
        'vendor' => $vendor
    ]);
}


    // Update template
    public function updateTemplate(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);
        
        $vendor->template_id = $request->template_id;
        $vendor->save();
        
        $vendor->load('template');
        
        return response()->json([
            'message' => 'Template updated successfully',
            'template' => [
                'id' => $vendor->template->id,
                'name' => $vendor->template->name,
                'subject' => $vendor->template->subject,
                'body' => $vendor->template->body
            ]
        ]);
    }

    // List all vendors
    public function index()
    {
        $vendors = Vendor::with('template')->orderBy('created_at', 'desc')->get();
        $templates = MailTemplate::where('status', 'active')->get();
        return view('pages.vendors.index', compact('vendors', 'templates'));
    }

 public function sendEmail(Request $request, $id)
{
    $vendor = Vendor::with('template')->findOrFail($id);

    if (!$vendor->template) {
        return response()->json([
            'success' => false,
            'message' => 'No template selected for this vendor!'
        ], 400);
    }

    $templateBody = $vendor->template->body;
    $templateSubject = $vendor->template->subject;

    $acceptUrl = route('vendors.accept', $vendor->token);
    $rejectUrl = route('vendors.reject', $vendor->token);

    // ✅ FIX: Add BOTH single { } and double {{ }} placeholders!
    $placeholders = [
        // Double curly braces (what your template uses)
        '{{vendor_name}}' => $vendor->vendor_name,
        '{{vendor_email}}' => $vendor->vendor_email,
        '{{registration_url}}' => $acceptUrl,
        '{{accept_url}}' => $acceptUrl,
        '{{reject_url}}' => $rejectUrl,
        '{{portal_url}}' => url('/'),
        '{{current_date}}' => now()->format('d-M-Y'),
        '{{year}}' => date('Y'),
        '{{company_name}}' => 'FIDE',
        
        // Single curly braces (for backward compatibility)
        '{vendor_name}' => $vendor->vendor_name,
        '{vendor_email}' => $vendor->vendor_email,
        '{registration_url}' => $acceptUrl,
        '{portal_url}' => url('/'),
        '{current_date}' => now()->format('d-M-Y'),
        '{year}' => date('Y'),
        '{company_name}' => 'FIDE',
    ];

    foreach ($placeholders as $placeholder => $value) {
        $templateBody = str_replace($placeholder, $value, $templateBody);
        $templateSubject = str_replace($placeholder, $value, $templateSubject);
    }

    try {
      Mail::to($vendor->vendor_email)->send(
    new VendorMail(
        $templateSubject,
        $templateBody,
        $acceptUrl,
        $rejectUrl,
        $vendor->vendor_name,
        $templateBody,
        $vendor->vendor_email  // ← ADD THIS LINE (with comma above!)
    )
);
        $vendor->update(['email_sent_at' => now()]);
        
        return response()->json([
            'success' => true,
            'message' => 'Email sent successfully to ' . $vendor->vendor_email
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to send email: ' . $e->getMessage()
        ], 500);
    }
}


    // =====================================================
    // 🔥 IMPORT VENDORS
    // =====================================================
    
    /**
     * Download import template
     */
    public function downloadImportTemplate()
    {
        $templatePath = storage_path('app/templates/vendor_import_template.xlsx');
        
        if (!file_exists($templatePath)) {
            abort(404, 'Template file not found');
        }

        return response()->download($templatePath, 'vendor_import_template.xlsx');
    }

   /**
 * Import vendors from Excel
 */
public function import(Request $request, VendorImportService $importService)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls|max:5120', // Max 5MB
    ]);

    try {
        $file = $request->file('file');
        
        // Use getRealPath() - directly use the temp uploaded file
        $fullPath = $file->getRealPath();

        if (!$fullPath || !file_exists($fullPath)) {
            throw new \Exception('Failed to save uploaded file');
        }

        $result = $importService->import($fullPath);

        return response()->json($result);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Import failed: ' . $e->getMessage(),
        ], 500);
    }
}



    // =====================================================
    // 🔥 HANDLE VENDOR ACCEPTANCE
    // =====================================================
    public function accept($token)
    {
        $vendor = Vendor::where('token', $token)->firstOrFail();

        // CHECK 1: Already submitted the form
        if ($vendor->registration_completed && !in_array($vendor->approval_status, ['rejected', 'revision_requested'])) {
            return view('pages.vendors.response', [
                'message' => 'You have already submitted your registration. Please wait for approval.',
                'type' => 'already_submitted'
            ]);
        }

        // CHECK 2: Vendor rejected the invitation (clicked reject button)
        if ($vendor->status === 'rejected') {
            return view('pages.vendors.response', [
                'message' => 'You have declined this invitation.',
                'type' => 'warning'
            ]);
        }

        // If vendor was rejected by admin, allow them to access form for corrections
        if (in_array($vendor->approval_status, ['rejected', 'revision_requested'])) {
            return redirect()->route('vendor.registration', $vendor->token);
        }

        // For pending vendors - just show the form
        if (in_array($vendor->status, ['pending', 'accepted'])) {
            // Update to 'accepted' to indicate they clicked the link
            if ($vendor->status === 'pending') {
                $vendor->update([
                    'status' => 'accepted',
                ]);
            }
            
            return redirect()->route('vendor.registration', $vendor->token);
        }

        // Fallback
        return view('pages.vendors.response', [
            'message' => 'This link is no longer valid.',
            'type' => 'warning'
        ]);
    }

    // Handle vendor rejection (from initial email - vendor declines invitation)
    public function reject($token)
    {
        $vendor = Vendor::where('token', $token)->firstOrFail();

        if ($vendor->status !== 'pending') {
            return view('pages.vendors.response', [
                'message' => 'This link has already been used.',
                'type' => 'warning'
            ]);
        }

        $vendor->update([
            'status' => 'rejected',
            'responded_at' => now()
        ]);

        return view('pages.vendors.response', [
            'message' => 'Your response has been recorded. Thank you.',
            'type' => 'info',
            'vendor' => $vendor
        ]);
    }

    // =====================================================
    // 🔥 SHOW WIZARD FORM
    // =====================================================
    public function showWizard($token)
    {
        $vendor = Vendor::with([
            'companyInfo',
            'contact',
            'statutoryInfo',
            'bankDetails',
            'taxInfo',
            'businessProfile',
            'documents'
        ])->where('token', $token)->firstOrFail();

        // Check if already submitted (and not rejected)
        if ($vendor->registration_completed && !in_array($vendor->approval_status, ['rejected', 'revision_requested'])) {
            return view('pages.vendors.response', [
                'message' => 'You have already submitted your registration.',
                'type' => 'already_submitted'
            ]);
        }

        // Check if vendor can access wizard
        $canAccess = in_array($vendor->status, ['pending', 'accepted']) || 
                     in_array($vendor->approval_status, ['rejected', 'revision_requested', 'draft', null]);

        if (!$canAccess) {
            return view('pages.vendors.response', [
                'message' => 'You cannot access this form at this time.',
                'type' => 'warning'
            ]);
        }

        return view('pages.vendors.wizard.main', compact('vendor'));
    }

    // =====================================================
    // 🔥 RESEND INVITATION
    // =====================================================
    public function resendInvitation($id)
    {
        $vendor = Vendor::findOrFail($id);

        // Generate new token
        $vendor->update([
            'token' => Vendor::generateToken(),
            'status' => 'pending',
            'registration_completed' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invitation reset. You can now send the email again.'
        ]);
    }
}