<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_invoice_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_invoice_id')->constrained('travel_invoices')->onDelete('cascade');
            
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->bigInteger('file_size')->nullable();
            
            $table->timestamps();
            
            $table->index('travel_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_invoice_bills');
    }
};