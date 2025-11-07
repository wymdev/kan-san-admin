<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_key')->unique();
            $table->string('page_name');
            $table->text('content');
            $table->string('page_type'); // privacy, terms, about, etc
            $table->string('public_slug')->unique();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->index(['page_type', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_pages');
    }
};
