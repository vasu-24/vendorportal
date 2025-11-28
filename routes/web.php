<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailTemplateController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\Api\VendorRegistrationController;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('pages.dashboard');
})->name('dashboard');

// VENDOR MODULE ROUTES
Route::prefix('vendors')->name('vendors.')->group(function () {
    Route::get('/', [VendorController::class, 'index'])->name('index');
    Route::get('/create', [VendorController::class, 'create'])->name('create');
    Route::post('/store', [VendorController::class, 'store'])->name('store');
    Route::post('/{id}/update-template', [VendorController::class, 'updateTemplate'])->name('updateTemplate');
    Route::post('/{id}/send-email', [VendorController::class, 'sendEmail'])->name('sendEmail');
    
    Route::get('/approvals', function () {
        return "Vendor Approval Page Coming Soon";
    })->name('approvals');
});

// Vendor Accept/Reject Routes (No Auth Required)
Route::get('/vendor/accept/{token}', [VendorController::class, 'accept'])->name('vendors.accept');
Route::get('/vendor/reject/{token}', [VendorController::class, 'reject'])->name('vendors.reject');

// Vendor Registration Wizard Routes (No Auth Required)
Route::get('/vendor/registration/{token}', [VendorRegistrationController::class, 'showWizard'])
    ->name('vendor.registration');

Route::get('/vendor/registration/success/{token}', [VendorRegistrationController::class, 'showSuccess'])
    ->name('vendor.registration.success');

// MASTER MODULE - TEMPLATES
Route::prefix('master')->name('master.')->group(function () {
    Route::get('/template', [MailTemplateController::class, 'index'])->name('template');
    Route::post('/template', [MailTemplateController::class, 'store'])->name('template.store');
    Route::get('/template/{id}', [MailTemplateController::class, 'show'])->name('template.show');
    Route::put('/template/{id}', [MailTemplateController::class, 'update'])->name('template.update');
    Route::delete('/template/{id}', [MailTemplateController::class, 'destroy'])->name('template.destroy');
});

// SETTINGS
Route::get('/settings/general', function () {
    return "General Settings Page";
})->name('settings.general');


// Vendor Approval Routes (Internal Team)
Route::prefix('vendors/approval')->name('vendors.approval.')->group(function () {
    Route::get('/', function () {
        return view('pages.vendors.approval.queue');
    })->name('queue');
    
    Route::get('/review/{id}', function ($id) {
        return view('pages.vendors.approval.review', compact('id'));
    })->name('review');
});