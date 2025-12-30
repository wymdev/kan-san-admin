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
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('push_token_updated_at');
            $table->timestamp('blocked_at')->nullable()->after('is_blocked');
            $table->unsignedBigInteger('blocked_by')->nullable()->after('blocked_at');
            $table->string('block_reason')->nullable()->after('blocked_by');
            
            $table->foreign('blocked_by')->references('id')->on('users')->onDelete('set null');
            $table->index('is_blocked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['blocked_by']);
            $table->dropIndex(['is_blocked']);
            $table->dropColumn(['is_blocked', 'blocked_at', 'blocked_by', 'block_reason']);
        });
    }
};
