<?php

namespace Database\Seeders;

use App\Models\CountryCode;
use Illuminate\Database\Seeder;

class CountryCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $array = [
            '中國' => '86',
            '香港' => '852',
            '台灣' => '886',
            '澳門' => '853',
            '新加坡' => '65',
            '日本' => '81',
            '泰國' => '66',
            '馬來西亞' => '60',
            '菲律賓' => '63',
            '澳洲' => '61',
            '加拿大' => '1',
            '英國' => '44',
            '美國' => '1',
        ];

        foreach ($array as $key => $value) {
            $countryCode = new CountryCode();
            $countryCode['name'] = $key;
            $countryCode['code'] = $value;
            $countryCode['sort'] = 0;
            $countryCode->save();
        }
    }
}
