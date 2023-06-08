<?php

namespace App\Exports\Sheets;

use App\Models\LegacyUser;
use App\Models\LegacyUserProfile;
use App\Models\User;
use App\Models\UserProfile;
use App\Utils\PhoneFormatUtil;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class UserPerSheetExport implements FromArray, WithTitle, WithHeadings
{
    const NEW_USER = 'NEW-USER';
    const MATCHED_LEGACY_USER = 'MATCHED-LEGACY-USER';
    const LEGACY_USER = 'LEGACY-USER';

    private $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function array(): array
    {
        $userIds = match ($this->type) {
            UserPerSheetExport::NEW_USER => User::query()
                ->where('role', User::ROLE_USER)
                ->where('is_legacy', false)
                ->pluck('id'),
            UserPerSheetExport::MATCHED_LEGACY_USER => User::query()
                ->where('role', User::ROLE_USER)
                ->where('is_legacy', true)
                ->where('is_matched', true)
                ->pluck('matched_user_id'),
            UserPerSheetExport::LEGACY_USER => User::query()
                ->where('role', User::ROLE_USER)
                ->where(function ($query) {
                    $query->where(function ($subQuery) {
                        $subQuery->where('is_legacy', true)
                            ->where('is_matched', false);
                    })
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('is_legacy', false)
                                ->whereNull('line_uid');
                        });
                })
                ->pluck('id'),
        };


        $users = User::query()
            ->whereIn('id', array_unique($userIds->toArray()))
            ->with([
                'profile:id,user_id,name,country_code,phone_number,birthday,address',
                'churchInfo:id,user_id,zone'
            ])
            ->get();

        $array = [];
        foreach ($users as $index => $user) {
            if (empty($user['profile']['name'])) continue;
            $array[] = [
                'count' => $index + 1,
                'name' => $user['profile']['name'],
                'birthday' => $user['profile']['birthday'] ?? '',
                'phone' => ($user['profile']['country_code'] ?? '') . ($user['profile']['phone_number'] ?? ''),
                'zone' => $user['churchInfo']['zone']['name'] ?? '',
                'address' => $user['profile']['address'] ?? '',
                'id' => $user['id'],
                'is_line_oa' => $user['line_uid'] ? '已註冊' : '尚未註冊',
            ];
        }

        return $array;
    }

    public function headings(): array
    {
        return ['編號', '姓名', '生日', '電話', '牧區', '地址', 'user_id', '是否已註冊 LINE OA'];
    }

    public function title(): string
    {
        return match ($this->type) {
            UserPerSheetExport::NEW_USER => '新會友',
            UserPerSheetExport::MATCHED_LEGACY_USER => '已比對成功會友',
            UserPerSheetExport::LEGACY_USER => '舊會友'
        };
    }
}
