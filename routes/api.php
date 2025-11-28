<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VendorRegistrationController;
use App\Http\Controllers\Api\VendorApprovalController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// =====================================================
// VENDOR REGISTRATION API ROUTES (For Vendors)
// =====================================================

Route::prefix('vendor/registration')->group(function () {
    Route::post('/step1/{token}', [VendorRegistrationController::class, 'saveStep1']);
    Route::post('/step2/{token}', [VendorRegistrationController::class, 'saveStep2']);
    Route::post('/step3/{token}', [VendorRegistrationController::class, 'saveStep3']);
    Route::post('/step4/{token}', [VendorRegistrationController::class, 'saveStep4']);
    Route::get('/data/{token}', [VendorRegistrationController::class, 'getRegistrationData']);
});

// =====================================================
// VENDOR APPROVAL API ROUTES (For Internal Team)
// =====================================================

Route::prefix('vendor/approval')->group(function () {
    
    // Get vendors
    Route::get('/pending', [VendorApprovalController::class, 'getPendingVendors']);
    Route::get('/status/{status}', [VendorApprovalController::class, 'getVendorsByStatus']);
    Route::get('/statistics', [VendorApprovalController::class, 'getStatistics']);
    
    // Vendor details & history
    Route::get('/{id}/details', [VendorApprovalController::class, 'getVendorDetails']);
    Route::get('/{id}/history', [VendorApprovalController::class, 'getVendorHistory']);
    
    // Approval actions
    Route::post('/{id}/approve', [VendorApprovalController::class, 'approveVendor']);
    Route::post('/{id}/reject', [VendorApprovalController::class, 'rejectVendor']);
    Route::post('/{id}/request-revision', [VendorApprovalController::class, 'requestRevision']);
    
    // Edit vendor data (by internal team)
    Route::put('/{id}/company-info', [VendorApprovalController::class, 'updateCompanyInfo']);
    Route::put('/{id}/contact', [VendorApprovalController::class, 'updateContact']);
    Route::put('/{id}/statutory-info', [VendorApprovalController::class, 'updateStatutoryInfo']);
    Route::put('/{id}/bank-details', [VendorApprovalController::class, 'updateBankDetails']);
    Route::put('/{id}/tax-info', [VendorApprovalController::class, 'updateTaxInfo']);
    Route::put('/{id}/business-profile', [VendorApprovalController::class, 'updateBusinessProfile']);
});