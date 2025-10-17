<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create admin hook table
 * 
 * This migration creates the admin_hook table for system hooks
 * Migrated from ThinkPHP database structure
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_hook', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64)->unique()->comment('Hook name');
            $table->string('plugin', 64)->default('')->comment('Plugin name');
            $table->string('description', 255)->default('')->comment('Hook description');
            $table->tinyInteger('system')->default(0)->comment('System hook: 0=no, 1=yes');
            $table->tinyInteger('status')->default(1)->comment('Status: 0=disabled, 1=enabled');
            $table->timestamps();

            $table->index('plugin');
            $table->index('system');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_hook');
    }
};
