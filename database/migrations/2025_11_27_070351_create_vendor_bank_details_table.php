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
        Schema::create('vendor_bank_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->unique();
            
            // Bank details
            $table->string('bank_name', 255)->nullable();
            $table->text('branch_address')->nullable();
            $table->string('account_holder_name', 255)->nullable();
            $table->string('account_number', 30)->nullable();
            $table->string('ifsc_code', 11)->nullable();
            $table->enum('account_type', ['Current', 'Savings'])->nullable();
            $table->string('cancelled_cheque_path', 500)->nullable();
            
            // Verification status (for admin/finance use)
            $table->boolean('bank_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->text('verification_remarks')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraint
            $table->foreign('vendor_id')
                  ->references('id')
                  ->on('vendors')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('bank_name');
            $table->index('ifsc_code');
            $table->index('account_number');
            $table->index('bank_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_bank_details');
    }
};