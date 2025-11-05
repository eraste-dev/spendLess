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
        Schema::table('user_settings', function (Blueprint $table) {
            $table->string('thousand_separator', 5)->default(' ')->after('currency_symbol');
            $table->string('decimal_separator', 5)->default(',')->after('thousand_separator');
            $table->unsignedTinyInteger('decimal_places')->default(2)->after('decimal_separator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn(['thousand_separator', 'decimal_separator', 'decimal_places']);
        });
    }
};
