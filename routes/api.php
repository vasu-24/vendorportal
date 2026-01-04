<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\ZohoDataController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\TimesheetController;
use App\Http\Controllers\Api\ManagerTagController;
use App\Http\Controllers\Api\TravelInvoiceController;
use App\Http\Controllers\Api\VendorInvoiceController;
use App\Http\Controllers\Api\TravelEmployeeController;
use App\Http\Controllers\Api\VendorApprovalController;
use App\Http\Controllers\Api\VendorContractController;
use App\Http\Controllers\Api\VendorRegistrationController;
use App\Http\Controllers\Api\VendorTravelInvoiceController;

// =====================================================
// VENDOR REGISTRATION (Public - No Auth)
// =====================================================

Route::prefix('vendor/registration')->group(function () {
    Route::get('/data/{token}', [VendorRegistrationController::class, 'getRegistrationData']);
    Route::post('/step1/{token}', [VendorRegistrationController::class, 'saveStep1']);
    Route::post('/step2/{token}', [VendorRegistrationController::class, 'saveStep2']);
    Route::post('/step3/{token}', [VendorRegistrationController::class, 'saveStep3']);
    Route::post('/step4/{token}', [VendorRegistrationController::class, 'saveStep4']);
});

// =====================================================
// ADMIN API ROUTES (Requires Auth + Permissions)
// =====================================================

