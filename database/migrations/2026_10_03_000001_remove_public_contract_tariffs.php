<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('contract_tariffs');
    }

    public function down(): void
    {
        throw new RuntimeException('Restoring removed public contract tariffs requires a verified backup.');
    }
};
