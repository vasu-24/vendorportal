<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailTemplateController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\Api\VendorRegistrationController;
use App\Http\Controllers\Auth\AuthController;

// =====================================================
// AUTH ROUTES (Login/Logout)
// =====================================================

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// =====================================================
// PROTECTED ROUTES (Need Login)
// =====================================================

Route::middleware(['auth'])->group(function () {

    // Dashboard
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

    // =====================================================
    // ADMIN ROUTES (Users & Roles Pages)
    // =====================================================
    
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Users pages
        Route::get('/users', function () {
            return view('pages.admin.users.index');
        })->name('users.index')->middleware('permission:view-users');

        // Roles pages
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