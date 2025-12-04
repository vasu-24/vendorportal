<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VendorRegistrationController;
use App\Http\Controllers\Api\VendorApprovalController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;

// =====================================================
// VENDOR REGISTRATION API ROUTES (For Vendors - No Auth)
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

Route::prefix('vendor/approval')->middleware(['web', 'auth', 'permission:view-vendors'])->group(function () {
    
    // View routes
    Route::get('/pending', [VendorApprovalController::class, 'getPendingVendors']);
    Route::get('/status/{status}', [VendorApprovalController::class, 'getVendorsByStatus']);
    Route::get('/statistics', [VendorApprovalController::class, 'getStatistics']);
    Route::get('/{id}/details', [VendorApprovalController::class, 'getVendorDetails']);
    Route::get('/{id}/history', [VendorApprovalController::class, 'getVendorHistory']);
    
    // Approval actions
    Route::post('/{id}/approve', [VendorApprovalController::class, 'approveVendor'])->middleware('permission:approve-vendors');
    Route::post('/{id}/reject', [VendorApprovalController::class, 'rejectVendor'])->middleware('permission:reject-vendors');
    Route::post('/{id}/request-revision', [VendorApprovalController::class, 'requestRevision'])->middleware('permission:edit-vendors');
    
    // Edit routes
    Route::put('/{id}/company-info', [VendorApprovalController::class, 'updateCompanyInfo'])->middleware('permission:edit-vendors');
    Route::put('/{id}/contact', [VendorApprovalController::class, 'updateContact'])->middleware('permission:edit-vendors');
    Route::put('/{id}/statutory-info', [VendorApprovalController::class, 'updateStatutoryInfo'])->middleware('permission:edit-vendors');
    Route::put('/{id}/bank-details', [VendorApprovalController::class, 'updateBankDetails'])->middleware('permission:edit-vendors');
    Route::put('/{id}/tax-info', [VendorApprovalController::class, 'updateTaxInfo'])->middleware('permission:edit-vendors');
    Route::put('/{id}/business-profile', [VendorApprovalController::class, 'updateBusinessProfile'])->middleware('permission:edit-vendors');
});

// =====================================================
// USER MANAGEMENT API ROUTES (For Internal Team)
// =====================================================

Route::prefix('admin/users')->middleware(['web', 'auth', 'permission:view-users'])->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/roles', [UserController::class, 'getRoles']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store'])->middleware('permission:create-users');
    Route::put('/{id}', [UserController::class, 'update'])->middleware('permission:edit-users');
    Route::delete('/{id}', [UserController::class, 'destroy'])->middleware('permission:delete-users');
});

// =====================================================
// ROLE MANAGEMENT API ROUTES (For Internal Team)
// =====================================================

Route::prefix('admin/roles')->middleware(['web', 'auth', 'permission:view-roles'])->group(function () {
    Route::get('/', [RoleController::class, 'index']);
    Route::get('/permissions', [RoleController::class, 'getPermissions']);
    Route::get('/{id}', [RoleController::class, 'show']);
    Route::post('/', [RoleController::class, 'store'])->middleware('permission:create-roles');
    Route::put('/{id}', [RoleController::class, 'update'])->middleware('permission:edit-roles');
    Route::delete('/{id}', [RoleController::class, 'destroy'])->middleware('permission:delete-roles');
});