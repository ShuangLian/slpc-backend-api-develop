<?php

namespace Database\Seeders;

use App\Models\VisitReason;
use Illuminate\Database\Seeder;

class VisitReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $reasons = [
            '家庭探訪（例行探訪）',
            '醫院探訪（緊急探訪）',
            '電話關懷',
            '婚喪關懷',
            '家庭禮拜',
            '其他',
        ];

        foreach ($reasons as $reason) {
            $visitReason = new VisitReason();
            $visitReason['reason'] = $reason;
            $visitReason->save();
        }
    }
}
