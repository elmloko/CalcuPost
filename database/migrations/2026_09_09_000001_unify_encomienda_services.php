<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('postal_services')->where('code', 'MI_ENCOMIENDA')->update([
            'code' => 'ENCOMIENDA',
            'name' => 'Encomienda',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('postal_services')
            ->where('code', 'ENCOMIENDA')
            ->where('modality', 'NAT')
            ->where('category', 'MI ENCOMIENDA')
            ->update(['code' => 'MI_ENCOMIENDA', 'name' => 'Mi Encomienda', 'updated_at' => now()]);
    }
};
