<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Add zoho_tds_tax_id if not exists
            if (!Schema::hasColumn('invoices', 'zoho_tds_tax_id')) {
                $table->string('zoho_tds_tax_id')->nullable()->after('tds_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('zoho_tds_tax_id');
        });
    }
};