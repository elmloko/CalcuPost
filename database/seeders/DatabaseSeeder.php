<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.test'],
            [
                'name' => 'admin',
                'password' => 'admin',
                'email_verified_at' => now(),
                'city' => 'LA PAZ',
                'ci' => 'ADMIN',
                'is_admin' => true,
            ]
        );
        $admin->forceFill(['is_admin' => true])->save();
    }
}
