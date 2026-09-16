<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefStatusKetersediaanSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('ref_status_ketersediaan')->insert([
            ['id' => 1, 'nama_status' => 'Ada', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'nama_status' => 'Tidak Ada', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
