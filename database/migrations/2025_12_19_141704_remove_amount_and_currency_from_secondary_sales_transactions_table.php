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
        Schema::table('secondary_sales_transactions', function (Blueprint $table) {
            $table->dropColumn(['amount', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('secondary_sales_transactions', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->after('purchased_at');
            $table->string('currency', 10)->default('THB')->after('amount');
        });
    }
};
