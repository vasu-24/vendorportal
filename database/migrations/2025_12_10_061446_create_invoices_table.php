<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            
            // Vendor Reference
            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            
            // Contract Reference
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('set null');
            
            // Invoice Type
            $table->enum('invoice_type', ['normal', 'travel'])->default('normal');
            
            // Invoice Details
            $table->string('invoice_number', 50);
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();
            
            // Amount Details (New Structure)
            $table->decimal('base_total', 15, 2)->default(0); // Sum of all line items (before tax)
            $table->decimal('gst_total', 15, 2)->default(0); // Total GST (CGST + SGST or IGST)
            $table->decimal('grand_total', 15, 2)->default(0); // Final total (base + gst)
            
            // Currency (default INR)
            $table->string('currency', 3)->default('INR');
            
            // Status
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'approved',
                'rejected',
                'resubmitted',
                'paid'
            ])->default('draft');
            
            // Remarks / Notes
            $table->text('remarks')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Submission tracking
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Who reviewed/approved/rejected
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            
            // Zoho Integration (for future)
            $table->string('zoho_invoice_id')->nullable();
            $table->timestamp('zoho_synced_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('vendor_id');
            $table->index('contract_id');
            $table->index('status');
            $table->index('invoice_type');
            $table->index('invoice_date');
            $table->unique(['vendor_id', 'invoice_number']); // Unique invoice number per vendor
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};