<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FundSourceSeeder extends Seeder
{
    public function run(): void
    {
        $fundSources = [
            [
                'token' => '_4McsMOpf8',
                'code' => 'GF',
                'name' => 'General Fund',
                'datetime_created' => '2024-09-16 08:31:41',
                'created_by' => 'superadmin2',
                'status' => 1,
                'datetime_disabled' => null,
                'disabled_by' => null,
            ],
            [
                'token' => 'wAFdsDdzLB',
                'code' => 'PRF',
                'name' => 'Passport Revolving Fund',
                'datetime_created' => '2024-09-16 08:32:31',
                'created_by' => 'superadmin2',
                'status' => 1,
                'datetime_disabled' => null,
                'disabled_by' => null,
            ],
            [
                'token' => 'mm9ZV7-VSK',
                'code' => 'OP',
                'name' => 'Office of the President',
                'datetime_created' => '2024-09-16 08:33:34',
                'created_by' => 'superadmin2',
                'status' => 1,
                'datetime_disabled' => null,
                'disabled_by' => null,
            ],
            [
                'token' => 'q0EDNsh0KL',
                'code' => 'TF',
                'name' => 'Trust Fund',
                'datetime_created' => '2024-09-17 06:53:03',
                'created_by' => 'superadmin2',
                'status' => 1,
                'datetime_disabled' => null,
                'disabled_by' => null,
            ],
            [
                'token' => 'XZSwbtuVBK',
                'code' => 'BTrReg',
                'name' => 'BTr Regular Fund',
                'datetime_created' => '2024-09-19 02:19:16',
                'created_by' => 'superadmin2',
                'status' => 1,
                'datetime_disabled' => null,
                'disabled_by' => null,
            ],
            [
                'token' => 'ZzCV8Vi_hI',
                'code' => 'PF',
                'name' => 'Provident Fund',
                'datetime_created' => '2024-09-19 02:20:46',
                'created_by' => 'superadmin2',
                'status' => 1,
                'datetime_disabled' => null,
                'disabled_by' => null,
            ],
        ];

        DB::table('fund_source')->insert($fundSources);
    }
}
