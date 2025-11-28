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
        Schema::create('vendor_company_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->unique();
            
            // Company details
            $table->string('legal_entity_name', 255);
            $table->string('business_type', 100)->nullable();
            $table->date('incorporation_date')->nullable();
            $table->text('registered_address')->nullable();
            $table->text('corporate_address')->nullable();
            $table->string('website', 255)->nullable();
            $table->string('parent_company', 255)->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraint
            $table->foreign('vendor_id')
                  ->references('id')
                  ->on('vendors')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('legal_entity_name');
            $table->index('business_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_company_infos');
    }
};