Route::middleware(['web', 'auth'])->group(function () {

    // =====================================================
    // USER API
    // =====================================================
    
    Route::prefix('admin/users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware('permission:view-users');
        Route::get('/roles', [UserController::class, 'getRoles'])->middleware('permission:view-users');
        Route::get('/{id}', [UserController::class, 'show'])->middleware('permission:view-users');
        Route::post('/', [UserController::class, 'store'])->middleware('permission:create-users');
        Route::put('/{id}', [UserController::class, 'update'])->middleware('permission:edit-users');
        Route::delete('/{id}', [UserController::class, 'destroy'])->middleware('permission:delete-users');
    });

    // =====================================================
    // ROLE API
    // =====================================================
    
    Route::prefix('admin/roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->middleware('permission:view-roles');
        Route::get('/permissions', [RoleController::class, 'getPermissions'])->middleware('permission:view-roles');
        Route::get('/{id}', [RoleController::class, 'show'])->middleware('permission:view-roles');
        Route::post('/', [RoleController::class, 'store'])->middleware('permission:create-roles');
        Route::put('/{id}', [RoleController::class, 'update'])->middleware('permission:edit-roles');
        Route::delete('/{id}', [RoleController::class, 'destroy'])->middleware('permission:delete-roles');
    });

    // =====================================================
    // VENDOR APPROVAL API
    // =====================================================
    
    Route::prefix('vendor/approval')->group(function () {
        Route::get('/pending', [VendorApprovalController::class, 'getPendingVendors'])->middleware('permission:view-vendors');
        Route::get('/status/{status}', [VendorApprovalController::class, 'getVendorsByStatus'])->middleware('permission:view-vendors');
        Route::get('/statistics', [VendorApprovalController::class, 'getStatistics'])->middleware('permission:view-vendors');
        Route::get('/{id}/details', [VendorApprovalController::class, 'getVendorDetails'])->middleware('permission:view-vendors');
        Route::get('/{id}/history', [VendorApprovalController::class, 'getVendorHistory'])->middleware('permission:view-vendors');
        Route::post('/{id}/approve', [VendorApprovalController::class, 'approveVendor'])->middleware('permission:approve-vendors');
        Route::post('/{id}/reject', [VendorApprovalController::class, 'rejectVendor'])->middleware('permission:reject-vendors');
        Route::post('/{id}/request-revision', [VendorApprovalController::class, 'requestRevision'])->middleware('permission:edit-vendors');
        Route::put('/{id}/company-info', [VendorApprovalController::class, 'updateCompanyInfo'])->middleware('permission:edit-vendors');
        Route::put('/{id}/contact', [VendorApprovalController::class, 'updateContact'])->middleware('permission:edit-vendors');
        Route::put('/{id}/statutory-info', [VendorApprovalController::class, 'updateStatutoryInfo'])->middleware('permission:edit-vendors');
        Route::put('/{id}/bank-details', [VendorApprovalController::class, 'updateBankDetails'])->middleware('permission:edit-vendors');
        Route::put('/{id}/tax-info', [VendorApprovalController::class, 'updateTaxInfo'])->middleware('permission:edit-vendors');
        Route::put('/{id}/business-profile', [VendorApprovalController::class, 'updateBusinessProfile'])->middleware('permission:edit-vendors');
    });
    
    Route::post('/vendors/{id}/sync-zoho', [VendorApprovalController::class, 'syncToZoho'])->middleware('permission:sync-vendors');

    // =====================================================
    // INVOICE API
    // =====================================================
    
    Route::prefix('admin/invoices')->group(function () {
        Route::get('/statistics', [InvoiceController::class, 'getStatistics'])->middleware('permission:view-invoices');
        Route::get('/pending', [InvoiceController::class, 'getPending'])->middleware('permission:view-invoices');
        Route::get('/status/{status}', [InvoiceController::class, 'getByStatus'])->middleware('permission:view-invoices');
        Route::get('/vendors', [InvoiceController::class, 'getVendors'])->middleware('permission:view-invoices');
        Route::get('/', [InvoiceController::class, 'index'])->middleware('permission:view-invoices');
        Route::get('/{id}', [InvoiceController::class, 'show'])->middleware('permission:view-invoices');
        Route::get('/{invoiceId}/attachment/{attachmentId}/download', [InvoiceController::class, 'downloadAttachment'])->middleware('permission:view-invoices');
        Route::post('/{id}/start-review', [InvoiceController::class, 'startReview'])->middleware('permission:review-invoices');
        Route::post('/{id}/approve', [InvoiceController::class, 'approve'])->middleware('permission:approve-invoices');
        Route::post('/{id}/reject', [InvoiceController::class, 'reject'])->middleware('permission:reject-invoices');
        Route::post('/{id}/mark-paid', [InvoiceController::class, 'markAsPaid'])->middleware('permission:pay-invoices');
        Route::post('/{id}/update-taxes', [InvoiceController::class, 'updateTaxes'])->middleware('permission:edit-invoices');
        Route::post('/{id}/push-to-zoho', [InvoiceController::class, 'pushToZoho'])->middleware('permission:sync-invoices');
        Route::post('/{id}/sync-from-zoho', [InvoiceController::class, 'syncFromZoho'])->middleware('permission:sync-invoices');
        Route::post('/sync-all-from-zoho', [InvoiceController::class, 'syncAllFromZoho'])->middleware('permission:sync-invoices');

        // Change Tag (RM can reassign)
Route::post('/{id}/change-tag', [InvoiceController::class, 'changeTag'])->middleware('permission:approve-invoices');

// Update Invoice (Finance can edit)
Route::put('/{id}/update', [InvoiceController::class, 'updateInvoice'])->middleware('permission:edit-invoices');
    });

    // =====================================================
    // CONTRACT API
    // =====================================================
    
    Route::prefix('admin/contracts')->group(function () {
        Route::get('/statistics', [ContractController::class, 'getStatistics'])->middleware('permission:view-contracts');
        Route::get('/vendors', [ContractController::class, 'getVendors'])->middleware('permission:view-contracts');
        Route::get('/organisations', [ContractController::class, 'getOrganisations'])->middleware('permission:view-contracts');
        Route::get('/categories', [ContractController::class, 'getCategories'])->middleware('permission:view-contracts');
        Route::get('/templates', [ContractController::class, 'getTemplates'])->middleware('permission:view-contracts');
        Route::get('/units', [ContractController::class, 'getUnits'])->middleware('permission:view-contracts');
        Route::post('/upload-document', [ContractController::class, 'uploadDocument'])->middleware('permission:edit-contracts');
        Route::get('/', [ContractController::class, 'index'])->middleware('permission:view-contracts');
        Route::post('/', [ContractController::class, 'store'])->middleware('permission:create-contracts');
        Route::get('/{id}', [ContractController::class, 'show'])->middleware('permission:view-contracts');
        Route::put('/{id}', [ContractController::class, 'update'])->middleware('permission:edit-contracts');
        Route::delete('/{id}', [ContractController::class, 'destroy'])->middleware('permission:delete-contracts');
        Route::patch('/{id}/status', [ContractController::class, 'updateStatus'])->middleware('permission:edit-contracts');
    });

    // =====================================================
    // CATEGORY API
    // =====================================================
    
    Route::prefix('admin/categories')->group(function () {
        Route::get('/statistics', [CategoryController::class, 'getStatistics'])->middleware('permission:view-categories');
        Route::get('/active', [CategoryController::class, 'getActive'])->middleware('permission:view-categories');
        Route::get('/', [CategoryController::class, 'index'])->middleware('permission:view-categories');
        Route::get('/travel', [CategoryController::class, 'getTravelCategories']);
        Route::get('/{id}', [CategoryController::class, 'show'])->middleware('permission:view-categories');
        Route::post('/', [CategoryController::class, 'store'])->middleware('permission:create-categories');
        Route::put('/{id}', [CategoryController::class, 'update'])->middleware('permission:edit-categories');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->middleware('permission:delete-categories');
        Route::post('/{id}/toggle-status', [CategoryController::class, 'toggleStatus'])->middleware('permission:edit-categories');
      
    });

    // =====================================================
    // MANAGER TAGS API
    // =====================================================
    
  Route::prefix('admin/manager-tags')->group(function () {
    Route::get('/', [ManagerTagController::class, 'index'])->middleware('permission:view-manager-tags');
    Route::get('/managers-dropdown', [ManagerTagController::class, 'managersDropdown'])->middleware('permission:view-manager-tags');
    Route::get('/tags-dropdown', [ManagerTagController::class, 'tagsDropdown'])->middleware('permission:view-invoices');
    Route::get('/assigned-tags-dropdown', [ManagerTagController::class, 'assignedTagsDropdown'])->middleware('permission:view-invoices');
    Route::post('/', [ManagerTagController::class, 'store'])->middleware('permission:create-manager-tags');
    Route::delete('/{userId}', [ManagerTagController::class, 'destroy'])->middleware('permission:delete-manager-tags');
});

    // =====================================================
    // ZOHO DATA API
    // =====================================================
    
    Route::prefix('zoho')->group(function () {
        Route::get('/chart-of-accounts', [ZohoDataController::class, 'getChartOfAccounts'])->middleware('permission:view-zoho-data');
        Route::get('/expense-accounts', [ZohoDataController::class, 'getExpenseAccounts'])->middleware('permission:view-zoho-data');
        Route::get('/taxes', [ZohoDataController::class, 'getTaxes'])->middleware('permission:view-zoho-data');
        Route::get('/tax-groups', [ZohoDataController::class, 'getTaxGroups'])->middleware('permission:view-zoho-data');
        Route::get('/reporting-tags', function() {
            try {
                $zohoService = new \App\Services\ZohoService();
                $tags = $zohoService->getReportingTags();
                return response()->json(['success' => true, 'data' => array_values($tags)]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
        })->middleware('permission:view-zoho-data');
    });

    // =====================================================
    // DASHBOARD API
    // =====================================================
    
    Route::prefix('dashboard')->group(function () {
        Route::get('/summary', [DashboardController::class, 'summary'])->middleware('permission:view-dashboard');
        Route::get('/focus-items', [DashboardController::class, 'focusItems'])->middleware('permission:view-dashboard');
        Route::get('/recent-activity', [DashboardController::class, 'recentActivity'])->middleware('permission:view-dashboard');
        Route::get('/sync-status', [DashboardController::class, 'syncStatus'])->middleware('permission:view-dashboard');
        Route::post('/sync-all', [DashboardController::class, 'syncAll'])->middleware('permission:sync-dashboard');
        Route::post('/sync-vendors', [DashboardController::class, 'syncVendors'])->middleware('permission:sync-dashboard');
        Route::post('/import-from-zoho', [DashboardController::class, 'importFromZoho'])->middleware('permission:sync-dashboard');
    });

    // =====================================================
    // TIMESHEET API
    // =====================================================
    
    Route::post('/timesheet/validate', [TimesheetController::class, 'validateTimesheet'])->middleware('permission:view-invoices');

});

