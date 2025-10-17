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
        Schema::create('cms_advert_type', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->default('')->comment('Type title');
            $table->string('name', 50)->default('')->comment('Type name (identifier)');
            $table->string('description', 500)->default('')->comment('Type description');
            $table->unsignedInteger('width')->default(0)->comment('Advert width');
            $table->unsignedInteger('height')->default(0)->comment('Advert height');
            $table->tinyInteger('status')->default(1)->comment('Status: 1=enabled, 0=disabled');
            $table->timestamps();
            
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_advert_type');
    }
};
