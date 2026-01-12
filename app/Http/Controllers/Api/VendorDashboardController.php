<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Invoice;
use Carbon\Carbon;

class VendorDashboardController extends Controller
{
    /**
     * Get dashboard statistics
     * Pending includes: ALL statuses except approved, rejected, paid, draft
     */
    public function getStats()
    {
        try {
            $vendor = Auth::guard('vendor')->user();
            
            // Get total count
            $totalInvoices = Invoice::where('vendor_id', $vendor->id)->count();
            
            // Get approved count
            $approvedInvoices = Invoice::where('vendor_id', $vendor->id)
                ->where('status', 'approved')
                ->count();
            
            // Get rejected count
            $rejectedInvoices = Invoice::where('vendor_id', $vendor->id)
                ->where('status', 'rejected')
                ->count();
            
            // Pending = All statuses EXCEPT approved, rejected, and paid
            // This includes: pending, submitted, under_review, pending_rm, pending_finance, pending_ceo, pending_vp, etc.
            $pendingInvoices = Invoice::where('vendor_id', $vendor->id)
                ->whereNotIn('status', ['approved', 'rejected', 'paid', 'draft'])
                ->count();
            
            $stats = [
                'total_invoices' => $totalInvoices,
                'approved_invoices' => $approvedInvoices,
                'pending_invoices' => $pendingInvoices,
                'rejected_invoices' => $rejectedInvoices,
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching statistics: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get recent invoices
     */
    public function getRecentInvoices()
    {
        try {
            $vendor = Auth::guard('vendor')->user();
            
            $invoices = Invoice::where('vendor_id', $vendor->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($invoice) {
                    return [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number ?? 'INV-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT),
                        'date' => $invoice->created_at->format('d M Y'),
                        'type' => ucfirst($invoice->invoice_type ?? 'Regular'),
                        'status' => $invoice->status,
                        'created_at' => $invoice->created_at->toIso8601String()
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $invoices
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching invoices: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get monthly chart data
     */
    public function getMonthlyData()
    {
        try {
            $vendor = Auth::guard('vendor')->user();
            
            $months = [];
            $data = [];
            
            // Get last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $months[] = $month->format('M Y');
                
                // Count invoices for this month
                $count = Invoice::where('vendor_id', $vendor->id)
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();
                
                $data[] = $count;
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $months,
                    'values' => $data
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching monthly data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get recent activities
     */
    public function getRecentActivities()
    {
        try {
            $vendor = Auth::guard('vendor')->user();
            
            $activities = [];
            
            // Get recent invoice activities
            $recentInvoices = Invoice::where('vendor_id', $vendor->id)
                ->orderBy('created_at', 'desc')
                ->skip(10)
                ->take(5)
                ->get();
            
            foreach ($recentInvoices as $invoice) {
                $activities[] = [
                    'title' => 'Invoice Submitted',
                    'description' => 'Invoice #' . ($invoice->invoice_number ?? 'INV-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT)),
                    'date' => $invoice->created_at->format('d M Y'),
                    'time' => $invoice->created_at->format('h:i A'),
                    'icon' => 'receipt'
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $activities
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching activities: ' . $e->getMessage()
            ], 500);
        }
    }
}