// =====================================================
// VENDOR PORTAL API (Requires Vendor Auth)
// =====================================================

Route::middleware(['web', 'auth:vendor'])->group(function () {


    Route::get('vendor/categories', [VendorInvoiceController::class, 'getCategories']);
    // =====================================================
    // VENDOR INVOICE API
    // =====================================================
    
    Route::prefix('vendor/invoices')->group(function () {
    

        Route::get('/statistics', [VendorInvoiceController::class, 'getStatistics']);
        Route::get('/contracts', [VendorInvoiceController::class, 'getContracts']);
        Route::get('/status/{status}', [VendorInvoiceController::class, 'getByStatus']);
        Route::get('/', [VendorInvoiceController::class, 'index']);
        Route::post('/', [VendorInvoiceController::class, 'store']);
        Route::get('/{id}', [VendorInvoiceController::class, 'show']);
        Route::post('/{id}/update', [VendorInvoiceController::class, 'update']);
        Route::post('/{id}/submit', [VendorInvoiceController::class, 'submit']);
        Route::delete('/{id}', [VendorInvoiceController::class, 'destroy']);
        Route::get('/{invoiceId}/attachment/{attachmentId}/download', [VendorInvoiceController::class, 'downloadAttachment']);
    });

    // =====================================================
    // VENDOR CONTRACT API
    // =====================================================
    
    Route::prefix('vendor/contracts')->group(function () {
        Route::get('/statistics', [VendorContractController::class, 'getStatistics']);
        Route::get('/dropdown', [VendorContractController::class, 'getContractsDropdown']);
        Route::get('/{id}/items', [VendorContractController::class, 'getContractItems']);
        Route::get('/', [VendorContractController::class, 'index']);
        Route::get('/{id}', [VendorContractController::class, 'show']);
    });

});




