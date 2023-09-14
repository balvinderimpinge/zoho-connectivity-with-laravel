<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZohoTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = date("Y-m-d H:i:s");

        DB::table('zoho_accesses')->insert([
            'client_id' => '1000.3WTIUO10003KAPQ11WUKMEQ5HAFQ4J',
            'client_secret' => 'd894264e59af2529ed3adbb500174ea34ccd12ae62',
            'refresh_token' => '1000.a1b62bd3385a357ea6d80e18750660f5.1f32d3d0f263bb2fce6bfe716b0e2e8d',
            'access_token' => "1000.eef3c0d9c552d7e31c447913214be070.9a568b05150d1a94e0f674665a368e4e",
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
    }
}
