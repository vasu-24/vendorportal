<?php

namespace App\Services;

use App\Models\ZohoToken;
use App\Models\Vendor;
use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

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

    // If vendor not synced to Zoho, create first
    if (!$invoice->vendor->zoho_contact_id) {
        Log::info('Vendor not in Zoho, creating first', ['vendor_id' => $invoice->vendor->id]);
        $this->createVendor($invoice->vendor);
        $invoice->vendor->refresh();
    }

    // 👇 ADD THIS LINE
    $defaultAccountId = config('zoho.expense_account_id');

    // Prepare line items
    $lineItems = [];
    foreach ($invoice->items as $item) {
        $lineItems[] = [
            'account_id' => $defaultAccountId,  // 👈 ADD THIS
            'name' => $item->category->name ?? $item->particulars ?? 'Item',
            'description' => $item->particulars ?? '',
            'quantity' => (float) $item->quantity,
            'rate' => (float) $item->rate,
            'tax_percentage' => (float) ($item->tax_percent ?? 0),
        ];
    }

    // If no line items, create one from totals
    if (empty($lineItems)) {
        $lineItems[] = [
            'account_id' => $defaultAccountId,  // 👈 ADD THIS
            'name' => 'Invoice Amount',
            'description' => $invoice->description ?? '',
            'quantity' => 1,
            'rate' => (float) $invoice->base_total,
            'tax_percentage' => $invoice->base_total > 0 
                ? round(($invoice->gst_total / $invoice->base_total) * 100, 2) 
                : 0,
        ];
    }

    // Build bill data
    $zohoData = [
        'vendor_id' => $invoice->vendor->zoho_contact_id,
        'bill_number' => $invoice->invoice_number,
        'date' => $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : now()->format('Y-m-d'),
        'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : now()->addDays(30)->format('Y-m-d'),
        'line_items' => $lineItems,
        'reference_number' => $invoice->invoice_number,
    ];

    // Add notes with contract reference
    $notes = [];
    if ($invoice->contract) {
        $notes[] = "Contract: {$invoice->contract->contract_number}";
    }
    if ($invoice->description) {
        $notes[] = $invoice->description;
    }
    if (!empty($notes)) {
        $zohoData['notes'] = implode("\n", $notes);
    }

    Log::info('Creating Bill in Zoho', [
        'invoice_id' => $invoice->id,
        'invoice_number' => $invoice->invoice_number,
        'vendor_zoho_id' => $invoice->vendor->zoho_contact_id,
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

        // Prepare line items
        $lineItems = [];
        foreach ($invoice->items as $item) {
            $lineItems[] = [
                'name' => $item->category->name ?? $item->particulars ?? 'Item',
                'description' => $item->particulars ?? '',
                'quantity' => (float) $item->quantity,
                'rate' => (float) $item->rate,
                'tax_percentage' => (float) ($item->tax_percent ?? 0),
            ];
        }

        $zohoData = [
            'bill_number' => $invoice->invoice_number,
            'date' => $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : now()->format('Y-m-d'),
            'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : now()->addDays(30)->format('Y-m-d'),
            'line_items' => $lineItems,
            'notes' => $invoice->description ?? '',
        ];

        Log::info('Updating Bill in Zoho', [
            'invoice_id' => $invoice->id,
            'zoho_bill_id' => $invoice->zoho_invoice_id,
        ]);

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
}