<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create admin message table
 * 
 * This migration creates the admin_message table for internal messaging
 * Migrated from ThinkPHP database structure
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_message', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uid_send')->default(0)->comment('Sender user ID');
            $table->unsignedBigInteger('uid_receive')->default(0)->comment('Receiver user ID');
            $table->string('type', 32)->default('system')->comment('Message type');
            $table->string('title', 255)->comment('Message title');
            $table->text('content')->nullable()->comment('Message content');
            $table->string('url', 255)->nullable()->comment('Related URL');
            $table->tinyInteger('status')->default(0)->comment('Status: 0=unread, 1=read');
            $table->timestamps();

            $table->index('uid_send');
            $table->index('uid_receive');
            $table->index('status');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_message');
    }
};
