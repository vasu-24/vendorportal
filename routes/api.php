<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\VendorInvoiceController;
use App\Http\Controllers\Api\VendorApprovalController;
use App\Http\Controllers\Api\VendorContractController;
use App\Http\Controllers\Api\VendorRegistrationController;
use App\Http\Controllers\Api\ZohoDataController;
use App\Http\Controllers\Api\TimesheetController;

// =====================================================
// VENDOR REGISTRATION (Public - No Auth)
// =====================================================

Route::prefix('vendor/registration')->group(function () {

    // Get full vendor registration data
    Route::get('/data/{token}', [VendorRegistrationController::class, 'getRegistrationData']);

    // Step 1 - Company Info + Contact Details
    Route::post('/step1/{token}', [VendorRegistrationController::class, 'saveStep1']);

    // Step 2 - Statutory Info + Bank Details
    Route::post('/step2/{token}', [VendorRegistrationController::class, 'saveStep2']);

    // Step 3 - Tax Info + Business Profile
    Route::post('/step3/{token}', [VendorRegistrationController::class, 'saveStep3']);

    // Step 4 - KYC Documents + Final Submit
    Route::post('/step4/{token}', [VendorRegistrationController::class, 'saveStep4']);
});


// =====================================================
// USER MANAGEMENT API (Requires Auth + Permissions)
// =====================================================

Route::prefix('admin/users')->middleware(['web', 'auth'])->group(function () {
    
    // View users - requires view-users permission
    Route::get('/', [UserController::class, 'index'])
        ->middleware('permission:view-users');
    
    Route::get('/roles', [UserController::class, 'getRoles'])
        ->middleware('permission:view-users');
    
    Route::get('/{id}', [UserController::class, 'show'])
        ->middleware('permission:view-users');
    
    // Create user - requires create-users permission
    Route::post('/', [UserController::class, 'store'])
        ->middleware('permission:create-users');
    
    // Edit user - requires edit-users permission
    Route::put('/{id}', [UserController::class, 'update'])
        ->middleware('permission:edit-users');
    
    // Delete user - requires delete-users permission
    Route::delete('/{id}', [UserController::class, 'destroy'])
        ->middleware('permission:delete-users');
});

// =====================================================
// ROLE MANAGEMENT API (Requires Auth + Permissions)
// =====================================================

Route::prefix('admin/roles')->middleware(['web', 'auth'])->group(function () {
    
    // View roles - requires view-roles permission
    Route::get('/', [RoleController::class, 'index'])
        ->middleware('permission:view-roles');
    
    Route::get('/permissions', [RoleController::class, 'getPermissions'])
        ->middleware('permission:view-roles');
    
    Route::get('/{id}', [RoleController::class, 'show'])
        ->middleware('permission:view-roles');
    
    // Create role - requires create-roles permission
    Route::post('/', [RoleController::class, 'store'])
        ->middleware('permission:create-roles');
    
    // Edit role - requires edit-roles permission
    Route::put('/{id}', [RoleController::class, 'update'])
        ->middleware('permission:edit-roles');
    
    // Delete role - requires delete-roles permission
    Route::delete('/{id}', [RoleController::class, 'destroy'])
        ->middleware('permission:delete-roles');
});

// =====================================================
// VENDOR APPROVAL API (Requires Auth + Permissions)
// =====================================================

