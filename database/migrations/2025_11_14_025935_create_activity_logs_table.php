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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship for actor (User or Customer)
            $table->string('actor_type')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            
            // Polymorphic relationship for subject (what was changed)
            $table->string('loggable_type')->nullable();
            $table->unsignedBigInteger('loggable_id')->nullable();
            
            // Action details
            $table->string('action', 50);
            $table->text('description')->nullable();
            
            // Data tracking (JSON columns)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // Context tracking
            $table->string('context', 50); // 'admin_portal' or 'api'
            $table->string('route')->nullable();
            $table->string('guard', 50)->nullable();
            
            // Request tracking
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            
            // Timestamp
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes for performance
            $table->index(['actor_type', 'actor_id'], 'idx_actor');
            $table->index(['loggable_type', 'loggable_id'], 'idx_loggable');
            $table->index('action');
            $table->index('context');
            $table->index('guard');
            $table->index('created_at');
            
            // Composite indexes for common queries
            $table->index(['actor_type', 'actor_id', 'created_at'], 'idx_actor_date');
            $table->index(['context', 'action'], 'idx_context_action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
