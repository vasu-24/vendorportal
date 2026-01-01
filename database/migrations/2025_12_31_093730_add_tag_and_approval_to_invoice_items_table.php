<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('tag_id')->nullable()->after('category_id');
            $table->string('tag_name')->nullable()->after('tag_id');
            $table->boolean('rm_approved')->default(false)->after('amount');
            $table->unsignedBigInteger('rm_approved_by')->nullable()->after('rm_approved');
            $table->timestamp('rm_approved_at')->nullable()->after('rm_approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['tag_id', 'tag_name', 'rm_approved', 'rm_approved_by', 'rm_approved_at']);
        });
    }
};