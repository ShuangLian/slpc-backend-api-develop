<?php

namespace Database\Seeders;

use App\Models\ChurchRole;
use Illuminate\Database\Seeder;

class ChurchRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            1 => ['name' => '牧師', 'text_color' => '#FAAD14', 'background_color' => '#FFFBE6', 'border_color' => '#FFE58F'],
            2 => ['name' => '長老', 'text_color' => '#FAAD14', 'background_color' => '#FFFBE6', 'border_color' => '#FFE58F'],
            3 => ['name' => '幹事', 'text_color' => '#FAAD14', 'background_color' => '#FFFBE6', 'border_color' => '#FFE58F'],
            4 => ['name' => '執事', 'text_color' => '#FAAD14', 'background_color' => '#FFFBE6', 'border_color' => '#FFE58F'],
            5 => ['name' => '團契會長', 'text_color' => '#2F54EB', 'background_color' => '#F0F5FF', 'border_color' => '#ADC6FF'],
            6 => ['name' => '詩班團長', 'text_color' => '#2F54EB', 'background_color' => '#F0F5FF', 'border_color' => '#ADC6FF'],
            7 => ['name' => '樂團團長', 'text_color' => '#2F54EB', 'background_color' => '#F0F5FF', 'border_color' => '#ADC6FF'],
            8 => ['name' => '敬拜團團長', 'text_color' => '#2F54EB', 'background_color' => '#F0F5FF', 'border_color' => '#ADC6FF'],
            9 => ['name' => '小組長', 'text_color' => '#2F54EB', 'background_color' => '#F0F5FF', 'border_color' => '#ADC6FF'],
            10 => ['name' => '兒童主日學校長', 'text_color' => '#FA541C', 'background_color' => '#FFF2E8', 'border_color' => '#FFBB96'],
            11 => ['name' => '兒童主日學主任', 'text_color' => '#FA541C', 'background_color' => '#FFF2E8', 'border_color' => '#FFBB96'],
            12 => ['name' => '國中組輔導', 'text_color' => '#FA541C', 'background_color' => '#FFF2E8', 'border_color' => '#FFBB96'],
            13 => ['name' => '高中組輔導', 'text_color' => '#FA541C', 'background_color' => '#FFF2E8', 'border_color' => '#FFBB96'],
            14 => ['name' => '大專組輔導', 'text_color' => '#FA541C', 'background_color' => '#FFF2E8', 'border_color' => '#FFBB96'],
            15 => ['name' => '教會職員', 'text_color' => '#722ED1', 'background_color' => '#F9F0FF', 'border_color' => '#D3ADF7'],
            16 => ['name' => '成人會友', 'text_color' => '#13C2C2', 'background_color' => '#E6FFFB', 'border_color' => '#87E8DE'],
            17 => ['name' => '小兒會友', 'text_color' => '#13C2C2', 'background_color' => '#E6FFFB', 'border_color' => '#87E8DE'],
            18 => ['name' => '雙連之友', 'text_color' => '#EB2F96', 'background_color' => '#FFF0F6', 'border_color' => '#FFADD2'],
            19 => ['name' => '安養中心員工', 'text_color' => '#52C41A', 'background_color' => '#F6FFED', 'border_color' => '#B7EB8F'],
            20 => ['name' => '安養中心長者', 'text_color' => '#52C41A', 'background_color' => '#F6FFED', 'border_color' => '#B7EB8F'],
            21 => ['name' => '安養中心家屬', 'text_color' => '#52C41A', 'background_color' => '#F6FFED', 'border_color' => '#B7EB8F'],
        ];

        foreach ($roles as $priority => $role) {
            $churchRole = new ChurchRole();
            $churchRole['name'] = $role['name'];
            $churchRole['priority'] = $priority;
            $churchRole['is_default_role'] = $role['name'] == '成人會友';
            $churchRole['text_color'] = $role['text_color'];
            $churchRole['background_color'] = $role['background_color'];
            $churchRole['border_color'] = $role['border_color'];
            $churchRole->save();
        }
    }
}
