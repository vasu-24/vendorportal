<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First update any 'travel' records to 'normal' (if any exist)
        DB::statement("UPDATE invoices SET invoice_type = 'normal' WHERE invoice_type = 'travel'");
        
        // Change ENUM to only have 'normal' and 'adhoc'
        DB::statement("ALTER TABLE invoices MODIFY COLUMN invoice_type ENUM('normal', 'adhoc') DEFAULT 'normal'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY COLUMN invoice_type ENUM('normal', 'travel') DEFAULT 'normal'");
    }
};