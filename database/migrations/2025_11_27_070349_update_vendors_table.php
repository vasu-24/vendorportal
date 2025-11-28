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
            // Registration tracking fields
            $table->unsignedTinyInteger('current_step')->default(0)->after('status');
            $table->boolean('registration_completed')->default(false)->after('current_step');
            $table->timestamp('registration_completed_at')->nullable()->after('registration_completed');
            
            // Declaration fields
            $table->string('digital_signature', 255)->nullable()->after('registration_completed_at');
            $table->boolean('declaration_accurate')->default(false)->after('digital_signature');
            $table->boolean('declaration_authorized')->default(false)->after('declaration_accurate');
            $table->boolean('declaration_terms')->default(false)->after('declaration_authorized');
            
            // Indexes for faster queries
            $table->index('status');
            $table->index('registration_completed');
            $table->index('current_step');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['status']);
            $table->dropIndex(['registration_completed']);
            $table->dropIndex(['current_step']);
            $table->dropIndex(['created_at']);
            
            // Drop columns
            $table->dropColumn([
                'current_step',
                'registration_completed',
                'registration_completed_at',
                'digital_signature',
                'declaration_accurate',
                'declaration_authorized',
                'declaration_terms',
            ]);
        });
    }
};