Route::prefix('vendor/approval')->middleware(['web', 'auth'])->group(function () {
    
    // View vendor approvals - requires view-vendors permission
    Route::get('/pending', [VendorApprovalController::class, 'getPendingVendors'])
        ->middleware('permission:view-vendors');
    
    Route::get('/status/{status}', [VendorApprovalController::class, 'getVendorsByStatus'])
        ->middleware('permission:view-vendors');
    
    Route::get('/statistics', [VendorApprovalController::class, 'getStatistics'])
        ->middleware('permission:view-vendors');
    
    Route::get('/{id}/details', [VendorApprovalController::class, 'getVendorDetails'])
        ->middleware('permission:view-vendors');
    
    Route::get('/{id}/history', [VendorApprovalController::class, 'getVendorHistory'])
        ->middleware('permission:view-vendors');
    
    // Approve vendor - requires approve-vendors permission
    Route::post('/{id}/approve', [VendorApprovalController::class, 'approveVendor'])
        ->middleware('permission:approve-vendors');
    
    // Reject vendor - requires reject-vendors permission
    Route::post('/{id}/reject', [VendorApprovalController::class, 'rejectVendor'])
        ->middleware('permission:reject-vendors');
    
    // Edit vendor info - requires edit-vendors permission
    Route::post('/{id}/request-revision', [VendorApprovalController::class, 'requestRevision'])
        ->middleware('permission:edit-vendors');
    
    Route::put('/{id}/company-info', [VendorApprovalController::class, 'updateCompanyInfo'])
        ->middleware('permission:edit-vendors');
    
    Route::put('/{id}/contact', [VendorApprovalController::class, 'updateContact'])
        ->middleware('permission:edit-vendors');
    
    Route::put('/{id}/statutory-info', [VendorApprovalController::class, 'updateStatutoryInfo'])
        ->middleware('permission:edit-vendors');
    
    Route::put('/{id}/bank-details', [VendorApprovalController::class, 'updateBankDetails'])
        ->middleware('permission:edit-vendors');
    
    Route::put('/{id}/tax-info', [VendorApprovalController::class, 'updateTaxInfo'])
        ->middleware('permission:edit-vendors');
    
    Route::put('/{id}/business-profile', [VendorApprovalController::class, 'updateBusinessProfile'])
        ->middleware('permission:edit-vendors');
});

// ZHOO API ROUTE FOR MANULA SYNCING
Route::post('/vendors/{id}/sync-zoho', [VendorApprovalController::class, 'syncToZoho']);


// =====================================================
// VENDOR INVOICE API (Vendor Portal - Requires Vendor Auth)
// =====================================================

Route::prefix('vendor/invoices')->middleware(['web', 'auth:vendor'])->group(function () {
    
    // Get statistics
    Route::get('/statistics', [VendorInvoiceController::class, 'getStatistics']);
    
    // Get contracts for dropdown
    Route::get('/contracts', [VendorInvoiceController::class, 'getContracts']);
    
    // Get invoices by status
    Route::get('/status/{status}', [VendorInvoiceController::class, 'getByStatus']);
    
    // List all invoices
    Route::get('/', [VendorInvoiceController::class, 'index']);
    
    // Create new invoice
    Route::post('/', [VendorInvoiceController::class, 'store']);
    
    // Get invoice details
    Route::get('/{id}', [VendorInvoiceController::class, 'show']);
    
    // Update invoice (POST for file upload)
    Route::post('/{id}/update', [VendorInvoiceController::class, 'update']);
    
    // Submit invoice for approval
    Route::post('/{id}/submit', [VendorInvoiceController::class, 'submit']);
    
    // Delete draft invoice
    Route::delete('/{id}', [VendorInvoiceController::class, 'destroy']);
    
    // Download attachment
    Route::get('/{invoiceId}/attachment/{attachmentId}/download', [VendorInvoiceController::class, 'downloadAttachment']);

});


// =====================================================
// ADMIN INVOICE API (Internal Portal - Requires Auth + Permissions)
// =====================================================

Route::prefix('admin/invoices')->middleware(['web', 'auth'])->group(function () {
    
    // View invoices - requires view-invoices permission
    Route::get('/statistics', [InvoiceController::class, 'getStatistics'])
        ->middleware('permission:view-invoices');
    
    Route::get('/pending', [InvoiceController::class, 'getPending'])
        ->middleware('permission:view-invoices');
    
    Route::get('/status/{status}', [InvoiceController::class, 'getByStatus'])
        ->middleware('permission:view-invoices');
    
    Route::get('/vendors', [InvoiceController::class, 'getVendors'])
        ->middleware('permission:view-invoices');
    
    Route::get('/', [InvoiceController::class, 'index'])
        ->middleware('permission:view-invoices');
    
    Route::get('/{id}', [InvoiceController::class, 'show'])
        ->middleware('permission:view-invoices');
    
    Route::get('/{invoiceId}/attachment/{attachmentId}/download', [InvoiceController::class, 'downloadAttachment'])
        ->middleware('permission:view-invoices');
    
    // Review invoices - requires review-invoices permission
    Route::post('/{id}/start-review', [InvoiceController::class, 'startReview'])
        ->middleware('permission:review-invoices');
    
    // Approve invoices - requires approve-invoices permission
    Route::post('/{id}/approve', [InvoiceController::class, 'approve'])
        ->middleware('permission:approve-invoices');
    
    // Reject invoices - requires reject-invoices permission
    Route::post('/{id}/reject', [InvoiceController::class, 'reject'])
        ->middleware('permission:reject-invoices');
    
    // Mark paid - requires pay-invoices permission
    Route::post('/{id}/mark-paid', [InvoiceController::class, 'markAsPaid'])
        ->middleware('permission:pay-invoices');

Route::post('/invoices/{id}/push-to-zoho', [InvoiceController::class, 'pushToZoho']);
    Route::post('/invoices/{id}/sync-from-zoho', [InvoiceController::class, 'syncFromZoho']);
    Route::post('/invoices/sync-all-from-zoho', [InvoiceController::class, 'syncAllFromZoho']);
    




// Zoho Data Routes (for dropdowns)
Route::middleware('auth:sanctum')->prefix('zoho')->group(function () {
    Route::get('/chart-of-accounts', [ZohoDataController::class, 'getChartOfAccounts']);
    Route::get('/expense-accounts', [ZohoDataController::class, 'getExpenseAccounts']);
    Route::get('/taxes', [ZohoDataController::class, 'getTaxes']);
    Route::get('/tax-groups', [ZohoDataController::class, 'getTaxGroups']);
});



});



