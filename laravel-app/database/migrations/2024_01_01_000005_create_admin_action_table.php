<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create admin action table
 * 
 * This migration creates the admin_action table for action definitions
 * Migrated from ThinkPHP database structure
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_action', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32)->unique()->comment('Action name');
            $table->string('title', 32)->comment('Action title');
            $table->string('remark', 255)->nullable()->comment('Action remark');
            $table->string('rule', 255)->nullable()->comment('Action rule');
            $table->tinyInteger('log')->default(1)->comment('Log action: 0=no, 1=yes');
            $table->tinyInteger('status')->default(1)->comment('Status: 0=disabled, 1=enabled');
            $table->timestamps();

            $table->index('name');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_action');
    }
};
