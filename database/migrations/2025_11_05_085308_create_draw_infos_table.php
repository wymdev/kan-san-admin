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
        Schema::create('draw_infos', function (Blueprint $table) {
            $table->id();
            $table->dateTime('draw_date');
            $table->dateTime('result_announce_date');
            $table->string('period')->nullable();
            $table->boolean('is_estimated')->default(false);
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draw_infos');
    }
};