Route::prefix('admin/categories')->middleware(['web', 'auth'])->group(function () {
    
    Route::get('/statistics', [CategoryController::class, 'getStatistics']);
    Route::get('/active', [CategoryController::class, 'getActive']); // For dropdowns
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{id}', [CategoryController::class, 'show']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::put('/{id}', [CategoryController::class, 'update']);
    Route::delete('/{id}', [CategoryController::class, 'destroy']);
    Route::post('/{id}/toggle-status', [CategoryController::class, 'toggleStatus']);

});



// =====================================================
// CONTRACT API (Admin - Requires Auth)
// =====================================================

Route::prefix('admin/contracts')->middleware(['web', 'auth'])->group(function () {
    
    // Statistics
    Route::get('/statistics', [App\Http\Controllers\Api\ContractController::class, 'getStatistics']);
    
    // Dropdowns
    Route::get('/vendors', [App\Http\Controllers\Api\ContractController::class, 'getVendors']);
    Route::get('/organisations', [App\Http\Controllers\Api\ContractController::class, 'getOrganisations']);
    Route::get('/categories', [App\Http\Controllers\Api\ContractController::class, 'getCategories']);
    Route::get('/templates', [App\Http\Controllers\Api\ContractController::class, 'getTemplates']);
    Route::get('/units', [App\Http\Controllers\Api\ContractController::class, 'getUnits']);
    
    // Upload Document (to any contract from Index page)
    Route::post('/upload-document', [App\Http\Controllers\Api\ContractController::class, 'uploadDocument']);

    // CRUD
    Route::get('/', [App\Http\Controllers\Api\ContractController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\ContractController::class, 'store']);
    Route::get('/{id}', [App\Http\Controllers\Api\ContractController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\Api\ContractController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\Api\ContractController::class, 'destroy']);
    
    // Status Update
    Route::patch('/{id}/status', [App\Http\Controllers\Api\ContractController::class, 'updateStatus']);
});






// =====================================================
// VENDOR CONTRACT API ROUTES
// =====================================================

Route::prefix('vendor/contracts')->middleware(['web', 'auth:vendor'])->group(function () {
    
    // Statistics
    Route::get('/statistics', [VendorContractController::class, 'getStatistics']);
    
    // Contracts dropdown (for invoice form)
    Route::get('/dropdown', [VendorContractController::class, 'getContractsDropdown']);
    
    // Contract items (for invoice form)
    Route::get('/{id}/items', [VendorContractController::class, 'getContractItems']);
    
    // List & View
    Route::get('/', [VendorContractController::class, 'index']);
    Route::get('/{id}', [VendorContractController::class, 'show']);
});






// =====================================================
// ZOHO DATA API ROUTES (for dropdowns)
// =====================================================

Route::prefix('zoho')->middleware(['web', 'auth'])->group(function () {
    Route::get('/chart-of-accounts', [ZohoDataController::class, 'getChartOfAccounts']);
    Route::get('/expense-accounts', [ZohoDataController::class, 'getExpenseAccounts']);
    Route::get('/taxes', [ZohoDataController::class, 'getTaxes']);
    Route::get('/tax-groups', [ZohoDataController::class, 'getTaxGroups']);
});




// Timesheet validation
Route::post('/timesheet/validate', [TimesheetController::class, 'validateTimesheet']);


// Inside admin invoices group
Route::post('/admin/invoices/{id}/update-taxes', [InvoiceController::class, 'updateTaxes']);







































































































