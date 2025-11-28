<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_name');
            $table->string('vendor_email');
            $table->foreignId('template_id')->nullable()->constrained('mail_templates')->onDelete('set null');
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->string('token')->unique()->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};