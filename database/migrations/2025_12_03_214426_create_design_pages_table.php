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
        Schema::create('design_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('design_id')->constrained()->onDelete('cascade');
            $table->string('name')->default('Página 1');
            $table->integer('order')->default(1);
            $table->enum('canvas_type', ['vertical', 'horizontal', 'template'])->default('vertical');
            $table->json('canvas_config');
            $table->json('canvas_images')->nullable();
            $table->json('texts')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->timestamps();

            // Índice para ordenamiento
            $table->index(['design_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_pages');
    }
};
