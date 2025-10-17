<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create admin plugin table
 * 
 * This migration creates the admin_plugin table for plugin management
 * Migrated from ThinkPHP database structure
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_plugin', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64)->unique()->comment('Plugin name');
            $table->string('title', 64)->comment('Plugin title');
            $table->string('icon', 64)->default('')->comment('Plugin icon');
            $table->string('description', 255)->default('')->comment('Plugin description');
            $table->string('author', 64)->default('')->comment('Plugin author');
            $table->string('author_url', 255)->default('')->comment('Author URL');
            $table->string('version', 16)->default('')->comment('Plugin version');
            $table->text('config')->nullable()->comment('Plugin configuration (JSON)');
            $table->tinyInteger('admin')->default(0)->comment('Admin only: 0=no, 1=yes');
            $table->tinyInteger('bootstrap')->default(0)->comment('Bootstrap: 0=no, 1=yes');
            $table->integer('sort')->default(100)->comment('Sort order');
            $table->tinyInteger('status')->default(1)->comment('Status: 0=disabled, 1=enabled, 2=damaged');
            $table->timestamps();

            $table->index('status');
            $table->index('admin');
            $table->index('sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_plugin');
    }
};
