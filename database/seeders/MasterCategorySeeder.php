<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterCategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['category_name' => 'Pantai'],
            ['category_name' => 'Pegunungan'],
            ['category_name' => 'Kota'],
            ['category_name' => 'Budaya'],
            ['category_name' => 'Kuliner'],
        ];

        foreach ($data as $row) {
            DB::table('master_category')->insert($row);
        }
    }
}