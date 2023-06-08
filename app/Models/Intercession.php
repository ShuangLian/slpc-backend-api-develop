<?php

namespace App\Models;

use App\Managers\IntercessionManager;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Intercession extends Model
{
    use HasFactory, SoftDeletes;

    //personal card types
    const GENERAL_CARD = 'GENERAL';
    const EMERGENCY_CARD = 'EMERGENCY';
    const PRIVATE_CARD = 'PRIVATE';

    const MINISTRY_CARD = 'MINISTRY';
    const THANKFUL_CARD = 'THANKFUL';

    const STATUS_PENDING = 'PENDING';
    const STATUS_PROCESSING = 'PROCESSING';
    const STATUS_DONE = 'DONE';
    const STATUS_DELETED = 'DELETED';

    const DAYS_90 = 90;
    const DAYS_30 = 30;

    protected $casts = [
        'is_target_user' => 'boolean',
        'is_target_christened' => 'boolean',
        'is_public' => 'boolean',
        'is_printed' => 'boolean',
    ];

    public function thankful()
    {
        return $this->hasOne(self::class, 'parent_id', 'id');
    }

    public function parent()
    {
        return $this->hasOne(self::class, 'id', 'parent_id');
    }

    /**
     * @throws \Exception
     */
    public static function createIntercessionByRequest(Request $request, int $userId): self
    {
        $request->validate([
            'card_type' => 'required',
            'apply_name' => 'required',
            'country_code' => 'required',
            'phone' => 'required',
        ], [
            'card_type.required' => '卡片類型為必填',
            'apply_name.required' => '代禱申請者為必填',
            'country_code.required' => '國碼為必填',
            'phone.required' => '手機號碼為必填',
        ]);

        if (IntercessionManager::isPersonalType($request['card_type'])) {
            $request->validate([
                'target_name' => 'required',
                'is_target_user' => 'required|boolean',
                'is_target_christened' => 'required|boolean',
                'target_age' => 'required|integer',
                'relative' => 'required',
                'apply_date' => 'required',
                'content' => 'required',
            ], [
                'target_name.required' => '禱告對象姓名為必填',
                'is_target_user.required' => '是否為會友為必填',
                'is_target_user.boolean' => '是否為會友格式不符合',
                'is_target_christened.required' => '是否已受洗為必填',
                'is_target_christened.boolean' => '是否已受洗格式不符合',
                'target_age.required' => '禱告對象年齡為必填',
                'target_age.integer' => '禱告對象年齡格式不符合',
                'relative.required' => '與代禱申請者關係為必填',
                'apply_date.required' => '申請日期為必填',
                'content.required' => '代禱事項為必填',
            ]);
        }

        if (IntercessionManager::isMinistryType($request['card_type'])) {
            $request->validate([
                'ministry' => 'required',
                'apply_date' => 'required',
                'is_public' => 'required|boolean',
                'content' => 'required',
            ], [
                'ministry.required' => '代禱項目為必填',
                'apply_date.required' => '申請日期為必填',
                'is_public.required' => '是否公開在教會公佈欄為必填',
                'is_public.boolean' => '是否公開在教會公佈欄格式不符合',
                'content.required' => '代禱事項為必填',
            ]);
        }

        if (IntercessionManager::isThankfulType($request['card_type'])) {
            $request->validate([
                'target_name' => 'required',
                'relative' => 'required',
                'prayer_answered_date' => 'required',
                'thankful_content' => 'required',
                'is_public' => 'required|boolean',
            ], [
                'target_name.required' => '禱告對象姓名為必填',
                'relative.required' => '與代禱申請者關係為必填',
                'prayer_answered_date.required' => '禱告應允的日期為必填',
                'thankful_content.required' => '應允感謝的內容為必填',
                'is_public.required' => '是否公開在教會公佈欄為必填',
                'is_public.boolean' => '是否公開在教會公佈欄格式不符合',
            ]);
        }

        if (IntercessionManager::isThankfulType($request['card_type'])) {
            $answeredIntercession = self::query()
                ->where('parent_id', $request['parent_id'])
                ->first();

            if (!empty($answeredIntercession)) {
                throw new \Exception('已經填寫過感謝卡囉！');
            }
        }

        $latestCardId = self::withTrashed()
            ->where('card_type', $request['card_type'])
            ->orderByDesc('id')
            ->pluck('card_id')
            ->first();

        $columns = ['card_type', 'apply_name', 'country_code', 'apply_date', 'content', 'parent_id', 'target_name',
            'is_target_user', 'is_target_christened', 'target_age', 'relative', 'ministry', 'is_public', 'prayer_answered_date',
            'thankful_content',
        ];

        $intercession = new self();
        foreach ($columns as $column) {
            if ($request->has($column)) {
                $intercession[$column] = $request[$column];
            }
        }
        $intercession['created_by_user_id'] = $userId;
        $intercession['user_id'] = $userId;
        $intercession['phone'] = UserProfile::getPhoneNumberFromRequest($request['phone'], $request['country_code']);
        $intercession['card_id'] = IntercessionManager::newCardId($latestCardId, $request['card_type']);
        $intercession['status'] = self::STATUS_PENDING;

        if (IntercessionManager::isThankfulType($request['card_type'])) {
            $intercession['apply_date'] = Carbon::now()->format('Y-m-d');
        }
        $intercession->save();

        return $intercession;
    }

    /**
     * @throws \Exception
     */
    public static function createIntercessionByAdmin(Request $request, int $adminUserId): self
    {
        $request->validate([
            'card_type' => 'required',
            'apply_name' => 'required',
            'country_code' => 'required',
            'phone' => 'required',
            'birthday' => 'required',
        ], [
            'card_type.required' => '卡片類型為必填',
            'apply_name.required' => '代禱申請者為必填',
            'country_code.required' => '國碼為必填',
            'phone.required' => '手機號碼為必填',
            'birthday.required' => '生日為必填',
        ]);

        if (IntercessionManager::isPersonalType($request['card_type'])) {
            $request->validate([
                'target_name' => 'required',
                'is_target_user' => 'required|boolean',
                'is_target_christened' => 'required|boolean',
                'target_age' => 'required|integer',
                'relative' => 'required',
                'apply_date' => 'required',
                'content' => 'required',
            ], [
                'target_name.required' => '禱告對象姓名為必填',
                'is_target_user.required' => '是否為會友為必填',
                'is_target_user.boolean' => '是否為會友格式不符合',
                'is_target_christened.required' => '是否已受洗為必填',
                'is_target_christened.boolean' => '是否已受洗格式不符合',
                'target_age.required' => '禱告對象年齡為必填',
                'target_age.integer' => '禱告對象年齡格式不符合',
                'relative.required' => '與代禱申請者關係為必填',
                'apply_date.required' => '申請日期為必填',
                'content.required' => '代禱事項為必填',
            ]);
        }

        if (IntercessionManager::isMinistryType($request['card_type'])) {
            $request->validate([
                'ministry' => 'required',
                'apply_date' => 'required',
                'is_public' => 'required|boolean',
                'content' => 'required',
            ], [
                'ministry.required' => '代禱項目為必填',
                'apply_date.required' => '申請日期為必填',
                'is_public.required' => '是否公開在教會公佈欄為必填',
                'is_public.boolean' => '是否公開在教會公佈欄格式不符合',
                'content.required' => '代禱事項為必填',
            ]);
        }

        $columns = ['card_type', 'apply_name', 'country_code', 'apply_date', 'content', 'parent_id', 'target_name',
            'is_target_user', 'is_target_christened', 'target_age', 'relative', 'ministry', 'is_public', 'prayer_answered_date',
            'thankful_content',
        ];

        $intercession = new self();
        foreach ($columns as $column) {
            if ($request->has($column)) {
                $intercession[$column] = $request[$column];
            }
        }
        $intercession['created_by_user_id'] = $adminUserId;
        $intercession['phone'] = UserProfile::getPhoneNumberFromRequest($request['phone'], $request['country_code']);
        $intercession['status'] = self::STATUS_PENDING;

        $userProfile = UserProfile::query()
            ->where('name', $request['apply_name'])
            ->where('country_code', $request['country_code'])
            ->where('phone_number', UserProfile::getPhoneNumberFromRequest($request['phone'], $request['country_code']))
            ->where('birthday', $request['birthday'])
            ->first();

        if (empty($userProfile)) {
            throw new \Exception('找不到該申請者');
        }
        $intercession['user_id'] = $userProfile['user_id'];

        $latestCardId = self::withTrashed()
            ->where('card_type', $request['card_type'])
            ->orderByDesc('id')
            ->pluck('card_id')
            ->first();
        $intercession['card_id'] = IntercessionManager::newCardId($latestCardId, $request['card_type']);

        $intercession->save();

        return $intercession;
    }
}
