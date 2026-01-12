<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    // =====================================================
    // GET ALL SETTINGS (from .env / config)
    // =====================================================
    
    public function index()
    {
        try {
            // Get Zoho connection status
            $zohoConnected = $this->checkZohoConnection();
            $zohoOrgId = null;
            
            try {
                $zohoOrgId = \App\Models\ZohoToken::getActive()?->organization_id;
            } catch (\Exception $e) {
                // ZohoToken might not exist
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    // Zoho (from config/env)
                    'zoho_connected' => $zohoConnected,
                    'zoho_organization_id' => $zohoOrgId,
                    
                    // TDS (from config/env)
                    'tds_monthly_due_date' => config('app.tds_monthly_due_date', 7),
                    'tds_march_due_date' => config('app.tds_march_due_date', 30),
                    'tds_warning_days' => config('app.tds_warning_days', 3),
                    'tds_default_rate' => config('app.tds_default_rate', 5),
                    'tds_show_dashboard_warning' => config('app.tds_show_dashboard_warning', true),
                    'tds_show_invoice_warning' => config('app.tds_show_invoice_warning', true),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get Settings Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load settings'
            ], 500);
        }
    }

    // =====================================================
    // SAVE ALL SETTINGS (to .env file)
    // =====================================================
    
    public function store(Request $request)
    {
        try {
            // =====================================================
            // 1. UPDATE ZOHO IN .env FILE
            // =====================================================
            if ($request->filled('zoho_client_id')) {
                $this->updateEnvValue('ZOHO_CLIENT_ID', $request->zoho_client_id);
            }
            if ($request->filled('zoho_client_secret')) {
                $this->updateEnvValue('ZOHO_CLIENT_SECRET', $request->zoho_client_secret);
            }
            
            // =====================================================
            // 2. UPDATE EMAIL/SMTP IN .env FILE
            // =====================================================
            if ($request->filled('smtp_host')) {
                $this->updateEnvValue('MAIL_HOST', $request->smtp_host);
            }
            if ($request->filled('smtp_port')) {
                $this->updateEnvValue('MAIL_PORT', $request->smtp_port);
            }
            if ($request->filled('smtp_username')) {
                $this->updateEnvValue('MAIL_USERNAME', $request->smtp_username);
            }
            if ($request->filled('smtp_password')) {
                $this->updateEnvValue('MAIL_PASSWORD', $request->smtp_password);
            }
            if ($request->has('smtp_encryption')) {
                $this->updateEnvValue('MAIL_ENCRYPTION', $request->smtp_encryption ?: 'null');
            }
            if ($request->filled('mail_from_address')) {
                $this->updateEnvValue('MAIL_FROM_ADDRESS', $request->mail_from_address);
            }
            if ($request->filled('mail_from_name')) {
                // Wrap in quotes if has spaces
                $fromName = $request->mail_from_name;
                if (strpos($fromName, ' ') !== false) {
                    $fromName = '"' . $fromName . '"';
                }
                $this->updateEnvValue('MAIL_FROM_NAME', $fromName);
            }
            
            // =====================================================
            // 3. UPDATE TDS CONFIG IN .env FILE
            // =====================================================
            if ($request->filled('tds_monthly_due_date')) {
                $this->updateEnvValue('TDS_MONTHLY_DUE_DATE', $request->tds_monthly_due_date);
            }
            if ($request->filled('tds_march_due_date')) {
                $this->updateEnvValue('TDS_MARCH_DUE_DATE', $request->tds_march_due_date);
            }
            if ($request->filled('tds_warning_days')) {
                $this->updateEnvValue('TDS_WARNING_DAYS', $request->tds_warning_days);
            }
            if ($request->filled('tds_default_rate')) {
                $this->updateEnvValue('TDS_DEFAULT_RATE', $request->tds_default_rate);
            }
            if ($request->has('tds_show_dashboard_warning')) {
                $this->updateEnvValue('TDS_SHOW_DASHBOARD_WARNING', $request->tds_show_dashboard_warning ? 'true' : 'false');
            }
            if ($request->has('tds_show_invoice_warning')) {
                $this->updateEnvValue('TDS_SHOW_INVOICE_WARNING', $request->tds_show_invoice_warning ? 'true' : 'false');
            }
            
            // Clear config cache so new values take effect
            Artisan::call('config:clear');
            
            Log::info('Settings updated via .env', ['updated_by' => auth()->id()]);
            
            return response()->json([
                'success' => true,
                'message' => 'Settings saved successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Save Settings Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save settings: ' . $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // UPDATE .env FILE VALUE
    // =====================================================
    
    protected function updateEnvValue($key, $value)
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);
        
        // Escape special characters in value
        $value = str_replace('\\', '\\\\', $value);
        
        // Check if key exists
        if (preg_match("/^{$key}=.*/m", $envContent)) {
            // Update existing key
            $envContent = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                $envContent
            );
        } else {
            // Add new key at end
            $envContent .= "\n{$key}={$value}";
        }
        
        file_put_contents($envFile, $envContent);
        
        Log::info("Updated .env: {$key}");
    }

    // =====================================================
    // GET TDS CONFIG (For Dashboard Warning)
    // =====================================================
    
    public function getTdsConfig()
    {
        try {
            $settings = [
                'tds_monthly_due_date' => config('app.tds_monthly_due_date', 7),
                'tds_march_due_date' => config('app.tds_march_due_date', 30),
                'tds_warning_days' => config('app.tds_warning_days', 3),
                'tds_show_dashboard_warning' => config('app.tds_show_dashboard_warning', true),
                'tds_show_invoice_warning' => config('app.tds_show_invoice_warning', true),
            ];
            
            // Calculate TDS warning
            $warning = $this->calculateTdsWarning($settings);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'config' => $settings,
                    'warning' => $warning,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get TDS config'
            ], 500);
        }
    }

    // =====================================================
    // GET TDS CONFIG FOR SPECIFIC INVOICE
    // =====================================================
    
    public function getTdsConfigForInvoice(Request $request)
    {
        try {
            $invoiceDate = $request->input('invoice_date');
            
            if (!$invoiceDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice date is required'
                ], 400);
            }
            
            $settings = [
                'tds_monthly_due_date' => config('app.tds_monthly_due_date', 7),
                'tds_march_due_date' => config('app.tds_march_due_date', 30),
                'tds_warning_days' => config('app.tds_warning_days', 3),
                'tds_show_dashboard_warning' => config('app.tds_show_dashboard_warning', true),
                'tds_show_invoice_warning' => config('app.tds_show_invoice_warning', true),
            ];
            
            // Calculate TDS warning based on invoice date
            $warning = $this->calculateTdsWarningForInvoice($settings, $invoiceDate);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'config' => $settings,
                    'warning' => $warning,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get TDS config'
            ], 500);
        }
    }

    // =====================================================
    // CALCULATE TDS WARNING
    // =====================================================
    
    protected function calculateTdsWarning($settings)
    {
        $today = now();
        $currentMonth = $today->month;
        $currentYear = $today->year;
        
        $monthlyDueDate = (int) ($settings['tds_monthly_due_date'] ?? 7);
        $marchDueDate = (int) ($settings['tds_march_due_date'] ?? 30);
        $warningDays = (int) ($settings['tds_warning_days'] ?? 3);
        
        // TDS for previous month
        $tdsMonth = $currentMonth - 1;
        $tdsYear = $currentYear;
        
        if ($tdsMonth === 0) {
            $tdsMonth = 12;
            $tdsYear = $currentYear - 1;
        }
        
        // March FY End special case
        if ($tdsMonth === 3) {
            $dueDate = $marchDueDate;
            $dueMonth = 4;
            $dueYear = $currentYear;
        } else {
            $dueDate = $monthlyDueDate;
            $dueMonth = $currentMonth;
            $dueYear = $currentYear;
        }
        
        $dueDateObj = \Carbon\Carbon::create($dueYear, $dueMonth, $dueDate);
        $daysRemaining = $today->diffInDays($dueDateObj, false);
        
        $showWarning = false;
        $isOverdue = false;
        $message = '';
        
        if ($daysRemaining < 0) {
            $showWarning = true;
            $isOverdue = true;
            $message = "TDS payment for " . \Carbon\Carbon::create($tdsYear, $tdsMonth, 1)->format('F Y') . " is OVERDUE! Due date was " . $dueDateObj->format('M d, Y');
        } elseif ($daysRemaining <= $warningDays) {
            $showWarning = true;
            $message = "TDS payment for " . \Carbon\Carbon::create($tdsYear, $tdsMonth, 1)->format('F Y') . " is due by " . $dueDateObj->format('M d, Y') . ". " . $daysRemaining . " day(s) remaining.";
        }
        
        return [
            'show_warning' => $showWarning,
            'is_overdue' => $isOverdue,
            'message' => $message,
            'tds_month' => \Carbon\Carbon::create($tdsYear, $tdsMonth, 1)->format('F Y'),
            'due_date' => $dueDateObj->format('Y-m-d'),
            'due_date_formatted' => $dueDateObj->format('M d, Y'),
            'days_remaining' => max(0, $daysRemaining),
        ];
    }

    // =====================================================
    // CALCULATE TDS WARNING FOR SPECIFIC INVOICE
    // =====================================================
    
    protected function calculateTdsWarningForInvoice($settings, $invoiceDate)
    {
        $today = now();
        $invoiceDateObj = \Carbon\Carbon::parse($invoiceDate);
        
        // TDS is for the invoice month
        $tdsMonth = $invoiceDateObj->month;
        $tdsYear = $invoiceDateObj->year;
        
        $monthlyDueDate = (int) ($settings['tds_monthly_due_date'] ?? 7);
        $marchDueDate = (int) ($settings['tds_march_due_date'] ?? 30);
        $warningDays = (int) ($settings['tds_warning_days'] ?? 3);
        
        // Calculate due date for this invoice's TDS
        // March FY End special case
        if ($tdsMonth === 3) {
            $dueDate = $marchDueDate;
            $dueMonth = 4;
            $dueYear = $tdsYear;
        } else {
            // TDS for invoice month is due on 7th of NEXT month
            $dueDate = $monthlyDueDate;
            $dueMonth = $tdsMonth + 1;
            $dueYear = $tdsYear;
            
            // Handle December → January of next year
            if ($dueMonth > 12) {
                $dueMonth = 1;
                $dueYear = $tdsYear + 1;
            }
        }
        
        $dueDateObj = \Carbon\Carbon::create($dueYear, $dueMonth, $dueDate);
        $daysRemaining = $today->diffInDays($dueDateObj, false);
        
        $showWarning = false;
        $isOverdue = false;
        $message = '';
        
        if ($daysRemaining < 0) {
            // OVERDUE
            $showWarning = true;
            $isOverdue = true;
            $overdueDays = abs($daysRemaining);
            $message = "TDS payment for " . $invoiceDateObj->format('F Y') . " is OVERDUE by {$overdueDays} day(s)! Due date was " . $dueDateObj->format('M d, Y');
        } elseif ($daysRemaining <= $warningDays) {
            // Within warning period (including day 0 = today is due date)
            $showWarning = true;
            if ($daysRemaining == 0) {
                $message = "TDS payment for " . $invoiceDateObj->format('F Y') . " is due TODAY (" . $dueDateObj->format('M d, Y') . ")";
            } else {
                $message = "TDS payment for " . $invoiceDateObj->format('F Y') . " is due by " . $dueDateObj->format('M d, Y') . ". " . $daysRemaining . " day(s) remaining.";
            }
        }
        
        return [
            'show_warning' => $showWarning,
            'is_overdue' => $isOverdue,
            'message' => $message,
            'tds_month' => $invoiceDateObj->format('F Y'),
            'due_date' => $dueDateObj->format('Y-m-d'),
            'due_date_formatted' => $dueDateObj->format('M d, Y'),
            'days_remaining' => max(0, $daysRemaining),
        ];
    }

    // =====================================================
    // TEST EMAIL CONNECTION
    // =====================================================
    
    public function testEmail(Request $request)
    {
        try {
            $testEmail = config('mail.from.address');
            
            if (!$testEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'No from address configured'
                ], 400);
            }
            
            Mail::raw('This is a test email from Vendor Portal Settings.', function ($message) use ($testEmail) {
                $message->to($testEmail)
                    ->subject('Test Email - Vendor Portal');
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Test email sent to ' . $testEmail
            ]);
            
        } catch (\Exception $e) {
            Log::error('Test Email Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Email test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // CHECK ZOHO CONNECTION
    // =====================================================
    
    protected function checkZohoConnection()
    {
        try {
            $token = \App\Models\ZohoToken::getActive();
            return $token && !$token->isExpired();
        } catch (\Exception $e) {
            return false;
        }
    }
}