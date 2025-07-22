<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ProductServicesSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/data/products_services.json');
        $data = json_decode(File::get($jsonPath), true);

        foreach ($data['RECORDS'] as $record) {
            DB::table('products_services')->updateOrInsert(
                [
                    'product_id' => $record['product_id'],
                    'service_id' => $record['services_id'],
                ],
            );
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
