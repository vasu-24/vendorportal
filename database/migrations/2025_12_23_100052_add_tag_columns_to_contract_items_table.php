<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_items', function (Blueprint $table) {
            $table->string('tag_id')->nullable()->after('rate');
            $table->string('tag_name')->nullable()->after('tag_id');
        });
    }

    public function down(): void
    {
        Schema::table('contract_items', function (Blueprint $table) {
            $table->dropColumn(['tag_id', 'tag_name']);
        });
    }
};