<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->string('banner_type'); // news, promotion, announcement
            $table->string('action_url')->nullable();
            $table->string('action_type')->nullable(); // internal, external, deeplink
            $table->boolean('is_active')->default(true);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_active', 'banner_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_banners');
    }
};
