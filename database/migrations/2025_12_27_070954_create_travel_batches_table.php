<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->integer('total_invoices')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            
            // Status - Changed from enum to string for all statuses
            $table->string('status')->default('draft');
            $table->string('current_approver_role')->nullable();
            $table->string('rejected_by_role')->nullable();
            
            // Timestamps
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('rm_approved_at')->nullable();
            $table->timestamp('vp_approved_at')->nullable();
            $table->timestamp('ceo_approved_at')->nullable();
            $table->timestamp('finance_approved_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('vendor_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_batches');
    }
};