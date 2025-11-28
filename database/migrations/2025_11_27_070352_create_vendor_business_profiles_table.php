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
        Schema::create('vendor_business_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->unique();
            
            // Business profile
            $table->text('core_activities')->nullable();
            $table->string('employee_count', 50)->nullable();
            $table->string('credit_period', 50)->nullable();
            
            // Annual turnover (last 3 FY)
            $table->string('turnover_fy1', 100)->nullable();
            $table->string('turnover_fy2', 100)->nullable();
            $table->string('turnover_fy3', 100)->nullable();
            
            // Additional business info (for future)
            $table->string('industry_type', 100)->nullable();
            $table->string('business_category', 100)->nullable();
            $table->integer('years_in_business')->nullable();
            $table->text('major_clients')->nullable();
            $table->text('certifications')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraint
            $table->foreign('vendor_id')
                  ->references('id')
                  ->on('vendors')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('employee_count');
            $table->index('industry_type');
            $table->index('business_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_business_profiles');
    }
};