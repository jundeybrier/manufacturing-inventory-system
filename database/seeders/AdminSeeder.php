<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

        // 3. Create dummy office if not exists
        $officeId = DB::table('offices')->insertGetId([
            'uuid'       => (string) Str::uuid(),
            'name'       => 'Main Office (Development)',
            'location'   => 'Butuan City, Agusan del Norte',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Create Super Admin User
        $superAdmin = User::updateOrCreate(
            ['email' => 'jundeybrier@gmail.com'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('qweasdzxc'),
                'office_id' => $officeId,
            ]
        );

        $superAdmin->assignRole('admin');

        // 5. Create PITS Admin User
        $pitsAdmin = User::updateOrCreate(
            ['email' => 'oca.pits@dfa.gov.ph'],
            [
                'name'      => 'Super Admin - PITS',
                'password'  => Hash::make('P@ssw0rd123$'),
                'office_id' => $officeId,
            ]
        );

        $pitsAdmin->assignRole('admin');
    }
}
