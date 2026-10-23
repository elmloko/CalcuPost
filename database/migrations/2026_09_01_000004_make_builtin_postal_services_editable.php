<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postal_services', function (Blueprint $table) {
            $table->string('modality', 10)->change();
        });

        if (! Schema::hasColumn('postal_services', 'profile')) {
            Schema::table('postal_services', function (Blueprint $table) {
                $table->string('profile', 50)->nullable()->after('category');
            });
        }

        $now = now();
        $services = [
            ['EMS', 'EMS', 'NAT', 'EMS NAT'], ['EMS', 'EMS', 'INT', 'EMS INT'],
            ['MI_ENCOMIENDA', 'Mi Encomienda', 'NAT', 'MI ENCOMIENDA'],
            ['ENCOMIENDA', 'Encomienda', 'INT', 'ENCOMIENDA'],
            ['LC_AO', 'LC/AO', 'NAT', 'LC/AO NAT'], ['LC_AO', 'LC/AO', 'INT', 'LC/AO INT'],
            ['ECA', 'ECA', 'NAT', 'ECA NAT'], ['ECA', 'ECA', 'INT', 'ECA INT'],
            ['PLIEGOS', 'Pliegos Oficiales', 'NAT', 'PLIEGOS NAT'], ['PLIEGOS', 'Pliegos Oficiales', 'INT', 'PLIEGOS INT'],
            ['SACAS_M', 'Sacas M', 'NAT', 'SACAS M NAT'], ['SACAS_M', 'Sacas M', 'INT', 'SACAS M INT'],
            ['SUPER', 'Super Express', 'NAT', 'SUPER NAT'], ['SUPER', 'Super Express', 'PAQUE', 'SUPER PAQUE NAT'], ['SUPER', 'Super Express', 'DOC', 'SUPER DOC NAT'],
        ];

        foreach ($services as [$code, $name, $modality, $category]) {
            DB::table('postal_services')->updateOrInsert(
                ['code' => $code, 'modality' => $modality],
                compact('name', 'category') + ['profile' => $category, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        DB::table('postal_services')->whereNull('profile')->update([
            'profile' => DB::raw("CONCAT('CUSTOM ', modality)"),
        ]);
    }

    public function down(): void
    {
        throw new \RuntimeException('Rollback would delete postal services and their profiles. Restore from a verified backup instead.');
    }
};
