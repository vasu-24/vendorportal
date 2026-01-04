<?php

namespace App\Jobs;

use App\Models\Vendor;
use App\Services\ZohoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncVendorToZoho implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $vendor;
    public $tries = 3; // Retry 3 times if failed
    public $timeout = 60; // 60 seconds timeout

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
    public function handle(ZohoService $zohoService): void
    {
        try {
            if (!$zohoService->isConnected()) {
                Log::warning('Zoho not connected for vendor', ['vendor_id' => $this->vendor->id]);
                return;
            }

            $this->vendor->load(['companyInfo', 'contact', 'statutoryInfo', 'bankDetails']);
            $zohoService->createVendor($this->vendor);

            Log::info('Vendor synced to Zoho successfully', ['vendor_id' => $this->vendor->id]);

        } catch (\Exception $e) {
            Log::error('Zoho sync job failed', [
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
        Log::error('SyncVendorToZoho job failed permanently', [
            'vendor_id' => $this->vendor->id,
            'error' => $exception->getMessage()
        ]);
    }
}