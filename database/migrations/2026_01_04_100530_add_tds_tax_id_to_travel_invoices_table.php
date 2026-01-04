<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travel_invoices', function (Blueprint $table) {
            $table->string('tds_tax_id')->nullable()->after('tds_percent');
        });
    }

    public function down(): void
    {
        Schema::table('travel_invoices', function (Blueprint $table) {
            $table->dropColumn('tds_tax_id');
        });
    }
};