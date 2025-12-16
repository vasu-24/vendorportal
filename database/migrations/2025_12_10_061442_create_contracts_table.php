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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            
            // Contract Number (auto-generated)
            $table->string('contract_number')->unique();
            
            // Template
            $table->string('template_file')->nullable();
            
            // Company (Organisation)
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_cin')->nullable();
            $table->text('company_address')->nullable();
            
            // Vendor
            $table->unsignedBigInteger('vendor_id');
            $table->string('vendor_name')->nullable();
            $table->string('vendor_cin')->nullable();
            $table->text('vendor_address')->nullable();
            
            // Contract Dates
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            
            // Contract Value (sum of all line items)
            $table->decimal('contract_value', 15, 2)->default(0);
            
            // Status
            $table->enum('status', ['draft', 'sent_for_signature', 'signed', 'active', 'expired', 'terminated'])->default('draft');
            
            // Visibility to vendor (for future DocuSign)
            $table->boolean('is_visible_to_vendor')->default(true);
            
            // Document path (signed contract PDF)
            $table->string('document_path')->nullable();
            
            // Notes
            $table->text('notes')->nullable();
            
            // Created by
            $table->unsignedBigInteger('created_by')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('vendor_id');
            $table->index('company_id');
            $table->index('status');
            $table->index('contract_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};