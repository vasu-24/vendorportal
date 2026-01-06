<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZohoService;
use App\Models\Invoice; // Corrected model name
use App\Models\TravelInvoice;

class SyncZohoBills extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'zoho:sync-bills';

    /**
     * The console command description.
     */
    protected $description = 'Sync bill payment status from Zoho Books (Normal + Travel Invoices)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Zoho Bills Sync...');

        try {
            $zohoService = app(ZohoService::class);

            if (!$zohoService->isConnected()) {
                $this->error('Zoho not connected!');
                return 1;
            }

            // ===============================
            // NORMAL INVOICES
            // ===============================
            $normal = Invoice::whereNotNull('zoho_invoice_id')
                ->where('status', 'approved')
                ->get();

            // ===============================
            // TRAVEL INVOICES
            // ===============================
            $travel = TravelInvoice::whereNotNull('zoho_bill_id')
                ->where('status', 'approved')
                ->get();

            $total = $normal->count() + $travel->count();
            $synced = 0;
            $paid = 0;
            $failed = 0;

            // ----- NORMAL -----
            foreach ($normal as $invoice) {
                try {
                    $zohoService->syncBillStatus($invoice);
                    $synced++;
                    if ($invoice->fresh()->status === 'paid') $paid++;
                } catch (\Throwable $e) {
                    $failed++;
                }
            }

            // ----- TRAVEL -----
            foreach ($travel as $invoice) {
                try {
                    // Add this function in ZohoService (see below)
                    $zohoService->syncTravelBillStatus($invoice);
                    $synced++;
                    if ($invoice->fresh()->status === 'paid') $paid++;
                } catch (\Throwable $e) {
                    $failed++;
                }
            }

            $this->info("Total invoices: $total");
            $this->info("Synced: $synced");
            $this->info("Marked as paid: $paid");
            $this->info("Failed: $failed");
            $this->info('Zoho Bills Sync Complete!');

            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
