<?php

namespace App\Http\Controllers;

use App\Services\ZohoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ZohoController extends Controller
{
    protected $zohoService;

    public function __construct(ZohoService $zohoService)
    {
        $this->zohoService = $zohoService;
    }

    /**
     * Show Zoho integration settings page
     */
    public function index()
    {
        $isConnected = $this->zohoService->isConnected();
        $organizations = [];
        $currentOrgId = null;

        if ($isConnected) {
            try {
                $organizations = $this->zohoService->getOrganizations();
                $currentOrgId = $this->zohoService->getOrganizationId();
            } catch (Exception $e) {
                Log::error('Failed to get Zoho organizations', ['error' => $e->getMessage()]);
            }
        }

        return view('pages.settings.zoho', compact('isConnected', 'organizations', 'currentOrgId'));
    }

    /**
     * Redirect to Zoho for authorization
     */
    public function connect()
    {
        $authUrl = $this->zohoService->getAuthUrl();
        return redirect($authUrl);
    }





/**
 * Get Chart of Accounts (Only Expense Accounts)
 */
public function getChartOfAccounts()
{
    try {
        if (!$this->zohoService->isConnected()) {
            return response()->json([
                'success' => false,
                'message' => 'Zoho not connected'
            ], 400);
        }

        // Get all accounts from Zoho
        $allAccounts = $this->zohoService->getChartOfAccounts();

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

        return response()->json([
            'success' => true,
            'data' => array_values($filteredAccounts)
        ]);

    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to load accounts: ' . $e->getMessage()
        ], 500);
    }
}





    /**
     * Handle callback from Zoho OAuth
     */
    public function callback(Request $request)
    {
        // Check for error
        if ($request->has('error')) {
            Log::error('Zoho OAuth Error', [
                'error' => $request->get('error'),
                'description' => $request->get('error_description'),
            ]);
            
            return redirect()->route('settings.zoho')
                ->with('error', 'Failed to connect to Zoho: ' . $request->get('error_description'));
        }

        // Get authorization code
        $code = $request->get('code');

        if (!$code) {
            return redirect()->route('settings.zoho')
                ->with('error', 'No authorization code received from Zoho');
        }

        try {
            // Exchange code for token
            $this->zohoService->getAccessToken($code);

            // Get organizations and set first one as default
            $organizations = $this->zohoService->getOrganizations();
            
            if (!empty($organizations)) {
                $this->zohoService->setOrganizationId($organizations[0]['organization_id']);
            }

            return redirect()->route('settings.zoho')
                ->with('success', 'Successfully connected to Zoho Books!');

        } catch (Exception $e) {
            Log::error('Zoho Callback Error', ['error' => $e->getMessage()]);
            
            return redirect()->route('settings.zoho')
                ->with('error', 'Failed to connect to Zoho: ' . $e->getMessage());
        }
    }

    /**
     * Set organization ID
     */
    public function setOrganization(Request $request)
    {
        $request->validate([
            'organization_id' => 'required|string',
        ]);

        try {
            $this->zohoService->setOrganizationId($request->organization_id);

            return response()->json([
                'success' => true,
                'message' => 'Organization set successfully',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Disconnect Zoho
     */
    public function disconnect()
    {
        try {
            $this->zohoService->disconnect();

            return redirect()->route('settings.zoho')
                ->with('success', 'Disconnected from Zoho Books');

        } catch (Exception $e) {
            return redirect()->route('settings.zoho')
                ->with('error', 'Failed to disconnect: ' . $e->getMessage());
        }
    }

    /**
     * Test connection
     */
    public function test()
    {
        try {
            $organizations = $this->zohoService->getOrganizations();

            return response()->json([
                'success' => true,
                'message' => 'Connection successful!',
                'organizations' => $organizations,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Zoho connection status (API)
     */
    public function status()
    {
        return response()->json([
            'connected' => $this->zohoService->isConnected(),
            'organization_id' => $this->zohoService->getOrganizationId(),
        ]);
    }
}