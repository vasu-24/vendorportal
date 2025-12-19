<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ZohoService;
use Illuminate\Http\Request;
use Exception;

class ZohoDataController extends Controller
{
    protected $zohoService;

    public function __construct(ZohoService $zohoService)
    {
        $this->zohoService = $zohoService;
    }

    /**
     * Get Chart of Accounts from Zoho
     * GET /api/zoho/chart-of-accounts
     */
    public function getChartOfAccounts(Request $request)
    {
        try {
            if (!$this->zohoService->isConnected()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zoho not connected'
                ], 400);
            }

            $accountType = $request->query('type'); // expense, income, etc.
            $accounts = $this->zohoService->getChartOfAccounts($accountType);

            return response()->json([
                'success' => true,
                'data' => $accounts
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Expense Accounts only
     * GET /api/zoho/expense-accounts
     */
    public function getExpenseAccounts()
    {
        try {
            if (!$this->zohoService->isConnected()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zoho not connected'
                ], 400);
            }

            $accounts = $this->zohoService->getExpenseAccounts();

            return response()->json([
                'success' => true,
                'data' => $accounts
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all Taxes from Zoho
     * GET /api/zoho/taxes
     */
    public function getTaxes()
    {
        try {
            if (!$this->zohoService->isConnected()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zoho not connected'
                ], 400);
            }

            $taxes = $this->zohoService->getFormattedTaxes();

            return response()->json([
                'success' => true,
                'data' => $taxes
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Tax Groups (TDS sections)
     * GET /api/zoho/tax-groups
     */
    public function getTaxGroups()
    {
        try {
            if (!$this->zohoService->isConnected()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zoho not connected'
                ], 400);
            }

            $taxGroups = $this->zohoService->getTaxGroups();

            return response()->json([
                'success' => true,
                'data' => $taxGroups
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}