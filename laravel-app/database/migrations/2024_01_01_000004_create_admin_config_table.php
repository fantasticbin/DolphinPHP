<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create admin config table
 * 
 * This migration creates the admin_config table for system configuration
 * Migrated from ThinkPHP database structure
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_config', function (Blueprint $table) {
            $table->id();
            $table->string('title', 32)->comment('Configuration title');
            $table->string('name', 32)->unique()->comment('Configuration name');
            $table->string('group', 16)->default('base')->comment('Configuration group');
            $table->string('type', 16)->default('text')->comment('Configuration type');
            $table->text('value')->nullable()->comment('Configuration value');
            $table->text('options')->nullable()->comment('Configuration options');
            $table->string('tip', 255)->nullable()->comment('Configuration tip');
            $table->tinyInteger('status')->default(1)->comment('Status: 0=disabled, 1=enabled');
            $table->integer('sort')->default(100)->comment('Sort order');
            $table->timestamps();

            $table->index('group');
            $table->index('status');
            $table->index('sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_config');
    }
};
