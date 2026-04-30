<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@warungmamah.com',
            'password' => Hash::make('password'),
            'role_id' => 1,
            'phone' => '081234567890',
            'address' => 'Warung Mamah',
        ]);
    }
}