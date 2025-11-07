<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_quotes', function (Blueprint $table) {
            $table->id();
            $table->text('quote');
            $table->string('author')->nullable();
            $table->string('category')->default('motivation'); // motivation, inspiration, success, luck
            $table->boolean('is_active')->default(true);
            $table->date('scheduled_for')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->integer('recipients_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['is_active', 'is_sent']);
            $table->index('scheduled_for');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_quotes');
    }
};
