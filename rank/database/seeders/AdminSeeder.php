<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@careereducate.com')],
            [
                'name' => env('ADMIN_NAME', 'Career Educate Admin'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'Admin@12345')),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
