<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_invoice_id')->constrained('travel_invoices')->onDelete('cascade');
            
            // Mode (from Excel: Flight, Cabs, Train, Insurance, Accommodation, Visa)
            $table->enum('mode', [
                'flight', 
                'cabs', 
                'train', 
                'insurance', 
                'accommodation', 
                'visa', 
                'other'
            ])->default('other');
            $table->string('mode_other')->nullable();  // If mode = 'other'
            
            // Details
            $table->text('particulars')->nullable();
            $table->date('expense_date')->nullable();
            
            // Amounts (from Excel)
            $table->decimal('basic', 15, 2)->default(0);           // Basic
            $table->decimal('taxes', 15, 2)->default(0);           // Taxes
            $table->decimal('service_charge', 15, 2)->default(0);  // Management Fee/Service Charge
            $table->decimal('gst', 15, 2)->default(0);             // GST
            $table->decimal('gross_amount', 15, 2)->default(0);    // Gross Amount (Total)
            
            $table->timestamps();
            
            $table->index('travel_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_invoice_items');
    }
};