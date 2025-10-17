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
        Schema::create('cms_link', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->default('')->comment('Link title');
            $table->string('url', 500)->default('')->comment('Link URL');
            $table->string('logo', 500)->default('')->comment('Link logo');
            $table->string('description', 500)->default('')->comment('Link description');
            $table->unsignedTinyInteger('rating')->default(0)->comment('Link rating');
            $table->unsignedInteger('sort')->default(100)->comment('Sort order');
            $table->tinyInteger('status')->default(1)->comment('Status: 1=enabled, 0=disabled');
            $table->timestamps();
            
            $table->index('status');
            $table->index('rating');
            $table->index('sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_link');
    }
};
