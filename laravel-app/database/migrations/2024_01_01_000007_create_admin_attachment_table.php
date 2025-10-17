<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create admin attachment table
 * 
 * This migration creates the admin_attachment table for file uploads
 * Migrated from ThinkPHP database structure
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_attachment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->default(0)->comment('User ID');
            $table->string('name', 255)->comment('Original filename');
            $table->string('path', 255)->comment('File path');
            $table->string('url', 255)->nullable()->comment('Full URL');
            $table->string('mime', 100)->comment('MIME type');
            $table->string('ext', 10)->comment('File extension');
            $table->unsignedBigInteger('size')->default(0)->comment('File size in bytes');
            $table->string('md5', 32)->nullable()->comment('MD5 hash');
            $table->string('sha1', 40)->nullable()->comment('SHA1 hash');
            $table->string('driver', 16)->default('local')->comment('Storage driver');
            $table->unsignedInteger('width')->default(0)->comment('Image width');
            $table->unsignedInteger('height')->default(0)->comment('Image height');
            $table->tinyInteger('status')->default(1)->comment('Status: 0=disabled, 1=enabled');
            $table->timestamps();

            $table->index('user_id');
            $table->index('mime');
            $table->index('ext');
            $table->index('driver');
            $table->index('md5');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_attachment');
    }
};
