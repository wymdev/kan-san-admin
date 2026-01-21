<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Performance indexes for lottery checking queries
     */
    public function up(): void
    {
        // Index for draw_results table - faster date lookups
        Schema::table('draw_results', function (Blueprint $table) {
            $table->index('draw_date', 'idx_draw_date');
        });

        // Index for ticket_purchases table - faster status/checked filtering
        Schema::table('ticket_purchases', function (Blueprint $table) {
            $table->index(['status', 'checked_at'], 'idx_status_checked');
        });

        // Index for secondary_sales_transactions table - faster status/checked filtering
        Schema::table('secondary_sales_transactions', function (Blueprint $table) {
            $table->index(['status', 'checked_at'], 'idx_secondary_status_checked');
        });

        // Index for secondary_lottery_tickets table - faster draw date lookups
        Schema::table('secondary_lottery_tickets', function (Blueprint $table) {
            $table->index('withdraw_date', 'idx_withdraw_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('draw_results', function (Blueprint $table) {
            $table->dropIndex('idx_draw_date');
        });

        Schema::table('ticket_purchases', function (Blueprint $table) {
            $table->dropIndex('idx_status_checked');
        });

        Schema::table('secondary_sales_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_secondary_status_checked');
        });

        Schema::table('secondary_lottery_tickets', function (Blueprint $table) {
            $table->dropIndex('idx_withdraw_date');
        });
    }
};
