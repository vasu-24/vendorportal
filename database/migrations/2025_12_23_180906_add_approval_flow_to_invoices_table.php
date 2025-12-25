<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Current approver role
            $table->string('current_approver_role')->nullable()->after('status');
            
            // RM Approval
            $table->unsignedBigInteger('rm_approved_by')->nullable()->after('rejected_by');
            $table->timestamp('rm_approved_at')->nullable()->after('rm_approved_by');
            
            // VP Approval
            $table->unsignedBigInteger('vp_approved_by')->nullable()->after('rm_approved_at');
            $table->timestamp('vp_approved_at')->nullable()->after('vp_approved_by');
            
            // CEO Approval
            $table->unsignedBigInteger('ceo_approved_by')->nullable()->after('vp_approved_at');
            $table->timestamp('ceo_approved_at')->nullable()->after('ceo_approved_by');
            
            // Finance Approval
            $table->unsignedBigInteger('finance_approved_by')->nullable()->after('ceo_approved_at');
            $table->timestamp('finance_approved_at')->nullable()->after('finance_approved_by');
            
            // Exceeds contract flag
            $table->boolean('exceeds_contract')->default(false)->after('current_approver_role');
        });
        
        // Update status enum to include new statuses
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'submitted', 'under_review', 'pending_rm', 'pending_vp', 'pending_ceo', 'pending_finance', 'approved', 'rejected', 'resubmitted', 'paid') DEFAULT 'draft'");
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'current_approver_role',
                'exceeds_contract',
                'rm_approved_by',
                'rm_approved_at',
                'vp_approved_by',
                'vp_approved_at',
                'ceo_approved_by',
                'ceo_approved_at',
                'finance_approved_by',
                'finance_approved_at',
            ]);
        });
    }
};