// =====================================================
// TRAVEL INVOICE API ROUTES
// =====================================================
// Add these routes to your routes/api.php file

/*
|--------------------------------------------------------------------------
| ADMIN PORTAL - Travel Employee Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth'])->prefix('admin')->group(function () {
    
    // Travel Employee Master
    Route::prefix('travel-employees')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\TravelEmployeeController::class, 'index']);
        Route::get('/statistics', [App\Http\Controllers\Api\TravelEmployeeController::class, 'getStatistics']);
        Route::get('/dropdown', [App\Http\Controllers\Api\TravelEmployeeController::class, 'dropdown']);
        Route::get('/projects', [App\Http\Controllers\Api\TravelEmployeeController::class, 'getProjects']);
        Route::post('/', [App\Http\Controllers\Api\TravelEmployeeController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\TravelEmployeeController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\TravelEmployeeController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\TravelEmployeeController::class, 'destroy']);
        Route::post('/{id}/toggle-status', [App\Http\Controllers\Api\TravelEmployeeController::class, 'toggleStatus']);
    });

    // Travel Invoice Management
  // Travel Invoice Management (ADMIN)
Route::prefix('travel-invoices')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\TravelInvoiceController::class, 'index']);
    Route::get('/statistics', [App\Http\Controllers\Api\TravelInvoiceController::class, 'getStatistics']);
    Route::get('/batches', [App\Http\Controllers\Api\TravelInvoiceController::class, 'getBatches']);
    Route::get('/batches/{batchId}/summary', [App\Http\Controllers\Api\TravelInvoiceController::class, 'getBatchSummary']);
    Route::put('/admin/travel-invoices/{id}/update', [TravelInvoiceController::class, 'updateInvoice']);
    Route::get('/{id}', [App\Http\Controllers\Api\TravelInvoiceController::class, 'show']);

    
    // Approval Actions - Single
    Route::post('/{id}/start-review', [App\Http\Controllers\Api\TravelInvoiceController::class, 'startReview']);
    Route::post('/{id}/approve', [App\Http\Controllers\Api\TravelInvoiceController::class, 'approve']);
    Route::post('/{id}/reject', [App\Http\Controllers\Api\TravelInvoiceController::class, 'reject']);
    Route::post('/{id}/mark-paid', [App\Http\Controllers\Api\TravelInvoiceController::class, 'markAsPaid']);
    
    // Bulk Actions - Batch
    Route::post('/batches/{batchId}/start-review', [App\Http\Controllers\Api\TravelInvoiceController::class, 'startReviewBatch']);  // 👈 ADD THIS
    Route::post('/batches/{batchId}/approve-all', [App\Http\Controllers\Api\TravelInvoiceController::class, 'approveAll']);
    Route::post('/batches/{batchId}/reject-all', [App\Http\Controllers\Api\TravelInvoiceController::class, 'rejectAll']);
});
});

/*
|--------------------------------------------------------------------------
| VENDOR PORTAL - Travel Invoice Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth:vendor'])->prefix('vendor')->group(function () {
    
    Route::prefix('travel-invoices')->group(function () {
        // Statistics & Lists
        Route::get('/statistics', [App\Http\Controllers\Api\VendorTravelInvoiceController::class, 'getStatistics']);
        Route::get('/batches/next-number', [App\Http\Controllers\Api\VendorTravelInvoiceController::class, 'getNextBatchNumber']);
        Route::get('/batches', [App\Http\Controllers\Api\VendorTravelInvoiceController::class, 'getBatches']);
        Route::get('/batches/{batchId}', [App\Http\Controllers\Api\VendorTravelInvoiceController::class, 'getBatchDetails']);
        Route::get('/employees', [App\Http\Controllers\Api\VendorTravelInvoiceController::class, 'getEmployees']);
        Route::get('/submitted-invoices', [App\Http\Controllers\Api\VendorTravelInvoiceController::class, 'getSubmittedInvoices']); // 👈 ADD THIS LINE HERE!
        Route::get('/', [App\Http\Controllers\Api\VendorTravelInvoiceController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Api\VendorTravelInvoiceController::class, 'show']); // This must be AFTER submitted-invoices
        
        // ... rest of routes
    

        // Create & Update
        Route::post('/batches', [App\Http\Controllers\Api\VendorTravelInvoiceController::class, 'createBatch']);
        Route::post('/', [App\Http\Controllers\Api\VendorTravelInvoiceController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\Api\VendorTravelInvoiceController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\VendorTravelInvoiceController::class, 'destroy']);
        
        // Bills
        Route::post('/{id}/bills', [App\Http\Controllers\Api\VendorTravelInvoiceController::class, 'uploadBills']);
        Route::delete('/{invoiceId}/bills/{billId}', [App\Http\Controllers\Api\VendorTravelInvoiceController::class, 'deleteBill']);
        Route::get('/{invoiceId}/bills/{billId}/download', [App\Http\Controllers\Api\VendorTravelInvoiceController::class, 'downloadBill']);
        
        // Submit
        Route::post('/batches/{batchId}/submit', [App\Http\Controllers\Api\VendorTravelInvoiceController::class, 'submitBatch']);
    });
});