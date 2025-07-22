<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get(database_path('seeders/data/products.json'));
        $records = json_decode($json, true)['RECORDS'];

        foreach ($records as $record) {
            DB::table('products')->updateOrInsert(
                ['id' => $record['id']],[
                'token' => $record['token'],
                'type' => $record['type'] ?: null,
                'name' => $record['name'],
                'datetime_created' => $this->parseDate($record['datetime_created']),
                'created_by' => $record['created_by'],
                'status' => (int) $record['status'],
                'datetime_disabled' => $record['datetime_disabled'] ? $this->parseDate($record['datetime_disabled']) : null,
                'disabled_by' => $record['disabled_by'] ?: null,
            ]);
        }
    }

    private function parseDate(string $date): ?string
    {
        try {
            return Carbon::createFromFormat('j/n/Y H:i:s', $date)->toDateTimeString();
        } catch (\Exception $e) {
            return null;
        }
    }
}
