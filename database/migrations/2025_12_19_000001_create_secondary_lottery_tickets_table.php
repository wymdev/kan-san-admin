<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secondary_lottery_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ticket_name')->nullable()->default('Secondary Ticket');
            $table->string('signature')->nullable();
            $table->date('withdraw_date')->nullable(); // Draw date
            $table->string('ticket_type')->nullable()->default('normal'); // normal/special/lucky
            $table->json('numbers')->nullable(); // Extracted ticket numbers
            $table->string('bar_code')->nullable();
            $table->integer('period')->nullable();
            $table->integer('big_num')->nullable();
            $table->integer('set_no')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('source_image')->nullable(); // Path to uploaded OCR image
            $table->string('source_seller')->nullable(); // Main seller name/reference
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('withdraw_date');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secondary_lottery_tickets');
    }
};
