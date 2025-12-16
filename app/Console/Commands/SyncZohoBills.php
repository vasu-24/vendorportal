<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZohoService;

class SyncZohoBills extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'zoho:sync-bills';

    /**
     * The console command description.
     */
    protected $description = 'Sync bill payment status from Zoho Books';

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

            $results = $zohoService->syncAllPendingBills();

            $this->info("Total invoices: {$results['total']}");
            $this->info("Synced: {$results['synced']}");
            $this->info("Marked as paid: {$results['paid']}");
            $this->info("Failed: {$results['failed']}");

            $this->info('Zoho Bills Sync Complete!');

            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}