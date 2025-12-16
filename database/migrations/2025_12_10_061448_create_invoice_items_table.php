<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            
            // Invoice Reference
            $table->unsignedBigInteger('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            
            // Contract Item Reference (optional - links to contract config)
            $table->unsignedBigInteger('contract_item_id')->nullable();
            $table->foreign('contract_item_id')->references('id')->on('contract_items')->onDelete('set null');
            
            // Category Reference
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            
            // Line Item Details
            $table->string('particulars')->nullable();
            $table->string('sac', 50)->nullable();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->string('unit', 50)->nullable();
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('invoice_id');
            $table->index('contract_item_id');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};