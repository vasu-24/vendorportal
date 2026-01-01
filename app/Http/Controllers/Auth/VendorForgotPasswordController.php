<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class VendorForgotPasswordController extends Controller
{
    /**
     * Show forgot password form (Step 1 - Enter Email)
     */
    public function showForgotPasswordForm()
    {
        return view('pages.auth.forgot-password', ['type' => 'vendor', 'step' => 1]);
    }

    /**
     * Send OTP to email
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // Check if vendor exists with this email
        $vendor = Vendor::where('vendor_email', $request->email)->first();

        if (!$vendor) {
            return back()->withErrors(['email' => 'We could not find a vendor with that email address.']);
        }

        // Check if vendor is approved
        if ($vendor->approval_status !== 'approved') {
            return back()->withErrors(['email' => 'Your account is not yet approved. Please wait for approval.']);
        }

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete any existing OTPs for this email
        DB::table('vendor_password_resets')->where('email', $request->email)->delete();

        // Store OTP (hashed for security)
        DB::table('vendor_password_resets')->insert([
            'email' => $request->email,
            'token' => Hash::make($otp),
            'otp' => $otp, // Store plain OTP for verification (will be deleted after use)
            'created_at' => Carbon::now()
        ]);

        // Send OTP email
        try {
            Mail::send('emails.vendor-otp', [
                'otp' => $otp,
                'vendor' => $vendor
            ], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Your OTP for Password Reset - Vendor Portal');
            });

            // Store email in session for next step
            session(['reset_email' => $request->email]);

            return redirect()->route('vendor.password.verify.otp.form')
                ->with('success', 'OTP has been sent to your email address.');

        } catch (\Exception $e) {
            \Log::error('Vendor OTP Email Error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Failed to send OTP. Please try again later.']);
        }
    }

    /**
     * Show OTP verification form (Step 2 - Enter OTP)
     */
    public function showVerifyOtpForm()
    {
        $email = session('reset_email');
        
        if (!$email) {
            return redirect()->route('vendor.password.request')
                ->withErrors(['email' => 'Please enter your email first.']);
        }

        return view('pages.auth.verify-otp', [
            'type' => 'vendor',
            'email' => $email
        ]);
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6'
        ]);

        $email = session('reset_email');

        if (!$email) {
            return redirect()->route('vendor.password.request')
                ->withErrors(['email' => 'Session expired. Please try again.']);
        }

        // Get OTP record
        $resetRecord = DB::table('vendor_password_resets')
            ->where('email', $email)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP. Please request a new one.']);
        }

        // Check if OTP is expired (10 minutes)
        $otpCreatedAt = Carbon::parse($resetRecord->created_at);
        if ($otpCreatedAt->addMinutes(10)->isPast()) {
            DB::table('vendor_password_resets')->where('email', $email)->delete();
            session()->forget('reset_email');
            
            return redirect()->route('vendor.password.request')
                ->withErrors(['email' => 'OTP has expired. Please request a new one.']);
        }

        // Verify OTP
        if ($resetRecord->otp !== $request->otp) {
            return back()->withErrors(['otp' => 'Invalid OTP. Please try again.']);
        }

        // OTP verified - mark in session
        session(['otp_verified' => true]);

        return redirect()->route('vendor.password.reset.form')
            ->with('success', 'OTP verified successfully. Please set your new password.');
    }

    /**
     * Resend OTP
     */
    public function resendOtp()
    {
        $email = session('reset_email');

        if (!$email) {
            return redirect()->route('vendor.password.request')
                ->withErrors(['email' => 'Session expired. Please try again.']);
        }

        $vendor = Vendor::where('vendor_email', $email)->first();

        if (!$vendor) {
            return redirect()->route('vendor.password.request')
                ->withErrors(['email' => 'Vendor not found.']);
        }

        // Generate new 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Update OTP in database
        DB::table('vendor_password_resets')->where('email', $email)->delete();
        DB::table('vendor_password_resets')->insert([
            'email' => $email,
            'token' => Hash::make($otp),
            'otp' => $otp,
            'created_at' => Carbon::now()
        ]);

        // Send OTP email
        try {
            Mail::send('emails.vendor-otp', [
                'otp' => $otp,
                'vendor' => $vendor
            ], function ($message) use ($email) {
                $message->to($email);
                $message->subject('Your OTP for Password Reset - Vendor Portal');
            });

            return back()->with('success', 'New OTP has been sent to your email.');

        } catch (\Exception $e) {
            \Log::error('Vendor Resend OTP Error: ' . $e->getMessage());
            return back()->withErrors(['otp' => 'Failed to resend OTP. Please try again.']);
        }
    }

    /**
     * Show reset password form (Step 3 - Enter New Password)
     */
    public function showResetPasswordForm()
    {
        $email = session('reset_email');
        $otpVerified = session('otp_verified');

        if (!$email || !$otpVerified) {
            return redirect()->route('vendor.password.request')
                ->withErrors(['email' => 'Please complete OTP verification first.']);
        }

        return view('pages.auth.reset-password', [
            'type' => 'vendor',
            'email' => $email
        ]);
    }

    /**
     * Reset the password
     */
    public function resetPassword(Request $request)
    {
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

        $email = session('reset_email');
        $otpVerified = session('otp_verified');

        if (!$email || !$otpVerified) {
            return redirect()->route('vendor.password.request')
                ->withErrors(['email' => 'Session expired. Please try again.']);
        }

        // Find vendor and update password
        $vendor = Vendor::where('vendor_email', $email)->first();

        if (!$vendor) {
            return redirect()->route('vendor.password.request')
                ->withErrors(['email' => 'Vendor not found.']);
        }

        // Update password
        $vendor->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
        ]);

        // Delete the OTP record
        DB::table('vendor_password_resets')->where('email', $email)->delete();

        // Clear session
        session()->forget(['reset_email', 'otp_verified']);

        return redirect()->route('vendor.login')
            ->with('success', 'Password has been reset successfully! Please login with your new password.');
    }
}