<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This column already exists in the live MySQL schema but its migration was missing.
        if (! Schema::hasColumn('ticket_purchases', 'currency')) {
            Schema::table('ticket_purchases', function (Blueprint $table) {
                $table->string('currency', 10)->default('THB');
            });
        }
    }

    public function down(): void
    {
        // Do not drop a pre-existing column or erase historical currency data on rollback.
    }
};
