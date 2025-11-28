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
        Schema::create('vendor_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->unique();
            
            // Contact details
            $table->string('contact_person', 255)->nullable();
            $table->string('designation', 100)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('email', 255)->nullable();
            
            // Additional contact (optional for future)
            $table->string('alternate_mobile', 20)->nullable();
            $table->string('alternate_email', 255)->nullable();
            $table->string('landline', 20)->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraint
            $table->foreign('vendor_id')
                  ->references('id')
                  ->on('vendors')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('contact_person');
            $table->index('mobile');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_contacts');
    }
};