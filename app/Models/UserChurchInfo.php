<?php

namespace App\Models;

use App\Managers\UserManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class UserChurchInfo extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * MEMBERSHIP_WITH_RESIDING: 籍在人在
     * MEMBERSHIP_WITHOUT_RESIDING: 籍在人不在
     * RESIDING_WITHOUT_MEMBERSHIP: 籍不在人在.
     */
    const MEMBERSHIP_WITH_RESIDING = 'membership-with-residing';
    const MEMBERSHIP_WITHOUT_RESIDING = 'membership-without-residing';
    const RESIDING_WITHOUT_MEMBERSHIP = 'residing-without-membership';

    const LOCATION_SHUANG_LIEN = 'shuang-lien';
    const LOCATION_HSIN_CHUANG = 'hsin-chuang';
    const LOCATION_SAN_CHIH = 'san-chih';

    /**
     * PARTICIPATION_TYPE_1: 愛網新朋友
     * PARTICIPATION_TYPE_2: 固定參加
     * PARTICIPATION_TYPE_3: 長期外出
     * PARTICIPATION_TYPE_4: 一年未參加
     * PARTICIPATION_TYPE_5: 旅居海外
     * PARTICIPATION_TYPE_6: 失聯.
     */
    const PARTICIPATION_TYPE_1 = '1';
    const PARTICIPATION_TYPE_2 = '2';
    const PARTICIPATION_TYPE_3 = '3';
    const PARTICIPATION_TYPE_4 = '4';
    const PARTICIPATION_TYPE_5 = '5';
    const PARTICIPATION_TYPE_6 = '6';

    protected $table = 'user_church_info';

    public static function getMembershipArray(): array
    {
        return [self::MEMBERSHIP_WITHOUT_RESIDING, self::MEMBERSHIP_WITH_RESIDING, self::RESIDING_WITHOUT_MEMBERSHIP];
    }

    public static function getChurchLocationArray(): array
    {
        return [self::LOCATION_SHUANG_LIEN, self::LOCATION_HSIN_CHUANG, self::LOCATION_SAN_CHIH];
    }

    public static function getParticipationArray(): array
    {
        return [self::PARTICIPATION_TYPE_1, self::PARTICIPATION_TYPE_2, self::PARTICIPATION_TYPE_3, self::PARTICIPATION_TYPE_4, self::PARTICIPATION_TYPE_5, self::PARTICIPATION_TYPE_6];
    }

    public static function getChurchInfoColumns(): array
    {
        return [
            'membership_status', 'participation_status', 'membership_location', 'zone',
            'serving_experience', 'adulthood_christened_church', 'childhood_christened_church',
            'confirmed_church', 'skill',
        ];
    }

    public static function getUserChurchInfoColumns(): array
    {
        return [
            'membership_status', 'participation_status', 'membership_location', 'zone',
            'serving_experience', 'adulthood_christened_at', 'adulthood_christened_church',
            'childhood_christened_at', 'childhood_christened_church', 'confirmed_at',
            'confirmed_church',
        ];
    }

    public static function createByLegacyUserChurchInfo(int $userId, $legacyChurchInfo): self
    {
        $userChurchInfo = new self();
        foreach ($legacyChurchInfo as $key => $value) {
            if (in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at', 'zone'])) {
                continue;
            }
            $userChurchInfo[$key] = $value;
        }

        if (array_key_exists('zone', $legacyChurchInfo)) {
            $userChurchInfo['zone'] = $legacyChurchInfo['zone']['id'] ?? null;
        }
        $userChurchInfo['user_id'] = $userId;

        return $userChurchInfo;
    }

    public static function mapOrCreateUserChurchInfo(?int $legacyUserId, int $newUserId)
    {
        $legacyChurchInfo = UserChurchInfo::query()
            ->where('user_id', $legacyUserId)
            ->first();

        if ($legacyChurchInfo == null) {
            $userChurchInfo = new self();
            $userChurchInfo['user_id'] = $newUserId;
        } else {
            $userChurchInfo = self::createByLegacyUserChurchInfo($newUserId, $legacyChurchInfo->toArray());
        }
        $userChurchInfo->save();
    }

    public function getZoneAttribute($value)
    {
        $zone = Zone::query()
            ->where('id', $value)
            ->with('parent')
            ->first();

        if ($zone == null) {
            return null;
        }

        if (!empty($zone['parent'])) {
            $zoneStr = $zone['parent']['name'] . '/' . $zone['name'];
        } else {
            $zoneStr = $zone['name'];
        }

        return [
            'id' => $value,
            'name' => $zoneStr,
        ];
    }

    public function updateOrReviewColumn($column, $newValue)
    {
        if (!in_array($column, Schema::getColumnListing($this->table))) {
            return;
        }

        if (empty($this[$column])) {
            $this[$column] = $newValue;
            $this->save();
        }

        if (!empty($this[$column])) {
            $type = match ($column) {
                Review::ADULTHOOD_CHRISTENED_CHURCH => 'adulthood_christened_church',
                Review::ADULTHOOD_CHRISTENED_AT => 'adulthood_christened_at',
                Review::CHILDHOOD_CHRISTENED_CHURCH => 'childhood_christened_church',
                Review::CHILDHOOD_CHRISTENED_AT => 'childhood_christened_at',
            };
            Review::createReview($this['user_id'], $type, $this[$column], $newValue);
        }
    }

    public static function getAfterValueIfSpecifyColumnIsReviewing($churchInfo, $userId)
    {
        $churchInfo['adulthood_christened_at'] = UserManager::getAfterValueIfColumnIsReviewing($churchInfo['adulthood_christened_at'], $userId, Review::ADULTHOOD_CHRISTENED_AT);
        $churchInfo['adulthood_christened_church'] = UserManager::getAfterValueIfColumnIsReviewing($churchInfo['adulthood_christened_church'], $userId, Review::ADULTHOOD_CHRISTENED_CHURCH);
        $churchInfo['childhood_christened_at'] = UserManager::getAfterValueIfColumnIsReviewing($churchInfo['childhood_christened_at'], $userId, Review::CHILDHOOD_CHRISTENED_AT);
        $churchInfo['childhood_christened_church'] = UserManager::getAfterValueIfColumnIsReviewing($churchInfo['childhood_christened_church'], $userId, Review::CHILDHOOD_CHRISTENED_CHURCH);

        return $churchInfo;
    }
}
