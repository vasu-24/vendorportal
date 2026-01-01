<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travel_invoices', function (Blueprint $table) {
            $table->timestamp('vp_pending_since')->nullable()->after('rm_approved_at');
            $table->boolean('auto_escalated')->default(false)->after('rejection_reason');
            $table->timestamp('auto_escalated_at')->nullable()->after('auto_escalated');
            $table->string('escalation_reason')->nullable()->after('auto_escalated_at');
        });
    }

    public function down(): void
    {
        Schema::table('travel_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'vp_pending_since',
                'auto_escalated',
                'auto_escalated_at',
                'escalation_reason',
            ]);
        });
    }
};