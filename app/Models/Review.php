<?php

namespace App\Models;

use App\Utils\PhoneFormatUtil;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class Review extends Model
{
    use HasFactory;

    const PENDING = 'pending';
    const ACCEPTED = 'accepted';
    const REJECTED = 'rejected';
    const CANCELED = 'canceled';
    const USER_CANCELED = 'user-canceled';
    const USER_STOPPED = 'user-stopped';

    const NAME = 'name';
    const BIRTH = 'birth';
    const PHONE = 'phone';
    const IDENTIFY_ID = 'identify_id';
    const ADULTHOOD_CHRISTENED_AT = 'adulthood_christened_at';
    const CHILDHOOD_CHRISTENED_AT = 'childhood_christened_at';
    const ADULTHOOD_CHRISTENED_CHURCH = 'adulthood_christened_church';
    const CHILDHOOD_CHRISTENED_CHURCH = 'childhood_christened_church';

    public function userProfile()
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'user_id')
            ->select(['user_id', 'name', 'birthday', 'country_code', 'phone_number']);
    }

    public static function createReview(int $userId, string $type, string $beforeValue, string $afterValue)
    {
        $acceptableTypes = [
            self::NAME,
            self::BIRTH,
            self::PHONE,
            self::IDENTIFY_ID,
            self::ADULTHOOD_CHRISTENED_AT,
            self::ADULTHOOD_CHRISTENED_CHURCH,
            self::CHILDHOOD_CHRISTENED_AT,
            self::CHILDHOOD_CHRISTENED_CHURCH,
        ];

        if (!in_array($type, $acceptableTypes)) {
            return;
        }

        $user = User::query()
            ->where('id', $userId)
            ->first();

        if ($user === null) {
            return;
        }

        if ($beforeValue !== $afterValue) {
            $existedReview = self::query()
                ->where('status', self::PENDING)
                ->where('user_id', $userId)
                ->where('type', $type)
                ->first();

            if ($existedReview != null) {
                if ($afterValue === $existedReview['after_value']) {
                    return;
                }
                $existedReview['status'] = self::CANCELED;
                $existedReview->save();

                if ($afterValue === $existedReview['before_value']) {
                    return;
                }

                $beforeValue = $existedReview['before_value'];
            }

            $review = new self();
            $review['user_id'] = $userId;
            $review['type'] = $type;
            $review['before_value'] = $beforeValue;
            $review['after_value'] = $afterValue;
            $review['status'] = self::PENDING;
            $review->save();
        }
    }

    public function accept(int $adminId)
    {
        self::updateUserData($this);
        $this['status'] = self::ACCEPTED;
        $this['reviewed_by_user_id'] = $adminId;
        $this['reviewed_at'] = Carbon::now();
        $this->save();
    }

    public function reject(int $adminId)
    {
        $this['status'] = self::REJECTED;
        $this['reviewed_by_user_id'] = $adminId;
        $this['reviewed_at'] = Carbon::now();
        $this->save();
    }

    /**
     * @throws \Exception
     */
    public static function updateUserData(self $review)
    {
        $userProfile = UserProfile::query()
            ->where('user_id', $review['user_id'])
            ->first();

        if ($userProfile == null) {
            throw new \Exception('找不到使用者資料');
        }

        $userChurchInfo = UserChurchInfo::query()
            ->where('user_id', $review['user_id'])
            ->first();

        if ($userChurchInfo == null) {
            $userChurchInfo = new UserChurchInfo();
            $userChurchInfo['user_id'] = $review['user_id'];
            $userChurchInfo->save();
        }
        switch ($review['type']) {
            case self::NAME:
                $userProfile['name'] = $review['after_value'];
                break;
            case self::BIRTH:
                $userProfile['birthday'] = $review['after_value'];
                break;
            case self::PHONE:
                $userProfile['country_code'] = PhoneFormatUtil::getCountryCode($review['after_value']);
                $userProfile['phone_number'] = PhoneFormatUtil::getPhoneNumber($review['after_value']);
                break;
            case self::IDENTIFY_ID:
                $userProfile['identify_id'] = $review['after_value'];
                break;
            case self::ADULTHOOD_CHRISTENED_AT:
                $userChurchInfo['adulthood_christened_at'] = $review['after_value'];
                break;
            case self::CHILDHOOD_CHRISTENED_AT:
                $userChurchInfo['childhood_christened_at'] = $review['after_value'];
                break;
            case self::ADULTHOOD_CHRISTENED_CHURCH:
                $userChurchInfo['adulthood_christened_church'] = $review['after_value'];
                break;
            case self::CHILDHOOD_CHRISTENED_CHURCH:
                $userChurchInfo['childhood_christened_church'] = $review['after_value'];
                break;
            default:
                throw new \Exception('找不到修改類型');
        }
        try {
            $userProfile->save();
        } catch (QueryException $exception) {
            Log::error($exception->getMessage());
            abort(400, '此身分證號已被使用');
        }
        $userChurchInfo->save();
    }

    public function getPendingReviewColumnsAttribute(): string
    {
        $reviews = self::query()
            ->select('type')
            ->where('user_id', $this['user_id'])
            ->where('status', self::PENDING)
            ->pluck('type');

        $columns = [
            self::NAME,
            self::BIRTH,
            self::PHONE,
            self::IDENTIFY_ID,
            self::ADULTHOOD_CHRISTENED_AT,
            self::CHILDHOOD_CHRISTENED_AT,
            self::ADULTHOOD_CHRISTENED_CHURCH,
            self::CHILDHOOD_CHRISTENED_CHURCH,
        ];

        $reviewingColumns = '';

        foreach ($columns as $column) {
            if ($reviews->contains($column)) {
                $reviewingColumns = $reviewingColumns . self::getColumnMandarin($column) . '、';
            }
        }

        return mb_substr($reviewingColumns, 0, mb_strlen($reviewingColumns) - 1);
    }

    public static function getColumnMandarin(string $type): string
    {
        return match ($type) {
            self::NAME => '姓名',
            self::BIRTH => '生日',
            self::PHONE => '電話',
            self::IDENTIFY_ID => '身分證字號',
            self::ADULTHOOD_CHRISTENED_AT => '成人洗禮時間',
            self::CHILDHOOD_CHRISTENED_AT => '幼兒洗禮時間',
            self::ADULTHOOD_CHRISTENED_CHURCH => '成人洗禮教會',
            self::CHILDHOOD_CHRISTENED_CHURCH => '幼兒洗禮教會',
        };
    }
}
