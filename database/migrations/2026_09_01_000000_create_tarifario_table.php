<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tarifario')) {
            return;
        }

        Schema::create('tarifario', function (Blueprint $table) {
            $table->id();
            $table->string('categoria', 50);
            $table->decimal('peso_min_kg', 12, 3);
            $table->decimal('peso_max_kg', 12, 3);

            foreach (['dest_a', 'dest_b', 'dest_c', 'dest_d', 'dest_e', 'dest_f', 'dest_g',
                'local_1', 'local_2', 'local_3', 'local_4', 'nacional', 'cui_int', 'cui_cap',
                'cui1', 'cui2', 'pro_dentro', 'pro_otro'] as $column) {
                $table->decimal($column, 14, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        // Preserve imported tariff data when rolling back an existing installation.
    }
};
