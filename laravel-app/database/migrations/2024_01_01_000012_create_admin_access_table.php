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
        Schema::create('admin_access', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uid')->comment('User ID');
            $table->string('module', 50)->default('')->comment('Module name');
            $table->string('group', 50)->default('')->comment('Permission group');
            $table->unsignedBigInteger('nid')->default(0)->comment('Node ID');
            
            $table->index('uid');
            $table->index(['module', 'group']);
            $table->index('nid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_access');
    }
};
