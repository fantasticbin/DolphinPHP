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
        Schema::create('cms_column', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pid')->default(0)->comment('Parent ID');
            $table->unsignedBigInteger('model')->default(0)->comment('Document model ID');
            $table->string('name', 50)->default('')->comment('Column name');
            $table->string('title', 100)->default('')->comment('Column title');
            $table->string('keywords', 500)->default('')->comment('SEO keywords');
            $table->text('description')->nullable()->comment('SEO description');
            $table->text('content')->nullable()->comment('Column content');
            $table->string('cover', 500)->default('')->comment('Cover image');
            $table->string('url', 200)->default('')->comment('External URL');
            $table->string('list_template', 100)->default('')->comment('List page template');
            $table->string('detail_template', 100)->default('')->comment('Detail page template');
            $table->string('page_template', 100)->default('')->comment('Single page template');
            $table->tinyInteger('type')->default(0)->comment('Type: 0=column, 1=single page');
            $table->unsignedInteger('list_row')->default(10)->comment('Items per page');
            $table->unsignedInteger('sort')->default(100)->comment('Sort order');
            $table->tinyInteger('status')->default(1)->comment('Status: 1=enabled, 0=disabled');
            $table->timestamps();
            
            $table->index('pid');
            $table->index('model');
            $table->index('status');
            $table->index('sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_column');
    }
};
