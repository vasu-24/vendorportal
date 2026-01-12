<?php

namespace App\Services;

use App\Models\ZohoToken;
use App\Models\Vendor;
use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use App\Models\TravelInvoice;

class ZohoService
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $accountsUrl;
    protected $apiUrl;
    protected $scopes;

    public function __construct()
    {
        $this->clientId = config('zoho.client_id');
        $this->clientSecret = config('zoho.client_secret');
        $this->redirectUri = config('zoho.redirect_uri');
        $this->accountsUrl = config('zoho.accounts_url');
        $this->apiUrl = config('zoho.api_url');
        $this->scopes = config('zoho.scopes');
    }

    /**
     * Get OAuth URL for Zoho authorization
     */
    public function getAuthUrl(): string
    {
        $scopes = implode(',', $this->scopes);
        
        $params = http_build_query([
            'scope' => $scopes,
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'access_type' => 'offline',
            'prompt' => 'consent',
        ]);

        return "{$this->accountsUrl}/oauth/v2/auth?{$params}";
    }

    /**
     * Exchange authorization code for access token
     */
    public function getAccessToken(string $code): array
    {
        $response = Http::asForm()->post("{$this->accountsUrl}/oauth/v2/token", [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'code' => $code,
        ]);

        if ($response->failed()) {
            Log::error('Zoho Token Error', ['response' => $response->json()]);
            throw new Exception('Failed to get access token from Zoho');
        }

        $data = $response->json();
        $this->saveToken($data);

        return $data;
    }

    /**
     * Refresh access token
     */
    public function refreshAccessToken(): ?ZohoToken
    {
        $token = ZohoToken::getActive();

        if (!$token || !$token->refresh_token) {
            Log::error('No refresh token available');
            return null;
        }

        $response = Http::asForm()->post("{$this->accountsUrl}/oauth/v2/token", [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $token->refresh_token,
        ]);

        if ($response->failed()) {
            Log::error('Zoho Refresh Token Error', ['response' => $response->json()]);
            return null;
        }

        $data = $response->json();

        $token->update([
            'access_token' => $data['access_token'],
            'expires_in' => $data['expires_in'] ?? 3600,
            'expires_at' => Carbon::now()->addSeconds($data['expires_in'] ?? 3600),
        ]);

        return $token;
    }

    /**
     * Save token to database
     */
    protected function saveToken(array $data): ZohoToken
    {
        ZohoToken::where('is_active', true)->update(['is_active' => false]);

        return ZohoToken::create([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'token_type' => $data['token_type'] ?? 'Bearer',
            'expires_in' => $data['expires_in'] ?? 3600,
            'expires_at' => Carbon::now()->addSeconds($data['expires_in'] ?? 3600),
            'is_active' => true,
        ]);
    }

    /**
     * Get valid access token (refresh if expired)
     */
    public function getValidToken(): ?string
    {
        $token = ZohoToken::getActive();

        if (!$token) {
            return null;
        }

        if ($token->isExpired()) {
            $token = $this->refreshAccessToken();
        }

        return $token?->access_token;
    }

    /**
     * Check if Zoho is connected
     */
    public function isConnected(): bool
    {
        return ZohoToken::getActive() !== null;
    }

    /**
     * Get organizations from Zoho
     */
    public function getOrganizations(): array
    {
        $accessToken = $this->getValidToken();

        if (!$accessToken) {
            throw new Exception('Not connected to Zoho');
        }

        $response = Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$accessToken}",
        ])->get("{$this->apiUrl}/books/v3/organizations");

        if ($response->failed()) {
            Log::error('Zoho Get Organizations Error', ['response' => $response->json()]);
            throw new Exception('Failed to get organizations from Zoho');
        }

        return $response->json()['organizations'] ?? [];
    }

    /**
     * Set organization ID
     */
    public function setOrganizationId(string $orgId): void
    {
        $token = ZohoToken::getActive();
        if ($token) {
            $token->update(['organization_id' => $orgId]);
        }
    }

    /**
     * Get organization ID
     */
    public function getOrganizationId(): ?string
    {
        return ZohoToken::getActive()?->organization_id;
    }

    // =====================================================
    // VENDOR METHODS
    // =====================================================

    /**
     * Create vendor in Zoho Books
     */
    public function createVendor(Vendor $vendor): array
    {
        $accessToken = $this->getValidToken();
        $organizationId = $this->getOrganizationId();

        if (!$accessToken) {
            throw new Exception('Not connected to Zoho');
        }

        if (!$organizationId) {
            throw new Exception('Organization ID not set');
        }

        // Load related data
        $vendor->load(['companyInfo', 'contact', 'statutoryInfo', 'bankDetails']);

        // SIMPLIFIED - Only basic fields
        $zohoData = [
            'contact_name' => $vendor->vendor_name ?? 'Unknown Vendor',
            'contact_type' => 'vendor',
        ];

        // Company Name
        if (!empty($vendor->companyInfo) && !empty($vendor->companyInfo->legal_entity_name)) {
            $zohoData['company_name'] = $vendor->companyInfo->legal_entity_name;
        } else {
            $zohoData['company_name'] = $vendor->vendor_name ?? '';
        }

        // Email
        if (!empty($vendor->vendor_email)) {
            $zohoData['email'] = $vendor->vendor_email;
        }

        // Phone
        if (!empty($vendor->contact) && !empty($vendor->contact->mobile)) {
            $zohoData['phone'] = $vendor->contact->mobile;
        }

        // Website
        if (!empty($vendor->companyInfo) && !empty($vendor->companyInfo->website)) {
            $zohoData['website'] = $vendor->companyInfo->website;
        }

        // Billing Address
        if (!empty($vendor->companyInfo) && !empty($vendor->companyInfo->registered_address)) {
            $zohoData['billing_address'] = [
                'address' => $vendor->companyInfo->registered_address,
            ];
        }

        // Contact Person
        if (!empty($vendor->contact) && !empty($vendor->contact->contact_person)) {
            $zohoData['contact_persons'] = [
                [
                    'first_name' => $vendor->contact->contact_person,
                    'email' => $vendor->contact->email ?? $vendor->vendor_email ?? '',
                    'phone' => $vendor->contact->mobile ?? '',
                    'is_primary_contact' => true,
                ],
            ];
        }

        // Notes (put GSTIN & PAN in notes)
        $notes = [];
        if (!empty($vendor->statutoryInfo) && !empty($vendor->statutoryInfo->gstin)) {
            $notes[] = 'GSTIN: ' . $vendor->statutoryInfo->gstin;
        }
        if (!empty($vendor->statutoryInfo) && !empty($vendor->statutoryInfo->pan_number)) {
            $notes[] = 'PAN: ' . $vendor->statutoryInfo->pan_number;
        }
        if (!empty($notes)) {
            $zohoData['notes'] = implode(' | ', $notes);
        }

        Log::info('Sending Vendor to Zoho', ['vendor_id' => $vendor->id, 'data' => $zohoData]);

        $response = Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$accessToken}",
            'Content-Type' => 'application/json',
        ])->post("{$this->apiUrl}/books/v3/contacts?organization_id={$organizationId}", $zohoData);

        $result = $response->json();

        if ($response->failed() || ($result['code'] ?? 0) !== 0) {
            Log::error('Zoho Create Vendor Error', [
                'vendor_id' => $vendor->id,
                'response' => $result,
                'sent_data' => $zohoData,
            ]);
            throw new Exception($result['message'] ?? 'Failed to create vendor in Zoho');
        }

        // Update vendor with Zoho contact ID
        $zohoContactId = $result['contact']['contact_id'] ?? null;
        
        if ($zohoContactId) {
            $vendor->update([
                'zoho_contact_id' => $zohoContactId,
                'zoho_synced_at' => Carbon::now(),
            ]);

            Log::info('Vendor created in Zoho', [
                'vendor_id' => $vendor->id,
                'zoho_contact_id' => $zohoContactId,
            ]);
        }

        return $result;
    }


