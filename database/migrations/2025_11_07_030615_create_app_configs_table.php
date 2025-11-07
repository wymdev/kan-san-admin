<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_configs', function (Blueprint $table) {
            $table->id();
            $table->string('config_key')->unique();
            $table->string('config_value');
            $table->string('value_type')->default('string'); 
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index('config_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_configs');
    }
};
