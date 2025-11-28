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
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'customer_registered', 'new_order'
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data (customer_id, order_id, etc.)
            $table->string('icon')->nullable(); // Icon class (lucide icon name)
            $table->string('color')->default('primary'); // Badge color
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['is_read', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
