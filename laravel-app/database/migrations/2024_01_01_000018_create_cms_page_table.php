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
        Schema::create('cms_page', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->default('')->comment('Page title');
            $table->string('keywords', 500)->default('')->comment('SEO keywords');
            $table->text('description')->nullable()->comment('SEO description');
            $table->longText('content')->nullable()->comment('Page content');
            $table->string('cover', 500)->default('')->comment('Cover image');
            $table->string('template', 100)->default('')->comment('Page template');
            $table->string('author', 50)->default('')->comment('Author');
            $table->unsignedInteger('views')->default(0)->comment('View count');
            $table->unsignedInteger('sort')->default(100)->comment('Sort order');
            $table->tinyInteger('status')->default(1)->comment('Status: 1=enabled, 0=disabled');
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
        Schema::dropIfExists('cms_page');
    }
};
