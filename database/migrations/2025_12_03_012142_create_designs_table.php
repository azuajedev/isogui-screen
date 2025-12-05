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
        Schema::create('designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('canvas_type', ['vertical', 'horizontal', 'template'])->default('vertical');
            $table->json('canvas_config'); // Dimensiones, fondo, etc.
            $table->json('canvas_images')->nullable(); // Array de imágenes insertadas
            $table->json('texts')->nullable(); // Textos del mockup
            $table->string('thumbnail_path')->nullable(); // Preview
            $table->timestamp('last_edited_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('user_id');
            $table->index('canvas_type');
            $table->index('last_edited_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('designs');
    }
};
