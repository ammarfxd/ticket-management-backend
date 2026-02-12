<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        User::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin',
            'password' => $password,
            'role' => User::ROLE_ADMIN,
        ]);

        User::updateOrCreate(
            ['email' => 'agent@example.com'],
            ['name' => 'Agent Demo', 'role' => User::ROLE_AGENT, 'password' => $password]
        );

        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            ['name' => 'Customer Demo', 'role' => User::ROLE_CUSTOMER, 'password' => $password]
        );
    }
}
