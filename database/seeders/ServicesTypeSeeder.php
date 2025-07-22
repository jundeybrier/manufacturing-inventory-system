<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicesTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['id' => 1, 'name' => 'PASSPORT'],
            ['id' => 2, 'name' => 'AUTHENTICATION'],
            ['id' => 3, 'name' => 'OTHERS'],
            ['id' => 4, 'name' => 'NOTARIALS'],
        ];

        foreach ($types as $type) {
            DB::table('services_type')->updateOrInsert(
                ['id' => $type['id']], // match by ID
                ['name' => $type['name']] // set or update name
            );
        }
    }
}
