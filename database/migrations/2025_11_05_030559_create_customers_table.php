<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique();
            $table->string('password');
            $table->string('full_name')->nullable();
            $table->enum('gender', ['M', 'F', 'Other'])->nullable();
            $table->date('dob')->nullable();
            $table->string('thai_pin')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('address')->nullable();
            $table->string('expo_push_token')->nullable();
            $table->timestamp('push_token_updated_at')->nullable();
            $table->timestamps();
            $table->index('phone_number');
            $table->index('expo_push_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
