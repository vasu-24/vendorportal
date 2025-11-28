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
    // Store new vendor
public function store(Request $request)
{
    $request->validate([
        'vendor_name' => 'required|string|max:255',
        'vendor_email' => 'required|email|max:255'
    ]);

    $vendor = Vendor::create([
        'vendor_name' => $request->vendor_name,
        'vendor_email' => $request->vendor_email,
        'status' => 'pending',
        'token' => Vendor::generateToken()
    ]);

    return redirect()->route('vendors.index')->with('success', 'Vendor created successfully! Please select a template to send email.');
}

// Update vendor template
public function updateTemplate(Request $request, $id)
{
    $request->validate([
        'template_id' => 'required|exists:mail_templates,id'
    ]);

    $vendor = Vendor::findOrFail($id);
    $vendor->update(['template_id' => $request->template_id]);

    return response()->json([
        'success' => true,
        'message' => 'Template updated successfully!'
    ]);
}

    // List all vendors
   // List all vendors
public function index()
{
    $vendors = Vendor::with('template')->orderBy('created_at', 'desc')->get();
    $templates = MailTemplate::where('status', 'active')->get();
    return view('pages.vendors.index', compact('vendors', 'templates'));
}

    // Send email to vendor
    // Send email to vendor
// Send email to vendor
public function sendEmail(Request $request, $id)
{
    $request->validate([
        'email_message' => 'required|string'
    ]);

    $vendor = Vendor::with('template')->findOrFail($id);

    if (!$vendor->template) {
        return back()->with('error', 'No template selected for this vendor!');
    }

    // Save email message
    $vendor->update(['email_message' => $request->email_message]);

    // Get custom email message
    $emailMessage = $request->email_message;

    // Replace placeholders in email message
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
        $emailMessage = str_replace($placeholder, $value, $emailMessage);
    }

    // Get template content for PDF
    $templateBody = $vendor->template->body;
    foreach ($placeholders as $placeholder => $value) {
        $templateBody = str_replace($placeholder, $value, $templateBody);
    }

    // Generate accept/reject URLs with token
    $acceptUrl = route('vendors.accept', $vendor->token);
    $rejectUrl = route('vendors.reject', $vendor->token);

    // Send email with custom message and template PDF
    try {
        Mail::to($vendor->vendor_email)->send(
            new VendorMail(
                $vendor->template->subject,
                $emailMessage,  // Custom email message
                $acceptUrl,
                $rejectUrl,
                $vendor->vendor_name,
                $templateBody,  // Template content for PDF
                $vendor->template->name
            )
        );
        
        // Update email sent timestamp
        $vendor->update(['email_sent_at' => now()]);
        
        return back()->with('success', 'Email sent successfully with template attachment to ' . $vendor->vendor_email);
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to send email: ' . $e->getMessage());
    }
}

    // Handle vendor acceptance
    public function accept($token)
    {
        $vendor = Vendor::where('token', $token)->firstOrFail();

        if ($vendor->status !== 'pending') {
            return view('pages.vendors.response', [
                'message' => 'This link has already been used.',
                'type' => 'warning'
            ]);
        }

        $vendor->update([
            'status' => 'accepted',
            'responded_at' => now()
        ]);

        return view('pages.vendors.response', [
            'message' => 'Thank you! You have successfully accepted the invitation.',
            'type' => 'success',
            'vendor' => $vendor
        ]);
    }

    // Handle vendor rejection
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
}