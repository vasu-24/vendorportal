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
        Schema::create('vendor_statutory_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->unique();
            
            // Statutory details
            $table->string('pan_number', 10)->nullable();
            $table->string('tan_number', 10)->nullable();
            $table->string('gstin', 15)->nullable();
            $table->string('cin', 21)->nullable();
            
            // MSME details
            $table->enum('msme_registered', ['Yes', 'No'])->nullable();
            $table->string('udyam_certificate_path', 500)->nullable();
            
            // Verification status (for admin use)
            $table->boolean('pan_verified')->default(false);
            $table->boolean('gst_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraint
            $table->foreign('vendor_id')
                  ->references('id')
                  ->on('vendors')
                  ->onDelete('cascade');
            
            // Indexes for search & verification
            $table->index('pan_number');
            $table->index('gstin');
            $table->index('cin');
            $table->index('msme_registered');
            $table->index('pan_verified');
            $table->index('gst_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_statutory_infos');
    }
};