<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStatisticalTag extends Model
{
    use HasFactory;

    const TAG_AMOUNT_DEDICATION = '/AMOUNT/DEDICATION/ACCOUNT_TITLE_ID';
    const TAG_COUNT_DEDICATION = '/COUNT/DEDICATION/ACCOUNT_TITLE_ID';

    public static function updateOrCreateAmountTag($accountCode, $accountTitleId, $amount)
    {
        $userStatisticalTag = self::query()
            ->where('account_code', $accountCode)
            ->where('tag_key', self::TAG_AMOUNT_DEDICATION . '/' . $accountTitleId)
            ->first();

        if ($userStatisticalTag == null) {
            $userStatisticalTag = new self();
            $userStatisticalTag['account_code'] = $accountCode;
            $userStatisticalTag['tag_key'] = self::TAG_AMOUNT_DEDICATION . '/' . $accountTitleId;
        }

        $userStatisticalTag['amount'] += $amount;
        $userStatisticalTag->save();
    }

    public static function updateOrCreateCountTag($accountCode, $accountTitleId, $isAdd = true)
    {
        $userStatisticalTag = self::query()
            ->where('account_code', $accountCode)
            ->where('tag_key', self::TAG_COUNT_DEDICATION . '/' . $accountTitleId)
            ->first();

        if ($userStatisticalTag == null) {
            $userStatisticalTag = new self();
            $userStatisticalTag['account_code'] = $accountCode;
            $userStatisticalTag['tag_key'] = self::TAG_COUNT_DEDICATION . '/' . $accountTitleId;
        }

        if ($isAdd) {
            $userStatisticalTag['amount'] += 1;
        } else {
            $userStatisticalTag['amount'] -= 1;
        }

        $userStatisticalTag->save();
    }
}
