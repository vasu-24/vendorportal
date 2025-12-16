<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorAuthController extends Controller
{
    /**
     * Show vendor login page
     */
    public function showLogin()
    {
        // If already logged in as vendor, redirect to vendor dashboard
        if (Auth::guard('vendor')->check()) {
            return redirect()->route('vendor.dashboard');
        }

        return view('pages.auth.login', ['type' => 'vendor']);
    }

    /**
     * Handle vendor login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = [
            'vendor_email' => $request->email,
            'password' => $request->password,
        ];

        $remember = $request->has('remember');

        if (Auth::guard('vendor')->attempt($credentials, $remember)) {
            $vendor = Auth::guard('vendor')->user();

            // Check if vendor is approved
            if (!$vendor->isApproved()) {
                Auth::guard('vendor')->logout();
                return back()->with('error', 'Your account is not approved yet. Please wait for approval.');
            }

            // Check if password is set
            if (empty($vendor->password)) {
                Auth::guard('vendor')->logout();
                return back()->with('error', 'Please set your password first using the link sent to your email.');
            }

            $request->session()->regenerate();
            
            return redirect()->intended(route('vendor.dashboard'));
        }

        return back()->with('error', 'Invalid email or password.');
    }

    /**
     * Handle vendor logout
     */
    public function logout(Request $request)
    {
        Auth::guard('vendor')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('vendor.login');
    }
}