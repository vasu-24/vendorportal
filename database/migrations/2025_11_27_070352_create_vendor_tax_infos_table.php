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
        Schema::create('vendor_tax_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->unique();
            
            // Tax details
            $table->enum('tax_residency', ['India', 'Other'])->nullable();
            $table->enum('gst_reverse_charge', ['Yes', 'No'])->nullable();
            $table->enum('sez_status', ['Yes', 'No'])->nullable();
            $table->string('tds_exemption_path', 500)->nullable();
            
            // TDS details (for finance)
            $table->string('tds_section', 50)->nullable();
            $table->decimal('tds_rate', 5, 2)->nullable();
            $table->date('tds_valid_from')->nullable();
            $table->date('tds_valid_to')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraint
            $table->foreign('vendor_id')
                  ->references('id')
                  ->on('vendors')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('tax_residency');
            $table->index('gst_reverse_charge');
            $table->index('sez_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_tax_infos');
    }
};