/**
 * Search for a vendor in Zoho by GSTIN, PAN, or name
 */
public function findVendorInZoho(Vendor $vendor): ?string
{
    $accessToken = $this->getValidToken();
    $organizationId = $this->getOrganizationId();

    if (!$accessToken || !$organizationId) {
        throw new Exception('Not connected to Zoho');
    }

    // Priority: GSTIN -> PAN -> Email -> Name
    $searchParams = [
        $vendor->statutoryInfo->gstin ?? null,
        $vendor->statutoryInfo->pan_number ?? null,
        $vendor->vendor_email ?? null,
        $vendor->vendor_name,
    ];

    foreach ($searchParams as $search) {
        if (!$search) continue;
        $url = "{$this->apiUrl}/books/v3/contacts?organization_id={$organizationId}&search_text=" . urlencode($search);

        $response = Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$accessToken}",
        ])->get($url);

        if ($response->failed()) continue;

        $result = $response->json();
        $contacts = $result['contacts'] ?? [];
        foreach ($contacts as $contact) {
            // Match further on GST/PAN if possible
            if (!empty($vendor->statutoryInfo->gstin) && isset($contact['gst_treatment']) && strpos($contact['notes'] ?? '', $vendor->statutoryInfo->gstin) !== false) {
                return $contact['contact_id'];
            }
            if (!empty($vendor->statutoryInfo->pan_number) && strpos($contact['notes'] ?? '', $vendor->statutoryInfo->pan_number) !== false) {
                return $contact['contact_id'];
            }
            if (!empty($vendor->vendor_email) && $contact['email'] === $vendor->vendor_email) {
                return $contact['contact_id'];
            }
            if ($contact['contact_name'] === $vendor->vendor_name) {
                return $contact['contact_id'];
            }
        }
    }
    return null; // Not found
}




    /**
     * Update vendor in Zoho Books
     */
    public function updateVendor(Vendor $vendor): array
    {
        if (!$vendor->zoho_contact_id) {
            return $this->createVendor($vendor);
        }

        $accessToken = $this->getValidToken();
        $organizationId = $this->getOrganizationId();

        if (!$accessToken) {
            throw new Exception('Not connected to Zoho');
        }

        $vendor->load(['companyInfo', 'contact', 'statutoryInfo']);

        $zohoData = [
            'contact_name' => $vendor->vendor_name,
        ];

        if (!empty($vendor->companyInfo) && !empty($vendor->companyInfo->legal_entity_name)) {
            $zohoData['company_name'] = $vendor->companyInfo->legal_entity_name;
        }

        if (!empty($vendor->vendor_email)) {
            $zohoData['email'] = $vendor->vendor_email;
        }

        if (!empty($vendor->contact) && !empty($vendor->contact->mobile)) {
            $zohoData['phone'] = $vendor->contact->mobile;
        }

        $response = Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$accessToken}",
            'Content-Type' => 'application/json',
        ])->put(
            "{$this->apiUrl}/books/v3/contacts/{$vendor->zoho_contact_id}?organization_id={$organizationId}",
            $zohoData
        );

        $result = $response->json();

        if ($response->failed() || ($result['code'] ?? 0) !== 0) {
            Log::error('Zoho Update Vendor Error', [
                'vendor_id' => $vendor->id,
                'response' => $result,
            ]);
            throw new Exception($result['message'] ?? 'Failed to update vendor in Zoho');
        }

        $vendor->update(['zoho_synced_at' => Carbon::now()]);

        return $result;
    }

    /**
     * Get vendor from Zoho Books
     */
    public function getVendor(string $zohoContactId): array
    {
        $accessToken = $this->getValidToken();
        $organizationId = $this->getOrganizationId();

        if (!$accessToken) {
            throw new Exception('Not connected to Zoho');
        }

        $response = Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$accessToken}",
        ])->get("{$this->apiUrl}/books/v3/contacts/{$zohoContactId}?organization_id={$organizationId}");

        if ($response->failed()) {
            throw new Exception('Failed to get vendor from Zoho');
        }

        return $response->json();
    }

    /**
     * Delete vendor from Zoho Books
     */
    public function deleteVendor(string $zohoContactId): bool
    {
        $accessToken = $this->getValidToken();
        $organizationId = $this->getOrganizationId();

        if (!$accessToken) {
            throw new Exception('Not connected to Zoho');
        }

        $response = Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$accessToken}",
        ])->delete("{$this->apiUrl}/books/v3/contacts/{$zohoContactId}?organization_id={$organizationId}");

        return $response->successful();
    }

    // =====================================================
    // BILL (INVOICE) METHODS - NEW!
    // =====================================================

    /**
     * 🔥 Create Bill in Zoho Books
     * Called when admin approves an invoice
     */

    public function createBill(Invoice $invoice): array
{
    $accessToken = $this->getValidToken();
    $organizationId = $this->getOrganizationId();

    if (!$accessToken) {
        throw new Exception('Not connected to Zoho');
    }

    if (!$organizationId) {
        throw new Exception('Organization ID not set');
    }

    // Load related data
    $invoice->load(['vendor', 'items', 'items.category', 'contract']);

    // Check if vendor exists
    if (!$invoice->vendor) {
        throw new Exception('Invoice has no vendor');
    }

    // If vendor not synced to Zoho, search first then create
    if (!$invoice->vendor->zoho_contact_id) {
        $zohoContactId = $this->findVendorInZoho($invoice->vendor);
        
        if ($zohoContactId) {
            $invoice->vendor->update([
                'zoho_contact_id' => $zohoContactId,
                'zoho_synced_at' => now(),
            ]);
            Log::info('Vendor found in Zoho, linked locally', [
                'vendor_id' => $invoice->vendor->id,
                'zoho_contact_id' => $zohoContactId,
            ]);
        } else {
            Log::info('Vendor not in Zoho, creating first', ['vendor_id' => $invoice->vendor->id]);
            $this->createVendor($invoice->vendor);
        }
        $invoice->vendor->refresh();
    }

    // Default account as fallback
    $defaultAccountId = config('zoho.expense_account_id');

    // -----------------------------------------------------
    // LINE ITEMS - TDS APPLIES ON BASE AMOUNT ONLY!
    // -----------------------------------------------------
    $lineItems = [];
    foreach ($invoice->items as $item) {
        // Use category's zoho_account_id, fallback to default
        $accountId = $item->category->zoho_account_id ?? $defaultAccountId;
        
        $lineItems[] = [
            'account_id' => $accountId,
            'name' => $item->category->name ?? $item->particulars ?? 'Item',
            'description' => $item->particulars ?? '',
            'quantity' => (float) $item->quantity,
            'rate' => (float) $item->rate,  // Base rate (TDS applicable)
            'tax_percentage' => (float) ($item->tax_percent ?? 0),
            'is_tds_applicable' => true,  // TDS applies on base rate
        ];
    }

    // If no line items, create one from totals
    if (empty($lineItems)) {
        $lineItems[] = [
            'account_id' => $defaultAccountId,
            'name' => 'Invoice Amount',
            'description' => $invoice->description ?? '',
            'quantity' => 1,
            'rate' => (float) $invoice->base_total,  // Base total (TDS applicable)
            'tax_percentage' => $invoice->base_total > 0 
                ? round(($invoice->gst_total / $invoice->base_total) * 100, 2) 
                : 0,
            'is_tds_applicable' => true,  // TDS applies on base amount
        ];
    }

   
// Build bill data
// 🔥 USE ZOHO_SYNC_DATE IF AVAILABLE (CHANGED BY FINANCE), OTHERWISE USE INVOICE_DATE
$billDate = $invoice->zoho_sync_date ?? $invoice->invoice_date;

$zohoData = [
    'vendor_id' => $invoice->vendor->zoho_contact_id,
    'bill_number' => $invoice->invoice_number,
    'date' => $billDate ? \Carbon\Carbon::parse($billDate)->format('Y-m-d') : now()->format('Y-m-d'),
    'due_date' => $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : now()->addDays(30)->format('Y-m-d'),
    'line_items' => $lineItems,
    'reference_number' => $invoice->invoice_number,
];

// -----------------------------------------------------
// TDS CONFIGURATION
// TDS is applied on BASE AMOUNT only
// We pass EXPLICIT tds_amount to override Zoho's calculation!
// -----------------------------------------------------
if (!empty($invoice->zoho_tds_tax_id)) {
    $zohoData['is_tds_applied'] = true;
    $zohoData['tds_tax_id'] = $invoice->zoho_tds_tax_id;
    
    // =====================================================
    // EXPLICITLY SET TDS AMOUNT (Calculated on BASE only!)
    // This overrides Zoho's default calculation
    // =====================================================
    $baseAmount = (float) ($invoice->base_total ?? 0);
    $tdsPercent = (float) ($invoice->tds_percent ?? 0);
    
    if ($tdsPercent > 0 && $baseAmount > 0) {
        $tdsAmount = round(($baseAmount * $tdsPercent) / 100, 2);
        $zohoData['tds_amount'] = $tdsAmount;
        
        Log::info('TDS Calculation for Zoho (Regular Invoice)', [
            'invoice_id' => $invoice->id,
            'base_amount' => $baseAmount,
            'tds_percent' => $tdsPercent,
            'tds_amount' => $tdsAmount,
        ]);
    }
}



// 🔥 PROFESSIONAL NOTES
$notes = [];

$notes[] = "═══════════════════════════════════";

if ($invoice->contract) {
    if ($invoice->contract->contract_type === 'adhoc') {
        $notes[] = " ADHOC CONTRACT INVOICE";
        $notes[] = "═══════════════════════════════════";
        $notes[] = "";
        $notes[] = "Contract #: {$invoice->contract->contract_number}";
        $notes[] = "SOW Value: ₹" . number_format($invoice->contract->sow_value ?? 0, 2);
        $notes[] = "Invoice Type: Ad-hoc / One-time Service";
    } else {
        $notes[] = " STANDARD CONTRACT INVOICE";
        $notes[] = "═══════════════════════════════════";
        $notes[] = "";
        $notes[] = "Contract #: {$invoice->contract->contract_number}";
        $notes[] = "Contract Value: ₹" . number_format($invoice->contract->contract_value ?? 0, 2);
    }
} else {
    $notes[] = " VENDOR INVOICE";
    $notes[] = "═══════════════════════════════════";
}

$notes[] = "";
$notes[] = "Vendor Invoice #: {$invoice->invoice_number}";
$notes[] = "Invoice Date: " . ($invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : now()->format('d M Y'));

if ($invoice->due_date) {
    $notes[] = "Due Date: " . $invoice->due_date->format('d M Y');
}

if ($invoice->description) {
    $notes[] = "";
    $notes[] = "Description: {$invoice->description}";
}

$notes[] = "";
$notes[] = "───────────────────────────────────";
$notes[] = "Generated via Vendor Portal";

$zohoData['notes'] = implode("\n", $notes);




    Log::info('Creating Bill in Zoho', [
        'invoice_id' => $invoice->id,
        'invoice_number' => $invoice->invoice_number,
        'vendor_zoho_id' => $invoice->vendor->zoho_contact_id,
        'contract_type' => $invoice->contract->contract_type ?? 'none',
        'data' => $zohoData,
    ]);

    $response = Http::withHeaders([
        'Authorization' => "Zoho-oauthtoken {$accessToken}",
        'Content-Type' => 'application/json',
    ])->post("{$this->apiUrl}/books/v3/bills?organization_id={$organizationId}", $zohoData);

    $result = $response->json();

    if ($response->failed() || ($result['code'] ?? 0) !== 0) {
        Log::error('Zoho Create Bill Error', [
            'invoice_id' => $invoice->id,
            'response' => $result,
            'sent_data' => $zohoData,
        ]);
        throw new Exception($result['message'] ?? 'Failed to create bill in Zoho');
    }

    // Update invoice with Zoho bill ID
    $zohoBillId = $result['bill']['bill_id'] ?? null;

    if ($zohoBillId) {
        $invoice->update([
            'zoho_invoice_id' => $zohoBillId,
            'zoho_synced_at' => Carbon::now(),
        ]);

        Log::info('Bill created in Zoho', [
            'invoice_id' => $invoice->id,
            'zoho_bill_id' => $zohoBillId,
        ]);
    }

    return $result;
}



    /**
     * Get Bill from Zoho Books
     */
    public function getBill(string $zohoBillId): array
    {
        $accessToken = $this->getValidToken();
        $organizationId = $this->getOrganizationId();

        if (!$accessToken) {
            throw new Exception('Not connected to Zoho');
        }

        $response = Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$accessToken}",
        ])->get("{$this->apiUrl}/books/v3/bills/{$zohoBillId}?organization_id={$organizationId}");

        if ($response->failed()) {
            throw new Exception('Failed to get bill from Zoho');
        }

        return $response->json();
    }

    /**
     * Get Bill Status from Zoho
     */
    public function getBillStatus(string $zohoBillId): ?string
    {
        try {
            $result = $this->getBill($zohoBillId);
            return $result['bill']['status'] ?? null;
        } catch (Exception $e) {
            Log::error('Failed to get bill status', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * 🔥 Sync Bill Payment Status from Zoho
     * Check if bill is paid in Zoho and update local invoice
     */
    public function syncBillStatus(Invoice $invoice): bool
    {
        if (!$invoice->zoho_invoice_id) {
            Log::warning('Invoice not synced to Zoho', ['invoice_id' => $invoice->id]);
            return false;
        }

        try {
            $result = $this->getBill($invoice->zoho_invoice_id);
            $zohoBill = $result['bill'] ?? null;

            if (!$zohoBill) {
                return false;
            }

            $zohoStatus = $zohoBill['status'] ?? '';
            $balanceDue = (float) ($zohoBill['balance'] ?? 0);

            Log::info('Zoho Bill Status', [
                'invoice_id' => $invoice->id,
                'zoho_status' => $zohoStatus,
                'balance_due' => $balanceDue,
            ]);

            // If paid in Zoho, update local status
            if ($zohoStatus === 'paid' || $balanceDue == 0) {
                if ($invoice->status !== 'paid') {
                    $invoice->update([
                        'status' => 'paid',
                        'paid_at' => Carbon::now(),
                        'zoho_synced_at' => Carbon::now(),
                    ]);

                    Log::info('Invoice marked as paid from Zoho sync', [
                        'invoice_id' => $invoice->id,
                    ]);
                }
                return true;
            }

            // Update sync timestamp
            $invoice->update(['zoho_synced_at' => Carbon::now()]);

            return true;

        } catch (Exception $e) {
            Log::error('Failed to sync bill status', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * 🔥 Sync all pending invoices from Zoho
     * Run this via cron job to keep status updated
     */
    public function syncAllPendingBills(): array
    {
        $results = [
            'total' => 0,
            'synced' => 0,
            'paid' => 0,
            'failed' => 0,
        ];

        // Get all approved invoices that are synced to Zoho but not paid yet
        $invoices = Invoice::whereNotNull('zoho_invoice_id')
            ->where('status', 'approved')
            ->get();

        $results['total'] = $invoices->count();

        foreach ($invoices as $invoice) {
            try {
                $synced = $this->syncBillStatus($invoice);
                
                if ($synced) {
                    $results['synced']++;
                    
                    if ($invoice->fresh()->status === 'paid') {
                        $results['paid']++;
                    }
                }
            } catch (Exception $e) {
                $results['failed']++;
                Log::error('Failed to sync invoice', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Zoho Bill Sync Complete', $results);

        return $results;
    }



/**
 * Sync payment status for TravelInvoice (for zoho_bill_id)
 */
public function syncTravelBillStatus(\App\Models\TravelInvoice $invoice): bool
{
    if (!$invoice->zoho_bill_id) {
        \Log::warning('TravelInvoice not synced to Zoho', ['invoice_id' => $invoice->id]);
        return false;
    }
    try {
        $result = $this->getBill($invoice->zoho_bill_id);
        $zohoBill = $result['bill'] ?? null;
        if (!$zohoBill) return false;

        $zohoStatus = $zohoBill['status'] ?? '';
        $balanceDue = (float) ($zohoBill['balance'] ?? 0);

        if ($zohoStatus === 'paid' || $balanceDue == 0) {
            if ($invoice->status !== 'paid') {
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'zoho_synced_at' => now(),
                ]);
            }
            return true;
        }
        $invoice->update(['zoho_synced_at' => now()]);
        return true;
    } catch (\Exception $e) {
        \Log::error('Failed to sync travel bill status', [
            'invoice_id' => $invoice->id,
            'error' => $e->getMessage(),
        ]);
        return false;
    }
}



    /**
     * Update Bill in Zoho
     */
    public function updateBill(Invoice $invoice): array
    {
        if (!$invoice->zoho_invoice_id) {
            return $this->createBill($invoice);
        }

        $accessToken = $this->getValidToken();
        $organizationId = $this->getOrganizationId();

        if (!$accessToken) {
            throw new Exception('Not connected to Zoho');
        }

        $invoice->load(['items', 'items.category']);

        // -----------------------------------------------------
        // LINE ITEMS - TDS APPLIES ON BASE AMOUNT ONLY!
        // -----------------------------------------------------
        $lineItems = [];
        foreach ($invoice->items as $item) {
            $lineItems[] = [
                'name' => $item->category->name ?? $item->particulars ?? 'Item',
                'description' => $item->particulars ?? '',
                'quantity' => (float) $item->quantity,
                'rate' => (float) $item->rate,  // Base rate (TDS applicable)
                'tax_percentage' => (float) ($item->tax_percent ?? 0),
                'is_tds_applicable' => true,  // TDS applies on base rate only
            ];
        }

        $zohoData = [
            'bill_number' => $invoice->invoice_number,
            'date' => $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : now()->format('Y-m-d'),
            'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : now()->addDays(30)->format('Y-m-d'),
            'line_items' => $lineItems,
            'notes' => $invoice->description ?? '',
        ];

        // -----------------------------------------------------
        // TDS CONFIGURATION
        // TDS is applied on BASE AMOUNT only
        // We pass EXPLICIT tds_amount to override Zoho's calculation!
        // -----------------------------------------------------
        if (!empty($invoice->zoho_tds_tax_id)) {
            $zohoData['is_tds_applied'] = true;
            $zohoData['tds_tax_id'] = $invoice->zoho_tds_tax_id;
            
            // EXPLICITLY SET TDS AMOUNT
            $baseAmount = (float) ($invoice->base_total ?? 0);
            $tdsPercent = (float) ($invoice->tds_percent ?? 0);
            
            if ($tdsPercent > 0 && $baseAmount > 0) {
                $tdsAmount = round(($baseAmount * $tdsPercent) / 100, 2);
                $zohoData['tds_amount'] = $tdsAmount;
            }
        }

        $response = Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$accessToken}",
            'Content-Type' => 'application/json',
        ])->put(
            "{$this->apiUrl}/books/v3/bills/{$invoice->zoho_invoice_id}?organization_id={$organizationId}",
            $zohoData
        );

        $result = $response->json();

        if ($response->failed() || ($result['code'] ?? 0) !== 0) {
            Log::error('Zoho Update Bill Error', [
                'invoice_id' => $invoice->id,
                'response' => $result,
            ]);
            throw new Exception($result['message'] ?? 'Failed to update bill in Zoho');
        }

        $invoice->update(['zoho_synced_at' => Carbon::now()]);

        return $result;
    }

    /**
     * Delete Bill from Zoho
     */
    public function deleteBill(string $zohoBillId): bool
    {
        $accessToken = $this->getValidToken();
        $organizationId = $this->getOrganizationId();

        if (!$accessToken) {
            throw new Exception('Not connected to Zoho');
        }

        $response = Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$accessToken}",
        ])->delete("{$this->apiUrl}/books/v3/bills/{$zohoBillId}?organization_id={$organizationId}");

        return $response->successful();
    }


/**
 * Zoho doesn't expose TDS via official API.
 * These are organization-specific and rarely change.
 */
public function getTdsTaxes(): array
{
    return config('zoho_tds_rates', []);
}



    /**
     * Get bill payments from Zoho
     */
    public function getBillPayments(string $zohoBillId): array
    {
        $accessToken = $this->getValidToken();
        $organizationId = $this->getOrganizationId();

        if (!$accessToken) {
            throw new Exception('Not connected to Zoho');
        }

        $response = Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$accessToken}",
        ])->get("{$this->apiUrl}/books/v3/bills/{$zohoBillId}/payments?organization_id={$organizationId}");

        if ($response->failed()) {
            throw new Exception('Failed to get bill payments from Zoho');
        }

        return $response->json();
    }

    /**
     * Disconnect Zoho (revoke tokens)
     */
    public function disconnect(): void
    {
        $token = ZohoToken::getActive();

        if ($token) {
            Http::asForm()->post("{$this->accountsUrl}/oauth/v2/token/revoke", [
                'token' => $token->refresh_token,
            ]);

            $token->update(['is_active' => false]);
        }
    }
    // =====================================================
    // CHART OF ACCOUNTS METHODS - NEW!
    // =====================================================

   
   /**
 * Get Chart of Accounts from Zoho Books
 * Returns ONLY Expense type accounts
 */
