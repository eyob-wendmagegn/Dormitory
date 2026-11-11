<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Eyob Nigatu',
            'user_id_number' => 'ADM-001',
            'email' => 'eyobnigatu18@gmail.com',
            'password' => Hash::make('Eyob1234'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}
