<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lottery_tickets', function (Blueprint $table) {
            $table->enum('status', ['active', 'hidden'])->default('active')->after('description');
            $table->integer('stock')->default(1)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('lottery_tickets', function (Blueprint $table) {
            $table->dropColumn(['status', 'stock']);
        });
    }
};
