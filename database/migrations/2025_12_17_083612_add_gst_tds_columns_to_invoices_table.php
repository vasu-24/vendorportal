<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // GST Fields
            $table->decimal('gst_percent', 5, 2)->default(18)->after('grand_total');
            $table->decimal('gst_amount', 15, 2)->default(0)->after('gst_percent');
            $table->string('zoho_gst_tax_id')->nullable()->after('gst_amount');
            
            // TDS Fields
            $table->decimal('tds_percent', 5, 2)->default(5)->after('zoho_gst_tax_id');
            $table->decimal('tds_amount', 15, 2)->default(0)->after('tds_percent');
            
            // Net Payable
            $table->decimal('net_payable', 15, 2)->default(0)->after('tds_amount');
            
            // Timesheet Fields
            $table->boolean('include_timesheet')->default(false)->after('net_payable');
            $table->string('timesheet_path')->nullable()->after('include_timesheet');
            $table->string('timesheet_filename')->nullable()->after('timesheet_path');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'gst_percent',
                'gst_amount',
                'zoho_gst_tax_id',
                'tds_percent',
                'tds_amount',
                'net_payable',
                'include_timesheet',
                'timesheet_path',
                'timesheet_filename'
            ]);
        });
    }
};