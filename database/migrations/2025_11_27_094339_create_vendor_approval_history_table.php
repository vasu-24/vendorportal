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
        Schema::create('vendor_approval_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            
            // Action details
            $table->enum('action', [
                'submitted',
                'pending_approval',
                'approved',
                'rejected',
                'revision_requested',
                'resubmitted',
                'data_updated'
            ]);
            
            // Who performed action
            $table->string('action_by_type', 50)->default('user'); // 'user' or 'vendor'
            $table->unsignedBigInteger('action_by_id')->nullable(); // user_id or vendor_id
            $table->string('action_by_name', 255)->nullable(); // Store name for quick display
            
            // Action details
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Data snapshot (optional - stores data at that moment)
            $table->json('data_snapshot')->nullable();
            
            // What fields changed (for data_updated action)
            $table->json('changed_fields')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Foreign key
            $table->foreign('vendor_id')
                  ->references('id')
                  ->on('vendors')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('vendor_id');
            $table->index('action');
            $table->index('action_by_type');
            $table->index('action_by_id');
            $table->index('created_at');
            $table->index(['vendor_id', 'action']);
            $table->index(['vendor_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_approval_history');
    }
};