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
        Schema::create('admin_icon_list', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('icon_id')->default(0)->comment('Icon library ID');
            $table->string('title', 100)->default('')->comment('Icon title');
            $table->string('class', 200)->default('')->comment('Icon CSS class');
            $table->string('code', 50)->default('')->comment('Icon code');
            
            $table->index('icon_id');
            $table->foreign('icon_id')->references('id')->on('admin_icon')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_icon_list');
    }
};
