<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\MailTemplateController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\Auth\VendorAuthController;
use App\Http\Controllers\Auth\VendorPasswordController;
use App\Http\Controllers\Api\VendorRegistrationController;
   use App\Http\Controllers\ZohoController;
   use App\Http\Controllers\Api\ContractController as ApiContractController;


// =====================================================
// INTERNAL TEAM AUTH ROUTES (Login/Logout)
// =====================================================

Route::get('/internal/auth/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/internal/auth/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/internal/auth/logout', [AuthController::class, 'logout'])->name('logout');

// =====================================================
// VENDOR AUTH ROUTES (Login/Logout)
// =====================================================

Route::get('/login', [VendorAuthController::class, 'showLogin'])->name('vendor.login');
Route::post('/login', [VendorAuthController::class, 'login'])->name('vendor.login.submit');
Route::post('/vendor/logout', [VendorAuthController::class, 'logout'])->name('vendor.logout');

// =====================================================
// PROTECTED ROUTES (Need Login)
// =====================================================

Route::middleware(['auth'])->group(function () {

    // =====================================================
    // DASHBOARD (All logged in users can see)
    // =====================================================
    
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', function () {
        return view('pages.dashboard');
    })->name('dashboard');

    // =====================================================
    // VENDOR MODULE ROUTES
    // =====================================================
    
    Route::prefix('vendors')->name('vendors.')->group(function () {
        
        // View vendors list - requires view-vendors permission
        Route::get('/', [VendorController::class, 'index'])
            ->name('index')
            ->middleware('permission:view-vendors');
        
        // Create vendor - requires create-vendors permission
        Route::get('/create', [VendorController::class, 'create'])
            ->name('create')
            ->middleware('permission:create-vendors');
        
        Route::post('/store', [VendorController::class, 'store'])
            ->name('store')
            ->middleware('permission:create-vendors');
        
        // Edit vendor - requires edit-vendors permission
        Route::post('/{id}/update-template', [VendorController::class, 'updateTemplate'])
            ->name('updateTemplate')
            ->middleware('permission:edit-vendors');
        
        Route::post('/{id}/send-email', [VendorController::class, 'sendEmail'])
            ->name('sendEmail')
            ->middleware('permission:edit-vendors');
        
        // Vendor Approvals - requires view-vendors permission (for viewing)
        Route::get('/approvals', function () {
            return "Vendor Approval Page Coming Soon";
        })->name('approvals')->middleware('permission:view-vendors');
    });

    // =====================================================
    // VENDOR APPROVAL ROUTES (Internal Team)
    // =====================================================
    
    Route::prefix('vendors/approval')->name('vendors.approval.')->group(function () {
        
        // View approval queue - requires view-vendors permission
        Route::get('/', function () {
            return view('pages.vendors.approval.queue');
        })->name('queue')->middleware('permission:view-vendors');
        
        // Review vendor - requires view-vendors permission (approve/reject checked separately)
        Route::get('/review/{id}', function ($id) {
            return view('pages.vendors.approval.review', compact('id'));
        })->name('review')->middleware('permission:view-vendors');
    });

    // =====================================================
    // MASTER MODULE - TEMPLATES
    // =====================================================
    
    Route::prefix('master')->name('master.')->middleware('permission:manage-templates')->group(function () {
        Route::get('/template', [MailTemplateController::class, 'index'])->name('template');
        Route::post('/template', [MailTemplateController::class, 'store'])->name('template.store');
        Route::get('/template/{id}', [MailTemplateController::class, 'show'])->name('template.show');
        Route::put('/template/{id}', [MailTemplateController::class, 'update'])->name('template.update');
        Route::delete('/template/{id}', [MailTemplateController::class, 'destroy'])->name('template.destroy');
    });

    // =====================================================
    // SETTINGS
    // =====================================================
    
    Route::get('/settings/general', function () {
        return "General Settings Page";
    })->name('settings.general')->middleware('permission:manage-settings');

    // =====================================================
    // ADMIN ROUTES (Users & Roles Management)
    // =====================================================
    
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Users page - requires view-users permission
        Route::get('/users', function () {
            return view('pages.admin.users.index');
        })->name('users.index')->middleware('permission:view-users');

        // Roles page - requires view-roles permission
        Route::get('/roles', function () {
            return view('pages.admin.roles.index');
        })->name('roles.index')->middleware('permission:view-roles');
        
    });

});

// =====================================================
// PUBLIC ROUTES (No Login Required)
// =====================================================

// Vendor Accept/Reject Routes
Route::get('/vendor/accept/{token}', [VendorController::class, 'accept'])->name('vendors.accept');
Route::get('/vendor/reject/{token}', [VendorController::class, 'reject'])->name('vendors.reject');

// Vendor Registration Wizard Routes
Route::get('/vendor/registration/{token}', [VendorRegistrationController::class, 'showWizard'])
    ->name('vendor.registration');

Route::get('/vendor/registration/success/{token}', [VendorRegistrationController::class, 'showSuccess'])
    ->name('vendor.registration.success');

// =====================================================
// VENDOR PROTECTED ROUTES (Need Vendor Login)
// =====================================================

