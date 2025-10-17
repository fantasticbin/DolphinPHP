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
        Schema::create('cms_support', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Support title');
            $table->string('type', 50)->comment('Support type (QQ, WeChat, Phone, etc.)');
            $table->string('account')->comment('Account/contact info');
            $table->string('avatar')->nullable()->comment('Avatar/icon');
            $table->integer('sort')->default(100)->comment('Sort order');
            $table->tinyInteger('status')->default(1)->comment('Status');
            $table->timestamps();

            $table->index('status');
            $table->index('type');
            $table->index('sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_support');
    }
};
