<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rendered_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screenshot_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->constrained()->onDelete('cascade');
            $table->string('language', 10);
            $table->json('texts');
            $table->string('output_path');
            $table->string('output_format', 10)->default('png');
            $table->integer('width');
            $table->integer('height');
            $table->integer('file_size')->nullable();
            $table->timestamp('rendered_at');
            $table->timestamps();
            $table->softDeletes();
            $table->index('screenshot_id');
            $table->index(['template_id', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rendered_images');
    }
};
