<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screenshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('original_filename');
            $table->string('stored_path');
            $table->enum('orientation', ['horizontal', 'vertical']);
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('project_id');
            $table->index('orientation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screenshots');
    }
};
