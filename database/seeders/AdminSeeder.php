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
        // 1️⃣ Permissions list
        $permissions = [
            'manage offices',
            'manage users',
            'manage services',
            'manage accounts',
            'manage fee components',
            'view office reports', // 👈 NEW permission
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2️⃣ Create or update admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // 3️⃣ Create Supervisor Role + only report permissions
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
        $supervisorRole->syncPermissions(['view office reports']); // 👈 only this permission for now

        // 4️⃣ Create dummy office if not exists
        $officeId = DB::table('offices')->insertGetId([
            'uuid'       => (string) Str::uuid(),
            'name'       => 'Main Office (Development)',
            'location'   => 'Butuan City, Agusan del Norte',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5️⃣ Create Admin Users
        $superAdmin = User::updateOrCreate(
            ['email' => 'jundeybrier@gmail.com'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make(''), // Update manually later
                'office_id' => $officeId,
            ]
        );
        $superAdmin->assignRole('admin');

        $pitsAdmin = User::updateOrCreate(
            ['email' => 'oca.pits@dfa.gov.ph'],
            [
                'name'      => 'Super Admin - PITS',
                'password'  => Hash::make(''),
                'office_id' => $officeId,
            ]
        );
        $pitsAdmin->assignRole('admin');
    }
}
