<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postal_services', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->string('name', 40);
            $table->string('modality', 3);
            $table->string('category', 50)->unique();
            $table->timestamps();
            $table->unique(['code', 'modality']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postal_services');
    }
};
