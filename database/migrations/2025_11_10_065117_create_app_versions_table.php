<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version', 20); // e.g., "1.0.0"
            $table->integer('version_code'); // e.g., 1, 2, 3 (for comparison)
            $table->enum('platform', ['android', 'ios', 'both'])->default('both');
            $table->string('minimum_version', 20)->nullable(); // Minimum supported version
            $table->integer('minimum_version_code')->nullable(); // For comparison
            $table->boolean('force_update')->default(false); // Force update flag
            $table->text('release_notes')->nullable();
            $table->string('download_url')->nullable(); // App store/Play store URL
            $table->boolean('is_active')->default(true);
            $table->boolean('is_latest')->default(false); // Mark as latest version
            $table->timestamp('release_date')->nullable();
            $table->json('features')->nullable(); // JSON array of new features
            $table->json('bug_fixes')->nullable(); // JSON array of bug fixes
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index(['platform', 'is_active']);
            $table->index(['version_code', 'platform']);
            $table->unique(['version', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
