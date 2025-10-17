<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create admin roles table
 * 
 * This migration creates the admin_role table for the DolphinPHP system
 * Migrated from ThinkPHP database structure
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_role', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32)->comment('Role name');
            $table->string('description', 255)->nullable()->comment('Role description');
            $table->text('access')->nullable()->comment('Access permissions (JSON)');
            $table->tinyInteger('status')->default(1)->comment('Status: 0=disabled, 1=enabled');
            $table->integer('sort')->default(0)->comment('Sort order');
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
        Schema::dropIfExists('admin_role');
    }
};
