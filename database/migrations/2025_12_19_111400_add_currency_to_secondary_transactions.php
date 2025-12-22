<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('secondary_sales_transactions', function (Blueprint $table) {
            // Add currency and detailed payment fields
            $table->string('currency', 10)->default('THB')->after('amount');
            $table->decimal('amount_mmk', 15, 2)->nullable()->after('currency');
            $table->decimal('amount_thb', 15, 2)->nullable()->after('amount_mmk');
            $table->decimal('exchange_rate', 10, 4)->nullable()->after('amount_thb');
        });
    }

    public function down(): void
    {
        Schema::table('secondary_sales_transactions', function (Blueprint $table) {
            $table->dropColumn(['currency', 'amount_mmk', 'amount_thb', 'exchange_rate']);
        });
    }
};
