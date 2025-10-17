<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_packet', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('Packet name (unique identifier)');
            $table->string('title', 100)->default('')->comment('Packet title');
            $table->string('description', 500)->default('')->comment('Packet description');
            $table->string('author', 100)->default('')->comment('Author name');
            $table->string('version', 20)->default('')->comment('Packet version');
            $table->json('tables')->nullable()->comment('Database tables (JSON array)');
            $table->tinyInteger('status')->default(0)->comment('Status: 0=not installed, 1=installed');
            $table->timestamps();
            
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_packet');
    }
};
