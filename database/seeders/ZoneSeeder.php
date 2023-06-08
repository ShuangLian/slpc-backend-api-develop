<?php

namespace Database\Seeders;

use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $level1Zones = [
            '東區',
            '西區',
            '南區',
            '北區',
            '社青牧區',
            '兒少牧區',
            '英語牧區',
        ];

        $level2Zones = [
            '中山區' => '東區',
            '大同' => '西區',
            '中正' => '西區',
            '萬華' => '西區',
            '板橋' => '西區',
            '三重' => '西區',
            '新莊' => '西區',
            '五股' => '西區',
            '蘆洲' => '西區',
            '新店' => '西區',
            '土城' => '西區',
            '泰山' => '西區',
            '林口' => '西區',
            '三峽' => '西區',
            '鶯歌' => '西區',
            '樹林' => '西區',
            '松山' => '南區',
            '信義' => '南區',
            '大安' => '南區',
            '文山' => '南區',
            '南港' => '南區',
            '汐止' => '南區',
            '永和' => '南區',
            '中和' => '南區',
            '深坑' => '南區',
            '桃園以南' => '南區',
            '士林' => '北區',
            '北投' => '北區',
            '八里' => '北區',
            '淡水' => '北區',
            '萬里' => '北區',
            '金山' => '北區',
            '三芝' => '北區',
            '石門' => '北區',
            '內湖' => '北區',
            '瑞芳' => '北區',
            '基隆' => '北區',
        ];

        foreach ($level1Zones as $level1Zone) {
            $zone = new Zone();
            $zone['church_type'] = Zone::CHURCH_TYPE_SHUANG_LIEN;
            $zone['name'] = $level1Zone;
            $zone['level'] = 1;
            $zone->save();
        }

        $level1ZoneNameById = Zone::query()
            ->where('church_type', Zone::CHURCH_TYPE_SHUANG_LIEN)
            ->where('level', 1)
            ->get()
            ->flatMap(function ($zone) {
                return [
                    $zone['name'] => $zone['id'],
                ];
            });

        foreach ($level2Zones as $level2Zone => $level1ZoneName) {
            $zone = new Zone();
            $zone['church_type'] = Zone::CHURCH_TYPE_SHUANG_LIEN;
            $zone['name'] = $level2Zone;
            $zone['level'] = 2;
            $zone['parent_id'] = $level1ZoneNameById[$level1ZoneName];
            $zone->save();
        }
    }
}
