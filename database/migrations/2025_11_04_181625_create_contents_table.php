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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['video', 'document', 'text', 'quiz', 'assignment'])->default('video');
            $table->string('video_url')->nullable();
            $table->string('document_path')->nullable();
            $table->text('content_text')->nullable();
            $table->integer('serial_number')->default(1);
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_free')->default(false);
            $table->boolean('is_preview')->default(false);
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('cascade');
            $table->foreignId('media_id')->nullable()->constrained('media')->onDelete('set null');
            $table->timestamp('media_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
