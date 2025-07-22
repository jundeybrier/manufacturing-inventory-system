<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrInsert(
            ['email' => 'jundey.brier@dfa.gov.ph'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('P@ssw0rd123$'), // 🔐 change after login
            ]
        );

        $admin = User::updateOrInsert(
            ['email' => 'oca.pits@dfa.gov.ph'],
            [
                'name' => 'Super Admin - PITS',
                'password' => Hash::make('P@ssw0rd123$'), // 🔐 change after login
            ]
        );


    }
}
