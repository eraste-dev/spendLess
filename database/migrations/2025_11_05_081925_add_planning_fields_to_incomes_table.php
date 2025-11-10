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
        Schema::table('incomes', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->after('income_date');
            $table->unsignedTinyInteger('month')->after('year');
            $table->boolean('is_planned')->default(false)->after('month');

            $table->index(['user_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'year', 'month']);
            $table->dropColumn(['year', 'month', 'is_planned']);
        });
    }
};
