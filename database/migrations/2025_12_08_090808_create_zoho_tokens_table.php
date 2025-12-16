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
        // Create zoho_tokens table
        Schema::create('zoho_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('access_token', 2000);
            $table->string('refresh_token', 2000)->nullable();
            $table->string('token_type')->default('Bearer');
            $table->integer('expires_in')->default(3600);
            $table->timestamp('expires_at')->nullable();
            $table->string('organization_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add zoho columns to vendors table
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('zoho_contact_id')->nullable()->after('approval_status');
            $table->timestamp('zoho_synced_at')->nullable()->after('zoho_contact_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoho_tokens');
        
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['zoho_contact_id', 'zoho_synced_at']);
        });
    }
};