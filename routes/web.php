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
use App\Http\Controllers\Auth\VendorForgotPasswordController;
use App\Http\Controllers\Auth\VendorChangePasswordController;

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
// PUBLIC ROUTES (No Login Required)
// =====================================================

Route::get('/vendor/accept/{token}', [VendorController::class, 'accept'])->name('vendors.accept');
Route::get('/vendor/reject/{token}', [VendorController::class, 'reject'])->name('vendors.reject');

Route::get('/vendor/registration/{token}', [VendorRegistrationController::class, 'showWizard'])->name('vendor.registration');
Route::get('/vendor/registration/success/{token}', [VendorRegistrationController::class, 'showSuccess'])->name('vendor.registration.success');

Route::get('/vendor/set-password/{token}', [VendorPasswordController::class, 'showSetPasswordForm'])->name('vendor.password.show');
Route::post('/vendor/set-password/{token}', [VendorPasswordController::class, 'setPassword'])->name('vendor.password.set');

Route::get('/zoho/callback', [ZohoController::class, 'callback'])->name('zoho.callback');

// =====================================================
// PROTECTED ROUTES (Need Login)
// =====================================================

Route::middleware(['auth'])->group(function () {

    // =====================================================
    // DASHBOARD
    // =====================================================
    
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', function () {
        return view('pages.dashboard');
    })->name('dashboard')->middleware('permission:view-dashboard');

    // =====================================================
    // VENDOR MODULE
    // =====================================================
    
    Route::prefix('vendors')->name('vendors.')->group(function () {
        
        // Import Routes
        Route::get('/import/template', [VendorController::class, 'downloadImportTemplate'])
            ->name('import.template')
            ->middleware('permission:import-vendors');
            
        Route::post('/import', [VendorController::class, 'import'])
            ->name('import')
            ->middleware('permission:import-vendors');
        
        // List vendors
        Route::get('/', [VendorController::class, 'index'])
            ->name('index')
            ->middleware('permission:view-vendors');
        
        // Create vendor
        Route::get('/create', [VendorController::class, 'create'])
            ->name('create')
            ->middleware('permission:create-vendors');
        
        Route::post('/store', [VendorController::class, 'store'])
            ->name('store')
            ->middleware('permission:create-vendors');
        
        // Approvals
        Route::get('/approvals', function () {
            return "Vendor Approval Page Coming Soon";
        })->name('approvals')->middleware('permission:view-vendors');
        
        // Edit vendor
        Route::post('/{id}/update-template', [VendorController::class, 'updateTemplate'])
            ->name('updateTemplate')
            ->middleware('permission:edit-vendors');
        
        Route::post('/{id}/send-email', [VendorController::class, 'sendEmail'])
            ->name('sendEmail')
            ->middleware('permission:edit-vendors');
    });
    
    // =====================================================
    // VENDOR APPROVAL ROUTES
    // =====================================================
    
    Route::prefix('vendors/approval')->name('vendors.approval.')->group(function () {
        
        Route::get('/', function () {
            return view('pages.vendors.approval.queue');
        })->name('queue')->middleware('permission:view-vendors');
        
        Route::get('/review/{id}', function ($id) {
            return view('pages.vendors.approval.review', compact('id'));
        })->name('review')->middleware('permission:view-vendors');
    });

    // =====================================================
    // CONTRACT MODULE
    // =====================================================
    
    Route::prefix('contracts')->name('contracts.')->group(function () {
        
        Route::get('/', [ContractController::class, 'index'])
            ->name('index')
            ->middleware('permission:view-contracts');
            
        Route::get('/create', [ContractController::class, 'create'])
            ->name('create')
            ->middleware('permission:create-contracts');
            
        Route::get('/{id}/edit', [ContractController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:edit-contracts');
            
        Route::get('/preview', [ContractController::class, 'preview'])
            ->name('preview')
            ->middleware('permission:view-contracts');
            
        Route::post('/download', [ContractController::class, 'download'])
            ->name('download')
            ->middleware('permission:view-contracts');
            
        Route::get('/preview-uploaded', [ApiContractController::class, 'previewUploadedDocument'])
            ->name('preview.uploaded')
            ->middleware('permission:view-contracts');
            
        Route::get('/preview-document', [ContractController::class, 'previewDocument'])
            ->name('preview.document')
            ->middleware('permission:view-contracts');
            
        Route::match(['get', 'post'], '/{id}/download-word', [ContractController::class, 'downloadWord'])
            ->name('download-word')
            ->middleware('permission:view-contracts');
    });

    // =====================================================
    // INVOICE MODULE
    // =====================================================
    
    Route::prefix('invoices')->name('invoices.')->group(function () {
        
        Route::get('/', function () {
            return view('pages.invoices.index');
        })->name('index')->middleware('permission:view-invoices');
        
        Route::get('/{id}', function ($id) {
            return view('pages.invoices.show', ['invoiceId' => $id]);
        })->name('show')->middleware('permission:view-invoices');
    });

    // =====================================================
    // CATEGORY MODULE
    // =====================================================
    
    Route::get('/categories', function () {
        return view('pages.master.categories.index');
    })->name('categories.index')->middleware('permission:view-categories');

    // =====================================================
    // TEMPLATE MODULE
    // =====================================================
    
   // =====================================================
// TEMPLATE MODULE
// =====================================================

Route::prefix('master')->middleware('permission:manage-templates')->group(function () {
    Route::get('/template', [MailTemplateController::class, 'index'])->name('master.template');
    Route::post('/template', [MailTemplateController::class, 'store'])->name('master.template.store');
    Route::get('/template/{id}', [MailTemplateController::class, 'show'])->name('master.template.show');
    Route::put('/template/{id}', [MailTemplateController::class, 'update'])->name('master.template.update');
    Route::delete('/template/{id}', [MailTemplateController::class, 'destroy'])->name('master.template.destroy');
});

    // =====================================================
    // ORGANISATION MODULE
    // =====================================================
    
    Route::get('/master/organisation', [OrganisationController::class, 'index'])
        ->name('master.organisation')
        ->middleware('permission:view-organisations');
        
    Route::post('/master/organisation', [OrganisationController::class, 'store'])
        ->name('master.organisation.store')
        ->middleware('permission:create-organisations');

    // =====================================================
    // MANAGER TAGS MODULE
    // =====================================================
    
    Route::get('/master/manager-tags', function () {
        return view('pages.master.manager-tags.index');
    })->name('master.manager-tags')->middleware('permission:view-manager-tags');

    // =====================================================
    // USER MODULE
    // =====================================================
    
    Route::prefix('admin')->name('admin.')->group(function () {
        
        Route::get('/users', function () {
            return view('pages.admin.users.index');
        })->name('users.index')->middleware('permission:view-users');

        Route::get('/roles', function () {
            return view('pages.admin.roles.index');
        })->name('roles.index')->middleware('permission:view-roles');
    });

    // =====================================================
    // ZOHO SETTINGS MODULE
    // =====================================================
    
    Route::prefix('settings')->group(function () {
        
        Route::get('/general', function () {
            return "General Settings Page";
        })->name('settings.general')->middleware('permission:manage-settings');
        
        Route::get('/zoho', [ZohoController::class, 'index'])
            ->name('settings.zoho')
            ->middleware('permission:manage-zoho');
            
        Route::get('/zoho/connect', [ZohoController::class, 'connect'])
            ->name('zoho.connect')
            ->middleware('permission:manage-zoho');
            
        Route::post('/zoho/disconnect', [ZohoController::class, 'disconnect'])
            ->name('zoho.disconnect')
            ->middleware('permission:manage-zoho');
            
        Route::post('/zoho/organization', [ZohoController::class, 'setOrganization'])
            ->name('zoho.organization')
            ->middleware('permission:manage-zoho');
            
        Route::get('/zoho/test', [ZohoController::class, 'test'])
            ->name('zoho.test')
            ->middleware('permission:manage-zoho');
            
        Route::get('/zoho/status', [ZohoController::class, 'status'])
            ->name('zoho.status')
            ->middleware('permission:manage-zoho');
    });

});

