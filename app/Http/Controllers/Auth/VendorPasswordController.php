<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class VendorPasswordController extends Controller
{
    /**
     * Show set password form
     */
    public function showSetPasswordForm($token)
    {
        $vendor = Vendor::where('token', $token)->first();

        // Check if token exists
        if (!$vendor) {
            return view('pages.vendors.response', [
                'message' => 'Invalid or expired link. Please contact support.',
                'type' => 'error'
            ]);
        }

        // Check if vendor is approved
        if ($vendor->approval_status !== 'approved') {
            return view('pages.vendors.response', [
                'message' => 'Your account is not yet approved. Please wait for approval.',
                'type' => 'warning'
            ]);
        }

        // Check if password already set
        if ($vendor->password) {
            return view('pages.vendors.response', [
                'message' => 'Password has already been set. Please login to continue.',
                'type' => 'info',
                'show_login' => true
            ]);
        }

        return view('pages.auth.set-password', compact('vendor', 'token'));
    }

    /**
     * Save the new password
     */
    public function setPassword(Request $request, $token)
    {
        $vendor = Vendor::where('token', $token)->first();

        // Validate token
        if (!$vendor) {
            return back()->with('error', 'Invalid or expired link.');
        }

        // Check if vendor is approved
        if ($vendor->approval_status !== 'approved') {
            return back()->with('error', 'Your account is not yet approved.');
        }

        // Check if password already set
        if ($vendor->password) {
            return redirect()->route('vendor.login')->with('error', 'Password already set. Please login.');
        }

        // Validate password
        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
            ],
        ], [
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
        ]);

        // Update password
        $vendor->update([
            'password' => Hash::make($request->password),
            'password_set_at' => now(),
        ]);

        // Regenerate token for security (prevents reuse)
        $vendor->update([
            'token' => Vendor::generateToken()
        ]);

        return redirect()->route('vendor.login')->with('success', 'Password set successfully! Please login to continue.');
    }
}