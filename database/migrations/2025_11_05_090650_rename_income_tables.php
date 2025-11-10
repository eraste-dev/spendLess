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
        // Rename incomes to income_entries first (to free up 'incomes' name)
        Schema::rename('incomes', 'income_entries');

        // Rename income_categories to incomes
        Schema::rename('income_categories', 'incomes');

        // Update the foreign key in income_entries table
        Schema::table('income_entries', function (Blueprint $table) {
            $table->dropForeign(['income_category_id']);
            $table->renameColumn('income_category_id', 'income_id');
        });

        Schema::table('income_entries', function (Blueprint $table) {
            $table->foreign('income_id')->references('id')->on('incomes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the foreign key changes
        Schema::table('income_entries', function (Blueprint $table) {
            $table->dropForeign(['income_id']);
            $table->renameColumn('income_id', 'income_category_id');
        });

        Schema::table('income_entries', function (Blueprint $table) {
            $table->foreign('income_category_id')->references('id')->on('income_categories')->onDelete('cascade');
        });

        // Reverse the table renames
        Schema::rename('incomes', 'income_categories');
        Schema::rename('income_entries', 'incomes');
    }
};
