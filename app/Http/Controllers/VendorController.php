<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\MailTemplate;
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
            'vendor_email' => 'required|email|max:255'
        ], [
            'vendor_name.unique' => 'A vendor with this name already exists!',
        ]);

        $vendor = Vendor::create([
            'vendor_name' => $request->vendor_name,
            'vendor_email' => $request->vendor_email,
            'status' => 'pending',
            'token' => Vendor::generateToken()
        ]);

        return redirect()->route('vendors.index')->with('success', 'Vendor created successfully! Please select a template to send email.');
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

    // Send email to vendor
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

        $placeholders = [
            '{vendor_name}' => $vendor->vendor_name,
            '{vendor_email}' => $vendor->vendor_email,
            '{portal_url}' => url('/'),
            '{current_date}' => now()->format('d-M-Y'),
            '{current_time}' => now()->format('h:i A'),
            '{year}' => date('Y'),
            '{company_name}' => 'Vendor Portal',
        ];

        foreach ($placeholders as $placeholder => $value) {
            $templateBody = str_replace($placeholder, $value, $templateBody);
            $templateSubject = str_replace($placeholder, $value, $templateSubject);
        }

        $acceptUrl = route('vendors.accept', $vendor->token);
        $rejectUrl = route('vendors.reject', $vendor->token);

        try {
            Mail::to($vendor->vendor_email)->send(
                new VendorMail(
                    $templateSubject,
                    $templateBody,
                    $acceptUrl,
                    $rejectUrl,
                    $vendor->vendor_name,
                    $templateBody
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

    // Handle vendor acceptance - Redirect to wizard
    public function accept($token)
    {
        $vendor = Vendor::where('token', $token)->firstOrFail();

        // Allow access if pending OR if rejected (for correction)
        if (!in_array($vendor->status, ['pending', 'accepted']) && 
            !in_array($vendor->approval_status, ['rejected', 'revision_requested'])) {
            return view('pages.vendors.response', [
                'message' => 'This link has already been used.',
                'type' => 'warning'
            ]);
        }

        // Update status to accepted if still pending
        if ($vendor->status === 'pending') {
            $vendor->update([
                'status' => 'accepted',
                'responded_at' => now()
            ]);
        }

        // Redirect to wizard form
        return redirect()->route('vendors.wizard', $vendor->token);
    }

    // Handle vendor rejection (from initial email)
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
    // 🔥 SHOW WIZARD FORM (NEW METHOD)
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

        // Check if vendor can access wizard
        $canAccess = in_array($vendor->status, ['accepted']) || 
                     in_array($vendor->approval_status, ['rejected', 'revision_requested', 'draft', null]);

        if (!$canAccess) {
            return view('pages.vendors.response', [
                'message' => 'You cannot access this form at this time.',
                'type' => 'warning'
            ]);
        }

        return view('pages.vendors.wizard.main', compact('vendor'));
    }
}