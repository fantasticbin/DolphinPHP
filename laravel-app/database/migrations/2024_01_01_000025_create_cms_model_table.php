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
        Schema::create('cms_model', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Model title');
            $table->string('name', 100)->comment('Model name');
            $table->string('table')->nullable()->comment('Database table');
            $table->tinyInteger('type')->default(1)->comment('Model type: 1=attached, 2=independent');
            $table->string('description')->nullable()->comment('Description');
            $table->integer('sort')->default(100)->comment('Sort order');
            $table->tinyInteger('status')->default(1)->comment('Status');
            $table->timestamps();

            $table->index('status');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_model');
    }
};
