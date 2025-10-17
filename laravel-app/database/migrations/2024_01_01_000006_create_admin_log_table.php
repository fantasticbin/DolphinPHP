<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create admin log table
 * 
 * This migration creates the admin_log table for action logging
 * Migrated from ThinkPHP database structure
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('action_id')->default(0)->comment('Action ID');
            $table->string('action_name', 32)->comment('Action name');
            $table->unsignedBigInteger('user_id')->default(0)->comment('User ID');
            $table->string('username', 32)->comment('Username');
            $table->unsignedBigInteger('record_id')->default(0)->comment('Record ID');
            $table->string('model', 32)->nullable()->comment('Model name');
            $table->text('remark')->nullable()->comment('Remark');
            $table->tinyInteger('status')->default(1)->comment('Status');
            $table->string('ip', 16)->nullable()->comment('IP address');
            $table->timestamps();

            $table->index('action_id');
            $table->index('action_name');
            $table->index('user_id');
            $table->index('model');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_log');
    }
};
