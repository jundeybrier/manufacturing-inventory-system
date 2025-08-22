<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Permissions
        $permissions = [
            'manage offices',
            'manage users',
            'manage services',
            'manage accounts',
            'manage fee components',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Create Admin Role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Assign all permissions to admin
        $adminRole->syncPermissions(Permission::all());

        // 3. Create Super Admin User
        $superAdmin = User::updateOrCreate(
            ['email' => 'jundeybrier@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('qweasdzxc'),
            ]
        );

        $superAdmin->assignRole('admin');

        // 4. Create PITS Admin User
        $pitsAdmin = User::updateOrCreate(
            ['email' => 'oca.pits@dfa.gov.ph'],
            [
                'name' => 'Super Admin - PITS',
                'password' => Hash::make('P@ssw0rd123$'),
            ]
        );

        $pitsAdmin->assignRole('admin');
    }
}
