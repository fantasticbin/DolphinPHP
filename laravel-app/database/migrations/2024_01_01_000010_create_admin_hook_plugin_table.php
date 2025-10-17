<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create admin hook plugin table
 * 
 * This migration creates the admin_hook_plugin table for linking hooks to plugins
 * Migrated from ThinkPHP database structure
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_hook_plugin', function (Blueprint $table) {
            $table->id();
            $table->string('hook', 64)->comment('Hook name');
            $table->string('plugin', 64)->comment('Plugin name');
            $table->integer('sort')->default(100)->comment('Sort order');
            $table->tinyInteger('status')->default(1)->comment('Status: 0=disabled, 1=enabled');
            $table->timestamps();

            $table->index('hook');
            $table->index('plugin');
            $table->index('status');
            $table->index('sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_hook_plugin');
    }
};
