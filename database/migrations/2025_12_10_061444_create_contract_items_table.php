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
        Schema::create('contract_items', function (Blueprint $table) {
            $table->id();
            
            // Contract reference
            $table->unsignedBigInteger('contract_id');
            
            // Category reference
            $table->unsignedBigInteger('category_id');
            
            // Item details
            $table->string('description')->nullable();
            
            // Quantity & Unit
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('unit')->nullable(); // hrs, days, months, nos, etc.
            
            // Rate
            $table->decimal('rate', 15, 2)->default(0);
            
            // Amount (quantity × rate)
            $table->decimal('amount', 15, 2)->default(0);
            
            // Tracking - how much invoiced against this item
            $table->decimal('invoiced_quantity', 10, 2)->default(0);
            $table->decimal('invoiced_amount', 15, 2)->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('contract_id');
            $table->index('category_id');
            
            // Foreign keys
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_items');
    }
};