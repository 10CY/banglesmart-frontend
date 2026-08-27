<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'admin@banglesmart.com',
            ],
            [
                'name' => 'BanglesMart Admin',
                'password' => Hash::make('Admin@123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
    }
}