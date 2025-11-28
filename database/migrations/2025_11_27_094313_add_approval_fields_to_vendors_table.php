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
        Schema::table('vendors', function (Blueprint $table) {
            // Approval status
            $table->enum('approval_status', [
                'draft',
                'pending_approval',
                'approved',
                'rejected',
                'revision_requested'
            ])->default('draft')->after('registration_completed_at');
            
            // Approval details
            $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            
            // Rejection details
            $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
            
            // Revision details
            $table->unsignedBigInteger('revision_requested_by')->nullable()->after('rejection_reason');
            $table->timestamp('revision_requested_at')->nullable()->after('revision_requested_by');
            $table->text('revision_notes')->nullable()->after('revision_requested_at');
            
            // Indexes
            $table->index('approval_status');
            $table->index('approved_by');
            $table->index('rejected_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['approval_status']);
            $table->dropIndex(['approved_by']);
            $table->dropIndex(['rejected_by']);
            
            // Drop columns
            $table->dropColumn([
                'approval_status',
                'approved_by',
                'approved_at',
                'rejected_by',
                'rejected_at',
                'rejection_reason',
                'revision_requested_by',
                'revision_requested_at',
                'revision_notes',
            ]);
        });
    }
};