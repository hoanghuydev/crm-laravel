<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users if they don't exist
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('Password123!'),
                'role' => 'admin',
                'is_active' => true,
            ]);
        }

        if (!User::where('email', 'staff@example.com')->exists()) {
            User::create([
                'name' => 'Staff User',
                'email' => 'staff@example.com',
                'password' => Hash::make('Password123!'),
                'role' => 'staff',
                'is_active' => true,
            ]);
        }

        if (!User::where('email', 'customer@example.com')->exists()) {
            User::create([
                'name' => 'Customer User',
                'email' => 'customer@example.com',
                'password' => Hash::make('Password123!'),
                'role' => 'customer',
                'is_active' => true,
            ]);
        }

        $this->command->info('Test users created successfully!');
        $this->command->info('Admin: admin@example.com / Password123!');
        $this->command->info('Staff: staff@example.com / Password123!');
        $this->command->info('Customer: customer@example.com / Password123!');
    }
}