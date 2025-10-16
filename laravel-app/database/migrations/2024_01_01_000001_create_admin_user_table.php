<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create admin users table
 * 
 * This migration creates the admin_user table for the DolphinPHP system
 * Migrated from ThinkPHP database structure
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_user', function (Blueprint $table) {
            $table->id();
            $table->string('username', 32)->unique()->comment('Username');
            $table->string('nickname', 32)->nullable()->comment('Nickname');
            $table->string('password')->comment('Password hash');
            $table->string('email', 64)->nullable()->comment('Email');
            $table->string('mobile', 11)->nullable()->comment('Mobile phone');
            $table->string('avatar', 255)->nullable()->comment('Avatar path');
            $table->unsignedBigInteger('role')->default(0)->comment('Role ID');
            $table->tinyInteger('status')->default(1)->comment('Status: 0=disabled, 1=enabled');
            $table->string('signup_ip', 16)->nullable()->comment('Signup IP');
            $table->timestamps();
            $table->softDeletes();

            $table->index('username');
            $table->index('email');
            $table->index('mobile');
            $table->index('role');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_user');
    }
};