public function getChartOfAccounts(string $accountType = null): array
{
    $accessToken = $this->getValidToken();
    $organizationId = $this->getOrganizationId();

    if (!$accessToken) {
        throw new Exception('Not connected to Zoho');
    }

    if (!$organizationId) {
        throw new Exception('Organization ID not set');
    }

    $url = "{$this->apiUrl}/books/v3/chartofaccounts?organization_id={$organizationId}";

    $response = Http::withHeaders([
        'Authorization' => "Zoho-oauthtoken {$accessToken}",
    ])->get($url);

    if ($response->failed()) {
        Log::error('Zoho Get Chart of Accounts Error', ['response' => $response->json()]);
        throw new Exception('Failed to get chart of accounts from Zoho');
    }

    $result = $response->json();
    $allAccounts = $result['chartofaccounts'] ?? [];

    // 🔥 FILTER ONLY EXPENSE ACCOUNTS
    $expenseTypes = ['expense', 'other_expense', 'cost_of_goods_sold'];

    $filteredAccounts = [];
    foreach ($allAccounts as $account) {
        $type = strtolower($account['account_type'] ?? '');
        if (in_array($type, $expenseTypes)) {
            $filteredAccounts[] = $account;
        }
    }

    Log::info('Fetched Expense Accounts from Zoho', [
        'total' => count($allAccounts),
        'filtered' => count($filteredAccounts),
    ]);

    return $filteredAccounts;
}

  /**
 * Get only Expense accounts from Zoho
 * Filters: expense, other_expense, cost_of_goods_sold
 */
