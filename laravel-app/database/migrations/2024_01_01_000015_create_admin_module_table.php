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
        Schema::create('admin_module', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('Module name (unique identifier)');
            $table->string('title', 100)->default('')->comment('Module title');
            $table->string('icon', 100)->default('')->comment('Module icon');
            $table->string('description', 500)->default('')->comment('Module description');
            $table->string('author', 100)->default('')->comment('Author name');
            $table->string('author_url', 200)->default('')->comment('Author URL');
            $table->text('config')->nullable()->comment('Module configuration (JSON)');
            $table->text('access')->nullable()->comment('Module access control (JSON)');
            $table->string('version', 20)->default('')->comment('Module version');
            $table->string('identifier', 100)->default('')->comment('Unique identifier');
            $table->tinyInteger('admin')->default(0)->comment('Admin only: 1=yes, 0=no');
            $table->tinyInteger('system_module')->default(0)->comment('System module: 1=yes, 0=no');
            $table->unsignedInteger('sort')->default(100)->comment('Sort order');
            $table->tinyInteger('status')->default(1)->comment('Status: -3=info incomplete, -2=info missing, -1=not installed, 0=disabled, 1=enabled');
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
        Schema::dropIfExists('admin_module');
    }
};
