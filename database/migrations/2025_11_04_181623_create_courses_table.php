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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->text('requirements')->nullable();
            $table->text('what_you_learn')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('regular_price', 10, 2)->nullable();
            $table->string('level')->default('beginner'); // beginner, intermediate, advanced
            $table->string('language')->default('en');
            $table->string('status')->default('draft'); // draft, published, archived
            $table->boolean('is_free')->default(false);
            $table->boolean('certificate_available')->default(true);
            $table->integer('duration_hours')->nullable();
            $table->integer('total_students')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->json('tags')->nullable();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('instructors')->onDelete('cascade');
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
