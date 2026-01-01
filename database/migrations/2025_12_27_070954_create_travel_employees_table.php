<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name');
            $table->string('employee_code')->nullable();
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('tag_id');                    // Project Code
            $table->string('tag_name');                  // Project Name
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('tag_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_employees');
    }
};