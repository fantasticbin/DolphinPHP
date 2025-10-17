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
        Schema::create('cms_advert', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type')->default(0)->comment('Advert type ID');
            $table->string('title', 100)->default('')->comment('Advert title');
            $table->text('content')->nullable()->comment('Advert content/code');
            $table->string('image', 500)->default('')->comment('Advert image');
            $table->string('url', 500)->default('')->comment('Click URL');
            $table->string('target', 20)->default('_blank')->comment('Link target');
            $table->timestamp('start_time')->nullable()->comment('Start time');
            $table->timestamp('end_time')->nullable()->comment('End time');
            $table->unsignedInteger('clicks')->default(0)->comment('Click count');
            $table->unsignedInteger('sort')->default(100)->comment('Sort order');
            $table->tinyInteger('status')->default(1)->comment('Status: 1=enabled, 0=disabled');
            $table->timestamps();
            
            $table->index('type');
            $table->index('status');
            $table->index('sort');
            $table->index('start_time');
            $table->index('end_time');
            
            $table->foreign('type')->references('id')->on('cms_advert_type')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_advert');
    }
};
