<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_invoices', function (Blueprint $table) {
            $table->id();
            
            // Batch & Vendor
            $table->foreignId('batch_id')->constrained('travel_batches')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            
            // Employee (1 invoice = 1 person)
            $table->foreignId('employee_id')->constrained('travel_employees')->onDelete('cascade');
            
            // Invoice Details
            $table->string('invoice_number');
            $table->enum('invoice_type', ['tax_invoice', 'credit_note'])->default('tax_invoice');
            $table->date('invoice_date');
            $table->unsignedBigInteger('reference_invoice_id')->nullable(); // For credit note
            
            // Project/Tag (auto-filled from employee)
            $table->string('tag_id');
            $table->string('tag_name');
            $table->string('project_code')->nullable();  // Same as tag_id but explicit
            
            // Travel Details
            $table->string('location')->nullable();
            $table->enum('travel_type', ['domestic', 'international'])->default('domestic');
            $table->date('travel_date')->nullable();
            $table->text('description')->nullable();
            
            // Amounts (calculated from items)
            $table->decimal('basic_total', 15, 2)->default(0);
            $table->decimal('taxes_total', 15, 2)->default(0);
            $table->decimal('service_charge_total', 15, 2)->default(0);
            $table->decimal('gst_total', 15, 2)->default(0);
            $table->decimal('gross_amount', 15, 2)->default(0);
            $table->decimal('tds_percent', 5, 2)->default(5);
            $table->decimal('tds_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            
            // Status & Approval Flow
            $table->enum('status', [
                'draft', 
                'submitted', 
                'resubmitted',
                'pending_rm', 
                'pending_vp', 
                'pending_ceo', 
                'pending_finance',
                'approved', 
                'rejected', 
                'paid'
            ])->default('draft');
            $table->string('current_approver_role')->nullable();
            $table->boolean('exceeds_contract')->default(false);
            
            // Assigned RM (auto from employee's tag)
            $table->foreignId('assigned_rm_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Approval Tracking
            $table->foreignId('rm_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rm_approved_at')->nullable();
            $table->foreignId('vp_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('vp_approved_at')->nullable();
            $table->foreignId('ceo_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ceo_approved_at')->nullable();
            $table->foreignId('finance_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finance_approved_at')->nullable();
            
            // Final Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            // Rejection
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('rejected_by_role')->nullable();
            
            // Timestamps
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Zoho Integration
            $table->string('zoho_bill_id')->nullable();
            $table->timestamp('zoho_synced_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['vendor_id', 'status']);
            $table->index('batch_id');
            $table->index('employee_id');
            $table->index('tag_id');
            $table->index('assigned_rm_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_invoices');
    }
};