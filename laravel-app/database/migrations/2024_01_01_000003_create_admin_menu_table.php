<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create admin menu table
 * 
 * This migration creates the admin_menu table for the DolphinPHP system
 * Migrated from ThinkPHP database structure
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_menu', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pid')->default(0)->comment('Parent menu ID');
            $table->string('module', 16)->nullable()->comment('Module name');
            $table->string('title', 32)->comment('Menu title');
            $table->string('icon', 64)->nullable()->comment('Icon class');
            $table->string('url_value', 255)->nullable()->comment('URL value');
            $table->string('url_type', 16)->nullable()->comment('URL type');
            $table->string('url_target', 16)->default('_self')->comment('URL target');
            $table->tinyInteger('online_hide')->default(0)->comment('Hide online: 0=no, 1=yes');
            $table->tinyInteger('status')->default(1)->comment('Status: 0=disabled, 1=enabled');
            $table->integer('sort')->default(0)->comment('Sort order');
            $table->text('params')->nullable()->comment('Parameters (JSON)');
            $table->timestamps();

            $table->index('pid');
            $table->index('module');
            $table->index('status');
            $table->index('sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_menu');
    }
};
