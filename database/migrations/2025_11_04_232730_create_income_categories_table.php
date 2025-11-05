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
        Schema::create('income_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // ex: Salaire, Freelance, Investissements
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2)->nullable(); // Montant estimé/prévu
            $table->boolean('is_monthly')->default(false); // Si c'est un revenu mensuel
            $table->date('income_date')->nullable(); // Date du revenu si non mensuel
            $table->string('color')->default('#3b82f6'); // Couleur pour visualisation
            $table->integer('order')->default(0); // Ordre d'affichage
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_categories');
    }
};
