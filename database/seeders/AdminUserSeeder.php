<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@hrpp.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), // use a strong password in prod
            ]
        );
    }
}
