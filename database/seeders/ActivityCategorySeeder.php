<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityCategory;
use Illuminate\Database\Seeder;

class ActivityCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->eventTypeSeed();
        $this->equipmentTypeSeed();
    }

    /**
     * @return void
     */
    private function eventTypeSeed(): void
    {
        $tier1Names = [
            '小組聚會',
            '團契聚會',
            '服事團體',
            '主日禮拜',
            '節期禮拜',
            '活動',
            '內部事務會議',
            '其他',
        ];
        $tier2Names = [
            '成人' => '小組聚會',
            '社青' => '小組聚會',
            '青少年' => '小組聚會',
            '兒童主日學' => '小組聚會',
            '英語' => '小組聚會',
            '迦南團契' => '團契聚會',
            '椰子團契' => '團契聚會',
            '雞啼團契' => '團契聚會',
            '盲朋友查經班' => '團契聚會',
            '迦拿團契' => '團契聚會',
            '松年團契' => '團契聚會',
            '媽媽讀書團契' => '團契聚會',
            '婦女團契' => '團契聚會',
            '活水溪雙語團契' => '團契聚會',
            '社區婦女成長學苑' => '團契聚會',
            '盲朋友聯誼會' => '團契聚會',
            '客語團契' => '團契聚會',
            '詩班/敬拜團/樂團' => '服事團體',
            '主日服事團體' => '服事團體',
            '第一場禮拜' => '主日禮拜',
            '第二場禮拜' => '主日禮拜',
            '第三場禮拜' => '主日禮拜',
            '第四場禮拜' => '主日禮拜',
            '分堂' => '主日禮拜',
            '聖誕節' => '節期禮拜',
            '受難周' => '節期禮拜',
            '復活節' => '節期禮拜',
            '新春禮拜' => '節期禮拜',
            '其他' => '節期禮拜',
            '音樂會' => '活動',
            '跨日聚會' => '活動',
            '營會' => '活動',
            '出遊活動' => '活動',
            '其他活動' => '活動',
            '第一主日' => '內部事務會議',
        ];
        $tier3Names = [
            '天母小組' => '成人',
            '活水A組' => '成人',
            '活水B組' => '成人',
            '漁海小組' => '成人',
            '恩典小組' => '成人',
            '呂底亞小組' => '成人',
            '但以理小組' => '成人',
            '提多小組' => '成人',
            '社區得勝小組' => '成人',
            '興盛小組' => '成人',
            '信實小組' => '成人',
            '以樂小組' => '成人',
            '喜樂小組' => '成人',
            '樂福小組' => '成人',
            '園中園小組' => '成人',
            '中山長青小組' => '成人',
            '葡萄樹A組' => '成人',
            '葡萄樹B組' => '成人',
            '葡萄樹C組' => '成人',
            '嗎哪小組' => '成人',
            '幸福得人小組' => '成人',
            '中山小組' => '成人',
            'I Believe 小組' => '社青',
            '心手相連小組' => '社青',
            '新婦小組' => '社青',
            '社青男女小組' => '社青',
            'Flash組' => '青少年',
            '高顏值組' => '青少年',
            'Emerge group' => '青少年',
            '雙連財產組' => '青少年',
            '大專組' => '青少年',
            '國中組' => '青少年',
            '教師小組A' => '兒童主日學',
            '教師小組B' => '兒童主日學',
            'Young Professionals' => '英語',
            'Kids Club' => '英語',
            '聖歌隊' => '詩班/敬拜團/樂團',
            '迦南詩班' => '詩班/敬拜團/樂團',
            '青少年詩班' => '詩班/敬拜團/樂團',
            '英語牧區詩班' => '詩班/敬拜團/樂團',
            '西羅亞詩班' => '詩班/敬拜團/樂團',
            '男聲合唱團' => '詩班/敬拜團/樂團',
            '婦女合唱團' => '詩班/敬拜團/樂團',
            '小西羅亞合唱團' => '詩班/敬拜團/樂團',
            '兒童合唱團' => '詩班/敬拜團/樂團',
            '米利安敬拜團' => '詩班/敬拜團/樂團',
            '777敬拜團' => '詩班/敬拜團/樂團',
            'Together敬拜團' => '詩班/敬拜團/樂團',
            '愛樂管弦樂團' => '詩班/敬拜團/樂團',
            '利未手鐘團A' => '詩班/敬拜團/樂團',
            '利未手鐘團B' => '詩班/敬拜團/樂團',
            '翻譯小組' => '主日服事團體',
            '台語' => [
                '第一場禮拜',
                '第三場禮拜',
            ],
            '華語/青少年' => '第二場禮拜',
            '英語' => '第四場禮拜',
            '三芝分堂禮拜' => '分堂',
            '新莊分堂禮拜' => '分堂',
            '小會' => '第一主日',
            '長執會' => '第一主日',
        ];

        // 新增 Tier1 Category
        foreach ($tier1Names as $tier1Name) {
            $tier1ActivityCategory = new ActivityCategory();
            $tier1ActivityCategory['activity_type'] = Activity::ACTIVITY_TYPE_EVENT;
            $tier1ActivityCategory['tier'] = 1;
            $tier1ActivityCategory['name'] = $tier1Name;
            $tier1ActivityCategory->save();
        }

        // 新增 Tier2 Category
        $tier1NameById = ActivityCategory::query()
            ->where('tier', 1)
            ->get()
            ->flatMap(function ($tier1ActivityCategory) {
                return [
                    $tier1ActivityCategory['name'] => $tier1ActivityCategory['id'],
                ];
            });

        foreach ($tier2Names as $tier2Name => $tier1Name) {
            $tier2ActivityCategory = new ActivityCategory();
            $tier2ActivityCategory['activity_type'] = Activity::ACTIVITY_TYPE_EVENT;
            $tier2ActivityCategory['parent_id'] = $tier1NameById[$tier1Name];
            $tier2ActivityCategory['tier'] = 2;
            $tier2ActivityCategory['name'] = $tier2Name;
            $tier2ActivityCategory->save();
        }

        // 新增 Tier3 Category
        $tier2NameById = ActivityCategory::query()
            ->where('tier', 2)
            ->get()
            ->flatMap(function ($tier2ActivityCategory) {
                return [
                    $tier2ActivityCategory['name'] => $tier2ActivityCategory['id'],
                ];
            });

        foreach ($tier3Names as $tier3Name => $tier2Names) {
            if (gettype($tier2Names) == 'array') {
                foreach ($tier2Names as $tier2Type) {
                    $tier3ActivityCategory = new ActivityCategory();
                    $tier3ActivityCategory['activity_type'] = Activity::ACTIVITY_TYPE_EVENT;
                    $tier3ActivityCategory['parent_id'] = $tier2NameById[$tier2Type];
                    $tier3ActivityCategory['tier'] = 3;
                    $tier3ActivityCategory['name'] = $tier3Name;
                    $tier3ActivityCategory->save();
                }
            } else {
                $tier3ActivityCategory = new ActivityCategory();
                $tier3ActivityCategory['activity_type'] = Activity::ACTIVITY_TYPE_EVENT;
                $tier3ActivityCategory['parent_id'] = $tier2NameById[$tier2Names];
                $tier3ActivityCategory['tier'] = 3;
                $tier3ActivityCategory['name'] = $tier3Name;
                $tier3ActivityCategory->save();
            }
        }
    }

    /**
     * @return void
     */
    private function equipmentTypeSeed(): void
    {
        $tier1Names = [
            '主日學',
            '牛埔庄講義所',
            '靈修培靈會',
            '其他',
        ];
        $tier2Names = [
            '兒童主日學' => '主日學',
            '成人主日學' => '主日學',
            '查經班' => '牛埔庄講義所',
            '專題講座' => '牛埔庄講義所',
            '臺語羅馬字班' => '牛埔庄講義所',
        ];
        $tier3Names = [
            '國小組' => '兒童主日學',
            '幼兒組' => '兒童主日學',
            '兒童台語文' => '兒童主日學',
            '聖經課程' => '成人主日學',
            '查經課程' => '成人主日學',
            '生活課程' => '成人主日學',
            '語言課程' => '成人主日學',
            '信徒課程' => '成人主日學',
            '關係佈道課程' => '成人主日學',
        ];

        // 新增 Tier1 Category
        foreach ($tier1Names as $tier1Name) {
            $tier1ActivityCategory = new ActivityCategory();
            $tier1ActivityCategory['activity_type'] = Activity::ACTIVITY_TYPE_EQUIPMENT;
            $tier1ActivityCategory['tier'] = 1;
            $tier1ActivityCategory['name'] = $tier1Name;
            $tier1ActivityCategory->save();
        }

        // 新增 Tier2 Category
        $tier1NameById = ActivityCategory::query()
            ->where('tier', 1)
            ->get()
            ->flatMap(function ($tier1ActivityCategory) {
                return [
                    $tier1ActivityCategory['name'] => $tier1ActivityCategory['id'],
                ];
            });

        foreach ($tier2Names as $tier2Name => $tier1Name) {
            $tier2ActivityCategory = new ActivityCategory();
            $tier2ActivityCategory['activity_type'] = Activity::ACTIVITY_TYPE_EQUIPMENT;
            $tier2ActivityCategory['parent_id'] = $tier1NameById[$tier1Name];
            $tier2ActivityCategory['tier'] = 2;
            $tier2ActivityCategory['name'] = $tier2Name;
            $tier2ActivityCategory->save();
        }

        // 新增 Tier3 Category
        $tier2NameById = ActivityCategory::query()
            ->where('tier', 2)
            ->get()
            ->flatMap(function ($tier2ActivityCategory) {
                return [
                    $tier2ActivityCategory['name'] => $tier2ActivityCategory['id'],
                ];
            });

        foreach ($tier3Names as $tier3Name => $tier2Names) {
            $tier3ActivityCategory = new ActivityCategory();
            $tier3ActivityCategory['activity_type'] = Activity::ACTIVITY_TYPE_EQUIPMENT;
            $tier3ActivityCategory['parent_id'] = $tier2NameById[$tier2Names];
            $tier3ActivityCategory['tier'] = 3;
            $tier3ActivityCategory['name'] = $tier3Name;
            $tier3ActivityCategory->save();
        }
    }
}
