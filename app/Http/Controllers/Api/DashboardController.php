<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\Invoice;
use App\Models\Category;
use App\Models\VendorApprovalHistory;
use App\Services\ZohoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $zohoService;

    public function __construct(ZohoService $zohoService)
    {
        $this->zohoService = $zohoService;
    }

    /**
     * Get dashboard summary stats
     */
    public function summary()
    {
        try {
            $now = Carbon::now();
            $startOfMonth = $now->copy()->startOfMonth();
// Vendor stats
$totalVendors = Vendor::count();
$pendingApprovals = Vendor::where('approval_status', 'pending_approval')->count();
$newVendorsThisMonth = Vendor::where('created_at', '>=', $startOfMonth)->count();

// Contract stats
$totalContracts = \App\Models\Contract::count();

// Invoice stats
$totalInvoices = Invoice::count();
$adhocInvoices = Invoice::where('invoice_type', 'adhoc')->count();

// Travel invoices (exclude draft)
$travelInvoices = \App\Models\TravelInvoice::where('status', '!=', 'draft')->count();

$invoicesProcessed = Invoice::where('created_at', '>=', $startOfMonth)
    ->whereIn('status', ['approved', 'paid'])
    ->count();

// Financial stats
$thisMonthAmount = Invoice::where('created_at', '>=', $startOfMonth)
    ->whereIn('status', ['approved', 'paid'])
    ->sum('grand_total') ?? 0;

$unpaidAmount = Invoice::where('status', 'approved')
    ->sum('grand_total') ?? 0;

// Average approval time (in days)
$avgApprovalDays = $this->calculateAvgApprovalTime();

return response()->json([
    'success' => true,
    'data' => [
        'total_vendors' => $totalVendors,
        'pending_approvals' => $pendingApprovals,
        'total_contracts' => $totalContracts,
        'total_invoices' => $totalInvoices,
        'adhoc_invoices' => $adhocInvoices,
        'travel_invoices' => $travelInvoices,
        'new_vendors_this_month' => $newVendorsThisMonth,
        'invoices_processed' => $invoicesProcessed,
        'this_month_amount' => $thisMonthAmount,
        'unpaid_amount' => $unpaidAmount,
        'avg_approval_days' => $avgApprovalDays,
    ]
]);

        } catch (\Exception $e) {
            Log::error('Dashboard summary error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load summary'
            ], 500);
        }
    }

    /**
     * Get focus items (things that need attention)
     */
    public function focusItems()
    {
        try {
            $items = [];

            // 1. Pending Approvals
            $pendingApprovals = Vendor::where('approval_status', 'pending_approval')->count();
            $overdueApprovals = Vendor::where('approval_status', 'pending_approval')
                ->where('registration_completed_at', '<=', Carbon::now()->subDays(3))
                ->count();

            if ($pendingApprovals > 0) {
                $items[] = [
                    'type' => 'approvals',
                    'priority' => $overdueApprovals > 0 ? 'high' : 'medium',
                    'icon' => 'bi-hourglass-split',
                    'title' => "{$pendingApprovals} Vendor Approvals Pending",
                    'subtitle' => $overdueApprovals > 0 ? "{$overdueApprovals} waiting for more than 3 days" : 'Review and approve vendors',
                    'action_text' => 'Review',
                    'action_url' => route('vendors.approval.queue'),
                ];
            }

            // 2. Invoices Due Soon
            $invoicesDueTomorrow = Invoice::where('status', 'approved')
                ->whereDate('due_date', Carbon::tomorrow())
                ->get();

            foreach ($invoicesDueTomorrow->take(2) as $invoice) {
                $items[] = [
                    'type' => 'invoice',
                    'priority' => 'high',
                    'icon' => 'bi-receipt',
                    'title' => 'Invoice Due Tomorrow',
                    'subtitle' => "#{$invoice->invoice_number} • ₹" . number_format($invoice->grand_total),
                    'action_text' => 'View',
                    'action_url' => route('invoices.show', $invoice->id),
                ];
            }

            // 3. Vendors Not Synced to Zoho
            $vendorsNotSynced = Vendor::where('approval_status', 'approved')
                ->whereNull('zoho_contact_id')
                ->count();

            if ($vendorsNotSynced > 0) {
                $items[] = [
                    'type' => 'sync',
                    'priority' => 'medium',
                    'icon' => 'bi-cloud-slash',
                    'title' => "{$vendorsNotSynced} Vendors Not Synced",
                    'subtitle' => 'Zoho sync pending',
                    'action_text' => 'Sync',
                    'action_url' => '#',
                ];
            }

            // 4. Documents Expiring Soon (if you have document expiry)
            // $expiringDocs = VendorDocument::where('valid_to', '<=', Carbon::now()->addDays(30))
            //     ->where('valid_to', '>', Carbon::now())
            //     ->count();
            
            // if ($expiringDocs > 0) {
            //     $items[] = [
            //         'type' => 'documents',
            //         'priority' => 'low',
            //         'icon' => 'bi-file-earmark-x',
            //         'title' => "{$expiringDocs} Documents Expiring",
            //         'subtitle' => 'Within next 30 days',
            //         'action_text' => 'Check',
            //         'action_url' => '#',
            //     ];
            // }

            // 5. Invoices Overdue
            $overdueInvoices = Invoice::where('status', 'approved')
                ->whereDate('due_date', '<', Carbon::today())
                ->count();

            if ($overdueInvoices > 0) {
                $items[] = [
                    'type' => 'invoice',
                    'priority' => 'high',
                    'icon' => 'bi-exclamation-triangle',
                    'title' => "{$overdueInvoices} Invoices Overdue",
                    'subtitle' => 'Payment past due date',
                    'action_text' => 'View',
                    'action_url' => route('invoices.index'),
                ];
            }

            // Sort by priority
            usort($items, function($a, $b) {
                $priority = ['high' => 0, 'medium' => 1, 'low' => 2];
                return $priority[$a['priority']] <=> $priority[$b['priority']];
            });

            return response()->json([
                'success' => true,
                'data' => array_slice($items, 0, 5) // Max 5 items
            ]);

        } catch (\Exception $e) {
            Log::error('Focus items error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load focus items'
            ], 500);
        }
    }

    /**
     * Get recent activity feed
     */
    public function recentActivity()
    {
        try {
            $activities = [];

            // Recent vendor approvals
            $recentApprovals = Vendor::whereNotNull('approved_at')
                ->orderBy('approved_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($recentApprovals as $vendor) {
                $activities[] = [
                    'type' => 'approved',
                    'message' => "<strong>{$vendor->vendor_name}</strong> approved",
                    'time' => $vendor->approved_at,
                    'time_ago' => Carbon::parse($vendor->approved_at)->diffForHumans(),
                ];
            }

            // Recent vendor registrations
            $recentRegistrations = Vendor::where('registration_completed', true)
                ->whereNotNull('registration_completed_at')
                ->orderBy('registration_completed_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($recentRegistrations as $vendor) {
                $activities[] = [
                    'type' => 'pending',
                    'message' => "<strong>{$vendor->vendor_name}</strong> registered",
                    'time' => $vendor->registration_completed_at,
                    'time_ago' => Carbon::parse($vendor->registration_completed_at)->diffForHumans(),
                ];
            }

            // Recent invoices
            $recentInvoices = Invoice::orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($recentInvoices as $invoice) {
                $statusText = $invoice->status === 'paid' ? 'marked as paid' : 'submitted';
                $activities[] = [
                    'type' => 'invoice',
                    'message' => "<strong>#{$invoice->invoice_number}</strong> {$statusText}",
                    'time' => $invoice->created_at,
                    'time_ago' => Carbon::parse($invoice->created_at)->diffForHumans(),
                ];
            }

            // Recent Zoho syncs
            $recentSyncs = Vendor::whereNotNull('zoho_synced_at')
                ->orderBy('zoho_synced_at', 'desc')
                ->limit(3)
                ->get();

            foreach ($recentSyncs as $vendor) {
                $activities[] = [
                    'type' => 'sync',
                    'message' => "<strong>{$vendor->vendor_name}</strong> synced to Zoho",
                    'time' => $vendor->zoho_synced_at,
                    'time_ago' => Carbon::parse($vendor->zoho_synced_at)->diffForHumans(),
                ];
            }

            // Sort by time descending
            usort($activities, function($a, $b) {
                return strtotime($b['time']) <=> strtotime($a['time']);
            });

            return response()->json([
                'success' => true,
                'data' => array_slice($activities, 0, 6) // Max 6 items
            ]);

        } catch (\Exception $e) {
            Log::error('Recent activity error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load activity'
            ], 500);
        }
    }

    /**
     * Get Zoho sync status
     */
    public function syncStatus()
    {
        try {
            // Vendors sync status
            $totalVendors = Vendor::where('approval_status', 'approved')->count();
            $syncedVendors = Vendor::where('approval_status', 'approved')
                ->whereNotNull('zoho_contact_id')
                ->count();

            // Invoices sync status
            $totalInvoices = Invoice::whereIn('status', ['approved', 'paid'])->count();
            $syncedInvoices = Invoice::whereIn('status', ['approved', 'paid'])
                ->whereNotNull('zoho_invoice_id')
                ->count();

            // Categories sync status (if you have zoho_account_id in categories)
            $totalCategories = Category::count();
            $syncedCategories = Category::whereNotNull('zoho_account_id')->count();

            // Last sync time
            $lastSync = Vendor::whereNotNull('zoho_synced_at')
                ->orderBy('zoho_synced_at', 'desc')
                ->first();
            $lastSyncTime = $lastSync ? Carbon::parse($lastSync->zoho_synced_at)->diffForHumans() : null;

            // Vendors not synced
            $vendorsNotSynced = $totalVendors - $syncedVendors;

            return response()->json([
                'success' => true,
                'data' => [
                    'vendors' => [
                        'total' => $totalVendors,
                        'synced' => $syncedVendors,
                    ],
                    'invoices' => [
                        'total' => $totalInvoices,
                        'synced' => $syncedInvoices,
                    ],
                    'categories' => [
                        'total' => $totalCategories,
                        'synced' => $syncedCategories,
                    ],
                    'last_sync' => $lastSyncTime,
                    'vendors_not_synced' => $vendorsNotSynced,
                    'new_in_zoho' => 0, // TODO: Implement check for vendors in Zoho not in portal
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Sync status error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load sync status'
            ], 500);
        }
    }

    /**
     * Sync all data with Zoho
     */
    public function syncAll()
    {
        try {
            if (!$this->zohoService->isConnected()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zoho is not connected. Please connect first.'
                ], 400);
            }

            $results = [
                'vendors_synced' => 0,
                'vendors_failed' => 0,
            ];

            // Sync vendors not yet synced
            $vendorsToSync = Vendor::where('approval_status', 'approved')
                ->whereNull('zoho_contact_id')
                ->get();

            foreach ($vendorsToSync as $vendor) {
                try {
                    $this->zohoService->createVendor($vendor);
                    $results['vendors_synced']++;
                } catch (\Exception $e) {
                    $results['vendors_failed']++;
                    Log::error('Vendor sync failed', [
                        'vendor_id' => $vendor->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$results['vendors_synced']} vendors synced, {$results['vendors_failed']} failed",
                'data' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Sync all error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync vendors with Zoho
     */
    public function syncVendors()
    {
        try {
            if (!$this->zohoService->isConnected()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zoho is not connected'
                ], 400);
            }

            $synced = 0;
            $failed = 0;

            $vendorsToSync = Vendor::where('approval_status', 'approved')
                ->whereNull('zoho_contact_id')
                ->get();

            foreach ($vendorsToSync as $vendor) {
                try {
                    $this->zohoService->createVendor($vendor);
                    $synced++;
                } catch (\Exception $e) {
                    $failed++;
                    Log::error('Vendor sync failed', [
                        'vendor_id' => $vendor->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$synced} vendors synced" . ($failed > 0 ? ", {$failed} failed" : ""),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import vendors from Zoho
     */
    public function importFromZoho()
    {
        try {
            if (!$this->zohoService->isConnected()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zoho is not connected'
                ], 400);
            }

            // TODO: Implement fetching vendors from Zoho and creating in portal
            // This would require a new method in ZohoService to get all vendors

            return response()->json([
                'success' => true,
                'message' => 'Import feature coming soon',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate average approval time in days
     */
    private function calculateAvgApprovalTime()
    {
        try {
            $approvedVendors = Vendor::whereNotNull('approved_at')
                ->whereNotNull('registration_completed_at')
                ->get();

            if ($approvedVendors->isEmpty()) {
                return 0;
            }

            $totalDays = 0;
            $count = 0;

            foreach ($approvedVendors as $vendor) {
                $submitted = Carbon::parse($vendor->registration_completed_at);
                $approved = Carbon::parse($vendor->approved_at);
                $days = $submitted->diffInDays($approved);
                $totalDays += $days;
                $count++;
            }

            return $count > 0 ? round($totalDays / $count, 1) : 0;

        } catch (\Exception $e) {
            return 0;
        }
    }
}