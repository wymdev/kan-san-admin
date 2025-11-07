<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('notification_type'); // daily_quote, purchase_status, announcement
            $table->string('title');
            $table->text('body');
            $table->json('payload')->nullable();
            $table->string('expo_ticket_id')->nullable();
            $table->string('status')->default('pending'); // pending, sent, failed, delivered
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->index(['customer_id', 'status']);
            $table->index(['notification_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_notification_logs');
    }
};
