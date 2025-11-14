<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_purchases', function (Blueprint $table) {
            $table->foreignId('draw_result_id')->nullable()->constrained()->onDelete('set null');
            $table->string('prize_won')->nullable(); // e.g., "First Prize", "Third Prize"
            $table->timestamp('checked_at')->nullable();
            
            // Update status column if needed
            // $table->string('status')->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_purchases', function (Blueprint $table) {
            $table->dropForeign(['draw_result_id']);
            $table->dropColumn(['draw_result_id', 'prize_won', 'checked_at']);
        });
    }
};