<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('offices')->insert([
            'uuid'       => (string) Str::uuid(),
            'name'       => 'Main Office (Development)',
            'location'   => 'Butuan City, Agusan del Norte',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
