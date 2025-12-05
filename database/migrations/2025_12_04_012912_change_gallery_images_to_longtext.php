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
        Schema::table('designs', function (Blueprint $table) {
            $table->longText('gallery_images')->nullable()->change();
        });
        
        Schema::table('design_pages', function (Blueprint $table) {
            $table->longText('canvas_images')->nullable()->change();
            $table->longText('texts')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('designs', function (Blueprint $table) {
            $table->json('gallery_images')->nullable()->change();
        });
        
        Schema::table('design_pages', function (Blueprint $table) {
            $table->json('canvas_images')->nullable()->change();
            $table->json('texts')->nullable()->change();
        });
    }
};
