<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('ci');
        });

        if (Schema::hasTable('roles') && Schema::hasTable('model_has_roles')) {
            $adminIds = DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->whereIn('roles.name', ['admin', 'Admin', 'administrador', 'Administrador'])
                ->pluck('model_has_roles.model_id');
            DB::table('users')->whereIn('id', $adminIds)->update(['is_admin' => true]);
        }

        // Keep legacy role tables intact for rollback and auditability.
    }

    public function down(): void
    {
        throw new \RuntimeException('Rollback would discard administrator assignments. Restore from a verified backup instead.');
    }
};
