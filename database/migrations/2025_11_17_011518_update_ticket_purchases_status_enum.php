<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $this->statuses(['pending', 'approved', 'rejected', 'won', 'not_won']);
    }

    public function down()
    {
        if (DB::table('ticket_purchases')->whereIn('status', ['won', 'not_won'])->exists()) {
            throw new RuntimeException('Cannot remove result statuses while purchases use them.');
        }
        $this->statuses(['pending', 'approved', 'rejected']);
    }

    private function statuses(array $values): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE ticket_purchases DROP CONSTRAINT IF EXISTS ticket_purchases_status_check');
            $allowed = implode(', ', array_map(fn ($value) => "'$value'", $values));
            DB::statement("ALTER TABLE ticket_purchases ADD CONSTRAINT ticket_purchases_status_check CHECK (status IN ($allowed))");
            return;
        }
        Schema::table('ticket_purchases', function (Blueprint $table) use ($values) {
            $table->enum('status', $values)->default('pending')->change();
        });
    }
};
