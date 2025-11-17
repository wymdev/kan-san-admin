<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First check the current ENUM values
        $statusValues = DB::select("SHOW COLUMNS FROM ticket_purchases WHERE Field = 'status'")[0]->Type;
        
        // Update to include all status values
        DB::statement("ALTER TABLE ticket_purchases MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'won', 'not_won') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE ticket_purchases MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};