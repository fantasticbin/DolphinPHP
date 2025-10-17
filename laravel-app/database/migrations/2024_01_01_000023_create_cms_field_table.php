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
        Schema::create('cms_field', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('model')->comment('Model ID');
            $table->string('level', 50)->nullable()->comment('Field level');
            $table->string('name', 100)->comment('Field name');
            $table->string('title')->comment('Field title');
            $table->string('define')->comment('Field definition (SQL type)');
            $table->string('type', 50)->comment('Field type');
            $table->text('options')->nullable()->comment('Field options');
            $table->string('value')->nullable()->comment('Default value');
            $table->tinyInteger('show')->default(1)->comment('Show in form');
            $table->tinyInteger('status')->default(1)->comment('Status');
            $table->integer('sort')->default(100)->comment('Sort order');
            $table->timestamps();

            $table->index('model');
            $table->index('status');
            $table->index(['model', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_field');
    }
};
