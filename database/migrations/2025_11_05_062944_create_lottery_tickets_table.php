<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lottery_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_name')->default('THAI GOVERNMENT LOTTERY');
            $table->string('signature')->nullable();
            $table->date('withdraw_date'); // Date when ticket can be withdrawn/future
            $table->string('ticket_type')->default('normal'); // "normal", "special", or "lucky"
            $table->json('numbers'); // Stored as array (e.g. ["9","9","9","9","9","9"])
            $table->string('bar_code')->unique();
            $table->integer('period');
            $table->integer('big_num')->nullable();
            $table->integer('set_no')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('left_icon')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lottery_tickets');
    }
};
