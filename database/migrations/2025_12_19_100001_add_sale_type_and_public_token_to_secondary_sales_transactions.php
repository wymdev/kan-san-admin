<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('secondary_sales_transactions', function (Blueprint $table) {
            // Sale type: 'own' = sell by my own (with customer), 'other' = sold by other (no customer)
            $table->enum('sale_type', ['own', 'other'])->default('own')->after('transaction_number');
            
            // Unique public token for result checking link
            $table->string('public_token', 32)->nullable()->unique()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('secondary_sales_transactions', function (Blueprint $table) {
            $table->dropColumn(['sale_type', 'public_token']);
        });
    }
};
