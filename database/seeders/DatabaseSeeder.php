<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Thêm trường 'address' và 'phone' để không bị lỗi NOT NULL
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'full_name' => 'System Administrator',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'status' => 1,
                'address' => 'Hà Nội, Việt Nam',
                'phone' => '0987654321',
            ]
        );
    }
}