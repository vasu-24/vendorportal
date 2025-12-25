<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_rm_id')->nullable()->after('exceeds_contract');
            $table->string('assigned_tag_id')->nullable()->after('assigned_rm_id');
            $table->string('assigned_tag_name')->nullable()->after('assigned_tag_id');
            
            // Index for faster queries
            $table->index('assigned_rm_id');
            $table->index('assigned_tag_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['assigned_rm_id']);
            $table->dropIndex(['assigned_tag_id']);
            $table->dropColumn(['assigned_rm_id', 'assigned_tag_id', 'assigned_tag_name']);
        });
    }
};