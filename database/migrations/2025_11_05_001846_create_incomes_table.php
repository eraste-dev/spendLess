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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('income_category_id')->constrained()->onDelete('cascade');
            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('income_date'); // Date du revenu
            $table->boolean('is_recurring')->default(false); // Si c'est mensuel
            $table->integer('recurrence_day')->nullable(); // Jour de récurrence (1-31)
            $table->date('next_occurrence')->nullable(); // Prochaine occurrence si récurrent
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'income_date']);
            $table->index(['user_id', 'is_recurring']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