public function getExpenseAccounts(): array
{
    $accessToken = $this->getValidToken();
    $organizationId = $this->getOrganizationId();

    if (!$accessToken) {
        throw new Exception('Not connected to Zoho');
    }

    if (!$organizationId) {
        throw new Exception('Organization ID not set');
    }

    $url = "{$this->apiUrl}/books/v3/chartofaccounts?organization_id={$organizationId}";

    $response = Http::withHeaders([
        'Authorization' => "Zoho-oauthtoken {$accessToken}",
    ])->get($url);

    if ($response->failed()) {
        Log::error('Zoho Get Chart of Accounts Error', ['response' => $response->json()]);
        throw new Exception('Failed to get chart of accounts from Zoho');
    }

    $result = $response->json();
    $allAccounts = $result['chartofaccounts'] ?? [];

    // Filter only Expense type accounts
    $expenseTypes = [
        'expense',
        'other_expense',
        'cost_of_goods_sold',
    ];

    $filteredAccounts = array_filter($allAccounts, function($account) use ($expenseTypes) {
        $accountType = strtolower($account['account_type'] ?? '');
        return in_array($accountType, $expenseTypes);
    });

    Log::info('Fetched Expense Accounts from Zoho', [
        'total' => count($allAccounts),
        'filtered' => count($filteredAccounts),
    ]);

    return array_values($filteredAccounts); // Re-index array
}

    /**
     * Get single account details from Zoho
     */
    public function getAccount(string $accountId): array
    {
        $accessToken = $this->getValidToken();
        $organizationId = $this->getOrganizationId();

        if (!$accessToken) {
            throw new Exception('Not connected to Zoho');
        }

        $response = Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$accessToken}",
        ])->get("{$this->apiUrl}/books/v3/chartofaccounts/{$accountId}?organization_id={$organizationId}");

        if ($response->failed()) {
            throw new Exception('Failed to get account from Zoho');
        }

        return $response->json()['account'] ?? [];
    }

    // =====================================================
    // TAX METHODS (TDS & GST) - NEW!
    // =====================================================

    /**
     * 🔥 Get all Taxes from Zoho Books
     * Used for TDS & GST dropdowns in admin panel
     */
    public function getTaxes(): array
    {
        $accessToken = $this->getValidToken();
        $organizationId = $this->getOrganizationId();

        if (!$accessToken) {
            throw new Exception('Not connected to Zoho');
        }

        if (!$organizationId) {
            throw new Exception('Organization ID not set');
        }

        $response = Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$accessToken}",
        ])->get("{$this->apiUrl}/books/v3/settings/taxes?organization_id={$organizationId}");

        if ($response->failed()) {
            Log::error('Zoho Get Taxes Error', ['response' => $response->json()]);
            throw new Exception('Failed to get taxes from Zoho');
        }

        $result = $response->json();

        Log::info('Fetched Taxes from Zoho', [
            'count' => count($result['taxes'] ?? []),
        ]);

        return $result['taxes'] ?? [];
    }

    /**
     * Get Tax Groups from Zoho (TDS groups like 194C, 194J)
     */
    public function getTaxGroups(): array
    {
        $accessToken = $this->getValidToken();
        $organizationId = $this->getOrganizationId();

        if (!$accessToken) {
            throw new Exception('Not connected to Zoho');
        }

        if (!$organizationId) {
            throw new Exception('Organization ID not set');
        }

        $response = Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$accessToken}",
        ])->get("{$this->apiUrl}/books/v3/settings/taxgroups?organization_id={$organizationId}");

        if ($response->failed()) {
            Log::error('Zoho Get Tax Groups Error', ['response' => $response->json()]);
            throw new Exception('Failed to get tax groups from Zoho');
        }

        return $response->json()['tax_groups'] ?? [];
    }

    /**
     * Get single tax details from Zoho
     */
    public function getTax(string $taxId): array
    {
        $accessToken = $this->getValidToken();
        $organizationId = $this->getOrganizationId();

        if (!$accessToken) {
            throw new Exception('Not connected to Zoho');
        }

        $response = Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$accessToken}",
        ])->get("{$this->apiUrl}/books/v3/settings/taxes/{$taxId}?organization_id={$organizationId}");

        if ($response->failed()) {
            throw new Exception('Failed to get tax from Zoho');
        }

        return $response->json()['tax'] ?? [];
    }
