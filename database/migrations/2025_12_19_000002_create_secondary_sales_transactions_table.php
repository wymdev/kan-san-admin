<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secondary_sales_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->uuid('secondary_ticket_id');
            $table->foreign('secondary_ticket_id')
                  ->references('id')
                  ->on('secondary_lottery_tickets')
                  ->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_name')->nullable(); // For customers without account
            $table->string('customer_phone')->nullable(); // For customers without account
            $table->datetime('purchased_at');
            $table->decimal('amount', 10, 2);
            $table->boolean('is_paid')->default(false);
            $table->string('payment_method')->nullable(); // Cash/Transfer/etc
            $table->datetime('payment_date')->nullable();
            $table->enum('status', ['pending', 'won', 'not_won'])->default('pending');
            $table->foreignId('draw_result_id')->nullable()->constrained('draw_results')->nullOnDelete();
            $table->string('prize_won')->nullable();
            $table->datetime('checked_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('status');
            $table->index('is_paid');
            $table->index('purchased_at');
            $table->index('checked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secondary_sales_transactions');
    }
};