// =====================================================
// VENDOR PORTAL ROUTES (Need Vendor Login)
// =====================================================

Route::middleware(['vendor.auth'])->prefix('vendor')->name('vendor.')->group(function () {

    Route::get('/dashboard', function () {
        return view('pages.vendor_portal.dashboard');
    })->name('dashboard');

    Route::get('/profile', function () {
        return view('pages.vendor_portal.profile');
    })->name('profile');

    Route::get('/documents', function () {
        return view('pages.vendor_portal.documents');
    })->name('documents');

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

    Route::get('/settings', function () {
        return view('pages.vendor_portal.settings');
    })->name('settings');

    Route::get('/contracts', function () {
        return view('pages.vendor_portal.contracts.index');
    })->name('contracts.index');

});






// =====================================================
// TRAVEL INVOICE WEB ROUTES
// =====================================================
// Add these routes to your routes/web.php file

/*
|--------------------------------------------------------------------------
| ADMIN PORTAL - Travel Employee Master Routes
| Path: resources/views/pages/master/travel-employees/
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Travel Employee Master
    Route::get('/master/travel-employees', function () {
        return view('pages.master.travel-employees.index');
    })->name('travel-employees.index');

});


/*
|--------------------------------------------------------------------------
| ADMIN PORTAL - Travel Invoice Routes
| Path: resources/views/pages/Travelinvoice/
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Travel Invoices List
    Route::get('/travel-invoices', function () {
        return view('pages.Travelinvoice.index');
    })->name('travel-invoices.index');

    // Travel Invoice Batch Summary (Show all invoices in a batch)
    Route::get('/travel-invoices/batch/{batchId}', function ($batchId) {
        return view('pages.Travelinvoice.batch', ['batchId' => $batchId]);
    })->name('travel-invoices.batch');

    // Travel Invoice Detail/Show
    Route::get('/travel-invoices/{id}', function ($id) {
        return view('pages.Travelinvoice.show', ['id' => $id]);
    })->name('travel-invoices.show');

});


/*
|--------------------------------------------------------------------------
| VENDOR PORTAL - Travel Invoice Routes
| Path: resources/views/pages/vendor_portal/travel-invoices/
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:vendor'])->prefix('vendor')->name('vendor.')->group(function () {

    // Travel Invoices List (Batches)
    Route::get('/travel-invoices', function () {
        return view('pages.vendor_portal.travel-invoices.index');
    })->name('travel-invoices.index');

    // Create Travel Invoice
    Route::get('/travel-invoices/create', function () {
        return view('pages.vendor_portal.travel-invoices.create');
    })->name('travel-invoices.create');

    // View Travel Invoice Detail
    Route::get('/travel-invoices/{id}', function ($id) {
        return view('pages.vendor_portal.travel-invoices.show', ['id' => $id]);
    })->name('travel-invoices.show');

    // Edit Travel Invoice (for rejected invoices)
    Route::get('/travel-invoices/{id}/edit', function ($id) {
        return view('pages.vendor_portal.travel-invoices.edit', ['id' => $id]);
    })->name('travel-invoices.edit');

});











// =====================================================
// ADD THESE ROUTES TO YOUR web.php FILE
// Vendor Forgot Password Routes (OTP Method)
// =====================================================



// Vendor Forgot Password Routes (Guest only - not logged in)
Route::middleware('guest:vendor')->prefix('vendor')->group(function () {
    
    // Step 1: Enter Email
    Route::get('/forgot-password', [VendorForgotPasswordController::class, 'showForgotPasswordForm'])
        ->name('vendor.password.request');
    
    Route::post('/forgot-password', [VendorForgotPasswordController::class, 'sendOtp'])
        ->name('vendor.password.send.otp');
    
    // Step 2: Verify OTP
    Route::get('/verify-otp', [VendorForgotPasswordController::class, 'showVerifyOtpForm'])
        ->name('vendor.password.verify.otp.form');
    
    Route::post('/verify-otp', [VendorForgotPasswordController::class, 'verifyOtp'])
        ->name('vendor.password.verify.otp');
    
    Route::get('/resend-otp', [VendorForgotPasswordController::class, 'resendOtp'])
        ->name('vendor.password.resend.otp');
    
    // Step 3: Reset Password
    Route::get('/reset-password', [VendorForgotPasswordController::class, 'showResetPasswordForm'])
        ->name('vendor.password.reset.form');
    
    Route::post('/reset-password', [VendorForgotPasswordController::class, 'resetPassword'])
        ->name('vendor.password.update');
});










Route::middleware(['auth:vendor'])->prefix('vendor')->group(function () {
    Route::get('/change-password', [VendorChangePasswordController::class, 'showChangePasswordForm'])
        ->name('vendor.change-password');
    
    Route::post('/change-password', [VendorChangePasswordController::class, 'updatePassword'])
        ->name('vendor.change-password.update');
});




// Approval Matrix / Invoice Flow
Route::get('/admin/master/approval-matrix/invoice-flow', function () {
    return view('pages.master.approval_matrix.invoice-flow');
})->middleware(['auth'])->name('master.invoice-flow');