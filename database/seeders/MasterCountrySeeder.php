<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterCountrySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['country_name' => 'Indonesia'],
            ['country_name' => 'Malaysia'],
            ['country_name' => 'Thailand'],
            ['country_name' => 'Singapura'],
            ['country_name' => 'Jepang'],
        ];
        foreach ($data as $row) {
            DB::table('master_country')->insert($row);
        }
    }
}