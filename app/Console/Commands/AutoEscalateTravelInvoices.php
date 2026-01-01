<?php

namespace App\Console\Commands;

use App\Models\TravelInvoice;
use App\Models\TravelBatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutoEscalateTravelInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'travel-invoices:auto-escalate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-escalate travel invoices from VOO to CEO after 7 days of no action';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auto-escalation check...');

        // Find invoices that are pending_vp for more than 7 days
        $sevenDaysAgo = Carbon::now()->subDays(7);

        $invoicesToEscalate = TravelInvoice::where('status', 'pending_vp')
            ->where(function($query) use ($sevenDaysAgo) {
                // Check vp_pending_since if it exists, otherwise use rm_approved_at
                $query->where('vp_pending_since', '<=', $sevenDaysAgo)
                      ->orWhere(function($q) use ($sevenDaysAgo) {
                          $q->whereNull('vp_pending_since')
                            ->where('rm_approved_at', '<=', $sevenDaysAgo);
                      });
            })
            ->get();

        if ($invoicesToEscalate->isEmpty()) {
            $this->info('No invoices to escalate.');
            return Command::SUCCESS;
        }

        $escalatedCount = 0;
        $batchIds = [];

        foreach ($invoicesToEscalate as $invoice) {
            try {
                $invoice->update([
                    'status' => 'pending_ceo',
                    'current_approver_role' => 'ceo',
                    'auto_escalated' => true,
                    'auto_escalated_at' => now(),
                    'escalation_reason' => 'Auto-escalated: VOO did not approve within 7 days',
                ]);

                $escalatedCount++;
                $batchIds[] = $invoice->batch_id;

                Log::info('Travel Invoice auto-escalated to CEO', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'batch_id' => $invoice->batch_id,
                    'days_pending' => Carbon::parse($invoice->rm_approved_at)->diffInDays(now()),
                ]);

                $this->line("  - Escalated: {$invoice->invoice_number}");

            } catch (\Exception $e) {
                Log::error('Failed to auto-escalate travel invoice', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("  - Failed: {$invoice->invoice_number} - {$e->getMessage()}");
            }
        }

        // Update batch statuses
        $uniqueBatchIds = array_unique($batchIds);
        foreach ($uniqueBatchIds as $batchId) {
            try {
                $batch = TravelBatch::find($batchId);
                if ($batch) {
                    $batch->updateStatus();
                }
            } catch (\Exception $e) {
                Log::error('Failed to update batch status after escalation', [
                    'batch_id' => $batchId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Auto-escalation complete. Escalated: {$escalatedCount} invoices.");

        // TODO: Send notification to CEO about escalated invoices
        // You can add email/notification logic here

        return Command::SUCCESS;
    }
}