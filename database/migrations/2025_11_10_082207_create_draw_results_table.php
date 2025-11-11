<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('draw_results', function ($table) {
            $table->id();
            $table->string('draw_date')->unique();
            $table->string('date_th');           // raw Thai date text
            $table->string('date_en')->nullable(); // english date text (optional, store as translated)
            $table->json('prizes');
            $table->json('running_numbers');
            $table->string('endpoint')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draw_results');
    }
};
