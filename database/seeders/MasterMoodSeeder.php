<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterMoodSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['mood_name' => 'Santai'],
            ['mood_name' => 'Petualangan'],
            ['mood_name' => 'Romantis'],
            ['mood_name' => 'Keluarga'],
            ['mood_name' => 'Solo'],
        ];
        foreach ($data as $row) {
            DB::table('master_mood')->insert($row);
        }
    }
}