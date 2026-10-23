<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tarifario') && ! Schema::hasIndex('tarifario', 'tarifario_category_weight_idx')) {
            Schema::table('tarifario', function (Blueprint $table) {
                $table->index(['categoria', 'peso_min_kg', 'peso_max_kg'], 'tarifario_category_weight_idx');
            });
        }
        if (Schema::hasTable('calculator_queries') && ! Schema::hasIndex('calculator_queries', 'calculator_queries_duration_idx')) {
            Schema::table('calculator_queries', function (Blueprint $table) {
                $table->index('duration_ms', 'calculator_queries_duration_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tarifario') && Schema::hasIndex('tarifario', 'tarifario_category_weight_idx')) {
            Schema::table('tarifario', function (Blueprint $table) {
                $table->dropIndex('tarifario_category_weight_idx');
            });
        }
        if (Schema::hasTable('calculator_queries') && Schema::hasIndex('calculator_queries', 'calculator_queries_duration_idx')) {
            Schema::table('calculator_queries', function (Blueprint $table) {
                $table->dropIndex('calculator_queries_duration_idx');
            });
        }
    }
};
