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
        Schema::create('invoice_attachments', function (Blueprint $table) {
            $table->id();
            
            // Invoice Reference
            $table->unsignedBigInteger('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            
            // Attachment Type
            $table->enum('attachment_type', [
                'invoice',           // Main invoice PDF
                'travel_document',   // Additional travel document
                'supporting'         // Any other supporting document
            ])->default('invoice');
            
            // File Details
            $table->string('file_name'); // Original file name
            $table->string('file_path'); // Storage path
            $table->string('file_type', 50)->nullable(); // pdf, jpg, png, etc.
            $table->unsignedBigInteger('file_size')->default(0); // Size in bytes
            
            // Description (optional)
            $table->string('description')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('invoice_id');
            $table->index('attachment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_attachments');
    }
};