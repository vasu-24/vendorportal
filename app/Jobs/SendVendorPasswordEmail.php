<?php

namespace App\Jobs;

use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendVendorPasswordEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $vendor;
    public $tries = 3; // Retry 3 times if failed
    public $timeout = 30; // 30 seconds timeout

    /**
     * Create a new job instance.
     */
    public function __construct(Vendor $vendor)
    {
        $this->vendor = $vendor;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Generate password reset token
            $token = Str::random(64);
            
            // Update vendor with token
            $this->vendor->update([
                'token' => $token,
            ]);

            $setPasswordUrl = route('vendor.password.show', $token);

            // Send email
            Mail::send('emails.vendor-set-password', [
                'vendor' => $this->vendor,
                'vendorName' => $this->vendor->vendor_name,
                'vendorEmail' => $this->vendor->vendor_email,
                'setPasswordUrl' => $setPasswordUrl,
            ], function ($message) {
                $message->to($this->vendor->vendor_email)
                        ->subject('Set Your Password - Vendor Portal');
            });

            Log::info('Password email sent to vendor', ['vendor_id' => $this->vendor->id]);

        } catch (\Exception $e) {
            Log::error('SendVendorPasswordEmail job failed', [
                'vendor_id' => $this->vendor->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendVendorPasswordEmail job failed permanently', [
            'vendor_id' => $this->vendor->id,
            'vendor_email' => $this->vendor->vendor_email,
            'error' => $exception->getMessage()
        ]);
    }
}