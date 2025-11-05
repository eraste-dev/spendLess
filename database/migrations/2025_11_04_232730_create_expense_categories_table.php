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
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // ex: Dépenses Fixes, Épargne, Loisirs & Extras
            $table->text('description')->nullable();
            $table->decimal('target_percentage', 5, 2)->nullable(); // ex: 10.00 pour 10%
            $table->decimal('target_amount', 15, 2)->nullable(); // Budget mensuel fixe
            $table->string('color')->default('#8b5cf6'); // Couleur pour visualisation
            $table->integer('order')->default(0); // Ordre d'affichage
            $table->boolean('is_active')->default(true);
            $table->boolean('is_recurring_monthly')->default(false); // Pour clonage automatique
            $table->integer('recurrence_day')->default(1); // Jour du mois pour création automatique
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};
