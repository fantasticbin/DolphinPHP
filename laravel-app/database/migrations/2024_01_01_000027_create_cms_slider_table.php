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
        Schema::create('cms_slider', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Slider title');
            $table->string('image')->comment('Image path/URL');
            $table->string('url')->nullable()->comment('Link URL');
            $table->string('target', 20)->default('_self')->comment('Link target');
            $table->string('description')->nullable()->comment('Description');
            $table->integer('sort')->default(100)->comment('Sort order');
            $table->tinyInteger('status')->default(1)->comment('Status');
            $table->timestamps();

            $table->index('status');
            $table->index('sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_slider');
    }
};