public function getFormattedTaxes(): array
{
    // GST Taxes (from /settings/taxes)
    $taxes = $this->getTaxes();
    $gstTaxes = [];
    foreach ($taxes as $tax) {
        $gstTaxes[] = [
            'tax_id' => $tax['tax_id'] ?? '',
            'tax_name' => $tax['tax_name'] ?? '',
            'tax_percentage' => $tax['tax_percentage'] ?? 0,
            'tax_type' => $tax['tax_type'] ?? '',
        ];
    }

    // TDS Taxes (from /settings/incometaxes)
    $tdsRaw = $this->getTdsTaxes();
    $tdsTaxes = [];
    foreach ($tdsRaw as $tds) {
        $tdsTaxes[] = [
            'tax_id'        => $tds['tax_id'] ?? '',                  // TDS tax id
            'tax_name'      => $tds['tax_name'] ?? '',                // "TDS - Contractors (194C) - 1%"
            'tax_percentage'=> $tds['tax_percentage'] ?? 0,
            'account_id'    => $tds['account_id'] ?? '',              // Needed for bill creation
            'section'       => $tds['section'] ?? '',                 // "194C"
            'is_active'     => $tds['is_active'] ?? false,
        ];
    }

    return [
        'gst' => $gstTaxes,
        'tds' => $tdsTaxes,   // From API!
        'all' => $taxes,
    ];
}





