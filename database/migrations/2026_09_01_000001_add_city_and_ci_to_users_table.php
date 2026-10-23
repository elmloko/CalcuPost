<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('city', 100)->nullable()->after('password');
            $table->string('ci', 50)->nullable()->after('city');
        });
    }

    public function down(): void
    {
        throw new \RuntimeException('Rollback would delete user location data. Restore from a verified backup instead.');
    }
};
