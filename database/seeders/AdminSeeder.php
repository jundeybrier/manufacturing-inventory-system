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
            'encode',
            'manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2️⃣ Create or update admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // 4️⃣ Create dummy office if not exists
        $officeId = DB::table('offices')->insertGetId([
            'uuid'       => (string) Str::uuid(),
            'name'       => 'Main Office',
            'location'   => 'Butuan City, Agusan del Norte',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5️⃣ Create Admin Users
        $superAdmin = User::updateOrCreate(
            ['email' => 'jundeybrier@gmail.com'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('qweasdzxc'), // Update manually later
                'office_id' => $officeId,
            ]
        );

        $superAdmin = User::updateOrCreate(
            ['email' => 'parrillaanalie08@gmail.com'],
            [
                'name'      => 'Administrator',
                'password'  => Hash::make('12345678'), // Update manually later
                'office_id' => $officeId,
            ]
        );
        $superAdmin->assignRole('admin');

    }
}