/**
 * 🔥 Create Travel Invoice Bill in Zoho Books
 * Called when Finance approves a travel invoice
 */
public function createTravelBill(TravelInvoice $invoice): array
{
    $accessToken = $this->getValidToken();
    $organizationId = $this->getOrganizationId();

    if (!$accessToken) {
        throw new Exception('Not connected to Zoho');
    }

    if (!$organizationId) {
        throw new Exception('Organization ID not set');
    }

    // Load required relations
    $invoice->load(['vendor.statutoryInfo', 'employee', 'items', 'category', 'batch']);

    // -----------------------------------------------------
    // VENDOR SAFETY CHECK
    // -----------------------------------------------------
    if (!$invoice->vendor) {
        throw new Exception('Travel Invoice has no vendor');
    }

    $vendor = $invoice->vendor;

    /**
     * STEP 1: If vendor already linked locally → use it
     */
    if (!$vendor->zoho_contact_id) {

        /**
         * STEP 2: Search vendor in Zoho BEFORE creating
         */
        $zohoContactId = $this->findVendorInZoho($vendor);

        if ($zohoContactId) {
            // ✅ Vendor found in Zoho → link locally
            $vendor->update([
                'zoho_contact_id' => $zohoContactId,
                'zoho_synced_at' => now(),
            ]);

            Log::info('Vendor found in Zoho, linked locally', [
                'vendor_id' => $vendor->id,
                'zoho_contact_id' => $zohoContactId,
            ]);
        } else {
            // ❌ Vendor NOT found → create new
            Log::info('Vendor not found in Zoho, creating', [
                'vendor_id' => $vendor->id,
            ]);

            $this->createVendor($vendor);
            $vendor->refresh();
        }
    }

    // -----------------------------------------------------
    // LINE ITEMS - TDS APPLIES ON BASE AMOUNT ONLY!
    // -----------------------------------------------------
    $defaultAccountId = config('zoho.expense_account_id');
    $lineItems = [];

    foreach ($invoice->items as $item) {
        // Use category's zoho_account_id if available
        $accountId = $invoice->category->zoho_account_id ?? $defaultAccountId;
        
        // Get individual amounts
        $basic = (float) ($item->basic ?? 0);
        $taxes = (float) ($item->taxes ?? 0);
        $serviceCharge = (float) ($item->service_charge ?? 0);
        $gst = (float) ($item->gst ?? 0);
        
        // =====================================================
        // LINE ITEM 1: BASE AMOUNT (TDS APPLICABLE)
        // TDS will be calculated on THIS amount only!
        // =====================================================
        if ($basic > 0) {
            $lineItems[] = [
                'account_id' => $accountId,
                'name' => $item->mode_label ?? 'Travel Expense',
                'description' => $item->particulars ?? '',
                'quantity' => 1,
                'rate' => $basic,
                'is_tds_applicable' => true,  // TDS applies on base amount
            ];
        }
        
        // =====================================================
        // LINE ITEM 2: TAXES (NO TDS)
        // =====================================================
        if ($taxes > 0) {
            $lineItems[] = [
                'account_id' => $accountId,
                'name' => 'Travel Taxes',
                'description' => 'Taxes for ' . ($item->mode_label ?? 'travel'),
                'quantity' => 1,
                'rate' => $taxes,
                'is_tds_applicable' => false,  // No TDS on taxes
            ];
        }
        
        // =====================================================
        // LINE ITEM 3: SERVICE CHARGES (NO TDS)
        // =====================================================
        if ($serviceCharge > 0) {
            $lineItems[] = [
                'account_id' => $accountId,
                'name' => 'Service Charges',
                'description' => 'Service charges for ' . ($item->mode_label ?? 'travel'),
                'quantity' => 1,
                'rate' => $serviceCharge,
                'is_tds_applicable' => false,  // No TDS on service charges
            ];
        }
        
        // =====================================================
        // LINE ITEM 4: GST (NO TDS)
        // =====================================================
        if ($gst > 0) {
            $lineItems[] = [
                'account_id' => $accountId,
                'name' => 'GST',
                'description' => 'GST for ' . ($item->mode_label ?? 'travel'),
                'quantity' => 1,
                'rate' => $gst,
                'is_tds_applicable' => false,  // No TDS on GST
            ];
        }
    }

    // Fallback if no items - use invoice totals
    if (empty($lineItems)) {
        $basic = (float) ($invoice->basic_total ?? $invoice->gross_amount);
        $taxes = (float) ($invoice->taxes_total ?? 0);
        $serviceCharge = (float) ($invoice->service_charge_total ?? 0);
        $gst = (float) ($invoice->gst_total ?? 0);
        
        // Base amount (TDS applicable)
        if ($basic > 0) {
            $lineItems[] = [
                'account_id' => $defaultAccountId,
                'name' => $invoice->category->name ?? 'Travel Expense',
                'description' => $invoice->description ?? '',
                'quantity' => 1,
                'rate' => $basic,
                'is_tds_applicable' => true,
            ];
        }
        
        // Other charges (No TDS)
        $otherCharges = $taxes + $serviceCharge + $gst;
        if ($otherCharges > 0) {
            $lineItems[] = [
                'account_id' => $defaultAccountId,
                'name' => 'Taxes & Charges',
                'description' => 'Taxes, Service Charges & GST',
                'quantity' => 1,
                'rate' => $otherCharges,
                'is_tds_applicable' => false,
            ];
        }
    }

   
// -----------------------------------------------------
// BILL DATA
// -----------------------------------------------------
// 🔥 USE ZOHO_SYNC_DATE IF AVAILABLE (CHANGED BY FINANCE), OTHERWISE USE INVOICE_DATE
$billDate = $invoice->zoho_sync_date 
    ?? $invoice->invoice_date 
    ?? $invoice->travel_date 
    ?? now();

$zohoData = [
    'vendor_id' => $vendor->zoho_contact_id,
    'bill_number' => $invoice->invoice_number,
    'date' => \Carbon\Carbon::parse($billDate)->format('Y-m-d'),
    'due_date' => now()->addDays(30)->format('Y-m-d'),
    'line_items' => $lineItems,
    'reference_number' => $invoice->invoice_number,
];

    // -----------------------------------------------------
    // TDS CONFIGURATION
    // TDS is applied on BASE AMOUNT only
    // We pass EXPLICIT tds_amount to override Zoho's calculation!
    // -----------------------------------------------------
    if (!empty($invoice->tds_tax_id) && $invoice->tds_percent > 0) {
        $zohoData['is_tds_applied'] = true;
        $zohoData['tds_tax_id'] = $invoice->tds_tax_id;
        
        // =====================================================
        // EXPLICITLY SET TDS AMOUNT (Calculated on BASE only!)
        // This overrides Zoho's default calculation
        // =====================================================
        $baseAmount = (float) ($invoice->basic_total ?? 0);
        $tdsPercent = (float) $invoice->tds_percent;
        $tdsAmount = round(($baseAmount * $tdsPercent) / 100, 2);
        
        $zohoData['tds_amount'] = $tdsAmount;
        
        Log::info('TDS Calculation for Zoho', [
            'invoice_id' => $invoice->id,
            'base_amount' => $baseAmount,
            'tds_percent' => $tdsPercent,
            'tds_amount' => $tdsAmount,
            'gross_amount' => $invoice->gross_amount,
        ]);
    }

    // -----------------------------------------------------
    // NOTES
    // -----------------------------------------------------
// -----------------------------------------------------
// 🔥 PROFESSIONAL NOTES FOR TRAVEL INVOICE
// -----------------------------------------------------
$notes = [];

$notes[] = "═══════════════════════════════════";
$notes[] = " TRAVEL EXPENSE INVOICE";
$notes[] = "═══════════════════════════════════";
$notes[] = "";

// Invoice Details
$notes[] = "Invoice #: {$invoice->invoice_number}";
$notes[] = "Invoice Date: " . ($invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : now()->format('d M Y'));

if ($invoice->batch) {
    $notes[] = "Batch #: {$invoice->batch->batch_number}";
}

$notes[] = "";
$notes[] = "───────────────────────────────────";
$notes[] = " EMPLOYEE DETAILS";
$notes[] = "───────────────────────────────────";

if ($invoice->employee) {
    $notes[] = "Name: {$invoice->employee->employee_name}";
    if ($invoice->employee->employee_code) {
        $notes[] = "Employee Code: {$invoice->employee->employee_code}";
    }
    if ($invoice->employee->department) {
        $notes[] = "Department: {$invoice->employee->department}";
    }
} else {
    $notes[] = "Name: N/A";
}

$notes[] = "";
$notes[] = "───────────────────────────────────";
$notes[] = " TRAVEL DETAILS";
$notes[] = "───────────────────────────────────";

if ($invoice->location) {
    $notes[] = "Location: {$invoice->location}";
}

if ($invoice->travel_date) {
    $notes[] = "Travel Date: " . $invoice->travel_date->format('d M Y');
}

if ($invoice->travel_type) {
    $notes[] = "Travel Type: " . ucfirst($invoice->travel_type);
}

if ($invoice->tag_name) {
    $notes[] = "Project/Tag: {$invoice->tag_name}";
}

// =====================================================
// AMOUNT BREAKDOWN WITH TDS
// =====================================================
$notes[] = "";
$notes[] = "───────────────────────────────────";
$notes[] = " AMOUNT BREAKDOWN";
$notes[] = "───────────────────────────────────";

$basicTotal = (float) ($invoice->basic_total ?? 0);
$taxesTotal = (float) ($invoice->taxes_total ?? 0);
$serviceTotal = (float) ($invoice->service_charge_total ?? 0);
$gstTotal = (float) ($invoice->gst_total ?? 0);
$grossTotal = (float) ($invoice->gross_amount ?? 0);
$tdsPercent = (float) ($invoice->tds_percent ?? 0);
$tdsAmount = (float) ($invoice->tds_amount ?? 0);
$netAmount = (float) ($invoice->net_amount ?? 0);

$notes[] = "Basic Amount:      ₹" . number_format($basicTotal, 2);
if ($taxesTotal > 0) {
    $notes[] = "Taxes:             ₹" . number_format($taxesTotal, 2);
}
if ($serviceTotal > 0) {
    $notes[] = "Service Charges:   ₹" . number_format($serviceTotal, 2);
}
if ($gstTotal > 0) {
    $notes[] = "GST:               ₹" . number_format($gstTotal, 2);
}
$notes[] = "                   ─────────────";
$notes[] = "Gross Total:       ₹" . number_format($grossTotal, 2);

if ($tdsPercent > 0) {
    $notes[] = "";
    $notes[] = "TDS @ {$tdsPercent}% on Base: -₹" . number_format($tdsAmount, 2);
    $notes[] = "  (Calculated on ₹" . number_format($basicTotal, 2) . ")";
    $notes[] = "                   ─────────────";
    $notes[] = "Net Payable:       ₹" . number_format($netAmount, 2);
}

$notes[] = "";
$notes[] = "═══════════════════════════════════";
$notes[] = "Generated via Vendor Portal";

$zohoData['notes'] = implode("\n", $notes);

    // -----------------------------------------------------
    // SEND TO ZOHO
    // -----------------------------------------------------
    Log::info('Creating Travel Bill in Zoho', [
        'invoice_id' => $invoice->id,
        'vendor_zoho_id' => $vendor->zoho_contact_id,
    ]);

    $response = Http::withHeaders([
        'Authorization' => "Zoho-oauthtoken {$accessToken}",
        'Content-Type' => 'application/json',
    ])->post(
        "{$this->apiUrl}/books/v3/bills?organization_id={$organizationId}",
        $zohoData
    );

    $result = $response->json();

    if ($response->failed() || ($result['code'] ?? 0) !== 0) {
        Log::error('Zoho Create Travel Bill Error', [
            'invoice_id' => $invoice->id,
            'response' => $result,
        ]);
        throw new Exception($result['message'] ?? 'Failed to create travel bill in Zoho');
    }

    // -----------------------------------------------------
    // SAVE ZOHO BILL ID
    // -----------------------------------------------------
    $zohoBillId = $result['bill']['bill_id'] ?? null;

    if ($zohoBillId) {
        $invoice->update([
            'zoho_bill_id' => $zohoBillId,
            'zoho_synced_at' => now(),
        ]);
    }

    return $result;
}






/**
 * Get Reporting Tags from Zoho Books
 */
public function getReportingTags(): array
{
    $accessToken = $this->getValidToken();
    $organizationId = $this->getOrganizationId();

    if (!$accessToken) {
        throw new Exception('Not connected to Zoho');
    }

    if (!$organizationId) {
        throw new Exception('Organization ID not set');
    }

    $response = Http::withHeaders([
        'Authorization' => "Zoho-oauthtoken {$accessToken}",
    ])->get("{$this->apiUrl}/books/v3/reportingtags?organization_id={$organizationId}");

    if ($response->failed()) {
        Log::error('Zoho Get Reporting Tags Error', ['response' => $response->json()]);
        throw new Exception('Failed to get reporting tags from Zoho');
    }

    $result = $response->json();

    Log::info('Fetched Reporting Tags from Zoho', [
        'count' => count($result['tags'] ?? []),
    ]);

    // Return only active tags
    $tags = $result['tags'] ?? [];
    
    return array_filter($tags, function($tag) {
        return $tag['is_active'] ?? false;
    });
}
}