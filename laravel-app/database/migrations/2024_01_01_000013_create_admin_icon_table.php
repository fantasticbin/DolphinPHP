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
        Schema::create('admin_icon', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->default('')->comment('Icon library title');
            $table->string('name', 100)->default('')->comment('Icon library name');
            $table->string('url', 500)->default('')->comment('CSS URL');
            $table->unsignedInteger('sort')->default(100)->comment('Sort order');
            $table->tinyInteger('status')->default(1)->comment('Status: 1=enabled, 0=disabled');
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
        Schema::dropIfExists('admin_icon');
    }
};
