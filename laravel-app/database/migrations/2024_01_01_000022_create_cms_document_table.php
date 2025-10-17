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
        Schema::create('cms_document', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cid')->default(0)->comment('Column ID');
            $table->unsignedBigInteger('uid')->default(0)->comment('User ID');
            $table->unsignedBigInteger('model')->default(0)->comment('Model ID');
            $table->string('title')->comment('Title');
            $table->string('summary', 500)->nullable()->comment('Summary');
            $table->longText('content')->nullable()->comment('Content');
            $table->string('flag', 100)->nullable()->comment('Custom flags');
            $table->unsignedInteger('view')->default(0)->comment('View count');
            $table->integer('sort')->default(100)->comment('Sort order');
            $table->tinyInteger('status')->default(1)->comment('Status');
            $table->tinyInteger('trash')->default(0)->comment('Trash status');
            $table->timestamps();

            $table->index('cid');
            $table->index('uid');
            $table->index('model');
            $table->index('status');
            $table->index(['trash', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_document');
    }
};
