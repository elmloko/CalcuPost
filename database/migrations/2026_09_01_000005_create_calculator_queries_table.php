<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculator_queries', function (Blueprint $table) {
            $table->id();
            // Historical snapshots must survive deletion or renaming of users/services.
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('actor_type', 10);
            $table->string('actor_key', 64);
            $table->string('actor_name');
            $table->string('service_code', 50);
            $table->string('service_name', 40);
            $table->string('category', 50);
            $table->string('modality', 10);
            $table->string('destination_code', 100);
            $table->string('destination_name', 150);
            $table->decimal('weight_kg', 12, 3);
            $table->json('dimensions')->nullable();
            $table->json('extras');
            $table->decimal('base_amount', 14, 2)->nullable();
            $table->decimal('total_amount', 14, 2)->nullable();
            $table->string('status', 16);
            $table->unsignedInteger('duration_ms');
            $table->timestamp('created_at')->index();
            $table->index(['service_code', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['actor_type', 'actor_key']);
        });
    }

    public function down(): void
    {
        throw new \RuntimeException('Rollback would delete calculator history. Restore from a verified backup instead.');
    }
};
