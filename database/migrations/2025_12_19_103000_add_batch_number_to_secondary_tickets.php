<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add batch_number to secondary_lottery_tickets
        Schema::table('secondary_lottery_tickets', function (Blueprint $table) {
            $table->string('batch_number', 50)->nullable()->after('id')->index();
        });

        // Add batch_token to secondary_sales_transactions for batch-based public links
        Schema::table('secondary_sales_transactions', function (Blueprint $table) {
            $table->string('batch_token', 32)->nullable()->after('public_token')->index();
        });
    }

    public function down(): void
    {
        Schema::table('secondary_lottery_tickets', function (Blueprint $table) {
            $table->dropColumn('batch_number');
        });

        Schema::table('secondary_sales_transactions', function (Blueprint $table) {
            $table->dropColumn('batch_token');
        });
    }
};
