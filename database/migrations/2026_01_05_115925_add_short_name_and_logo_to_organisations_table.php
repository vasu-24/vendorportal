<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('organisations', function (Blueprint $table) {
        $table->string('short_name', 20)->nullable()->after('company_name');
        $table->string('logo')->nullable()->after('address');
    });
}

public function down()
{
    Schema::table('organisations', function (Blueprint $table) {
        $table->dropColumn(['short_name', 'logo']);
    });
}};