Route::middleware(['vendor.auth'])->prefix('vendor')->name('vendor.')->group(function () {

    // Vendor Dashboard
    Route::get('/dashboard', function () {
        return view('pages.vendor_portal.dashboard');
    })->name('dashboard');

    // Vendor Profile
    Route::get('/profile', function () {
        return view('pages.vendor_portal.profile');
    })->name('profile');

    // Vendor Documents
    Route::get('/documents', function () {
        return view('pages.vendor_portal.documents');
    })->name('documents');

  // Vendor Invoices
Route::get('/invoices', function () {
    return view('pages.vendor_portal.invoices.index');
})->name('invoices.index');

Route::get('/invoices/create', function () {
    return view('pages.vendor_portal.invoices.form', [
        'mode' => 'create',
        'invoiceId' => null
    ]);
})->name('invoices.create');

Route::get('/invoices/{id}', function ($id) {
    return view('pages.vendor_portal.invoices.form', [
        'mode' => 'show',
        'invoiceId' => $id
    ]);
})->name('invoices.show');

Route::get('/invoices/{id}/edit', function ($id) {
    return view('pages.vendor_portal.invoices.form', [
        'mode' => 'edit',
        'invoiceId' => $id
    ]);
})->name('invoices.edit');

    // Vendor Settings
    Route::get('/settings', function () {
        return view('pages.vendor_portal.settings');
    })->name('settings');


     Route::get('/contracts', function () {
        return view('pages.vendor_portal.contracts.index');
    })->name('contracts.index');

});




// Vendor Set Password Routes (Public)
Route::get('/vendor/set-password/{token}', [VendorPasswordController::class, 'showSetPasswordForm'])
    ->name('vendor.password.show');

Route::post('/vendor/set-password/{token}', [VendorPasswordController::class, 'setPassword'])
    ->name('vendor.password.set');


 // =====================================================
// CONTRACT ROUTES (Admin - Requires Auth)
// =====================================================

Route::prefix('contracts')->middleware(['auth'])->name('contracts.')->group(function () {
    Route::get('/', [ContractController::class, 'index'])->name('index');
    Route::get('/create', [ContractController::class, 'create'])->name('create');
    Route::get('/{id}/edit', [ContractController::class, 'edit'])->name('edit');
    Route::get('/preview', [ContractController::class, 'preview'])->name('preview');
    Route::post('/download', [ContractController::class, 'download'])->name('download');
      // Preview uploaded contract document
    Route::get('/preview-uploaded', [ApiContractController::class, 'previewUploadedDocument'])->name('preview.uploaded');
});





//   Zoho OAuth Callback (No auth required - Zoho redirects here)
Route::get('/zoho/callback', [ZohoController::class, 'callback'])->name('zoho.callback');

// Zoho Settings & Integration (Auth required)
Route::middleware(['auth'])->prefix('settings')->group(function () {
    
    // Zoho Settings Page
    Route::get('/zoho', [ZohoController::class, 'index'])->name('settings.zoho');
    
    // Connect to Zoho
    Route::get('/zoho/connect', [ZohoController::class, 'connect'])->name('zoho.connect');
    
    // Disconnect from Zoho
    Route::post('/zoho/disconnect', [ZohoController::class, 'disconnect'])->name('zoho.disconnect');
    
    // Set Organization
    Route::post('/zoho/organization', [ZohoController::class, 'setOrganization'])->name('zoho.organization');
    
    // Test Connection
    Route::get('/zoho/test', [ZohoController::class, 'test'])->name('zoho.test');
    
    // Get Status (API)
    Route::get('/zoho/status', [ZohoController::class, 'status'])->name('zoho.status');
});


// =====================================================
// Create Organisation master route
// =====================================================

Route::get('/master/organisation', [OrganisationController::class, 'index'])
    ->name('master.organisation');

Route::post('/master/organisation', [OrganisationController::class, 'store'])
    ->name('master.organisation.store');



    Route::prefix('invoices')->middleware(['auth'])->group(function () {
    
    // Invoice List Page
    Route::get('/', function () {
        return view('pages.invoices.index');
    })->name('invoices.index');
    
    // Invoice Details Page
    Route::get('/{id}', function ($id) {
        return view('pages.invoices.show', ['invoiceId' => $id]);
    })->name('invoices.show');

});




Route::get('/categories', function () {
    return view('pages.master.categories.index');
})->name('categories.index')->middleware('auth');



Route::prefix('contracts')->middleware(['auth'])->name('contracts.')->group(function () {
    
    // Pages
    Route::get('/', [App\Http\Controllers\ContractController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\ContractController::class, 'create'])->name('create');
    Route::get('/{id}/edit', [App\Http\Controllers\ContractController::class, 'edit'])->name('edit');
    
    // Preview template (PDF in iframe)
    Route::get('/preview', [App\Http\Controllers\ContractController::class, 'preview'])->name('preview');
    
    // Preview uploaded document
    Route::get('/preview-document', [App\Http\Controllers\ContractController::class, 'previewDocument'])->name('preview.document');
    
    // Download Word file (auto-download after create)
    Route::get('/{id}/download-word', [App\Http\Controllers\ContractController::class, 'downloadWord'])->name('download-word');
});

































































































