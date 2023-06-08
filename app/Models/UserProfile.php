<?php

namespace App\Models;

use App\Managers\UserManager;
use App\Utils\PhoneFormatUtil;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class UserProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = ['is_married' => 'boolean'];

    public static function getProfileColumns(): array
    {
        return [
            'name', 'identify_id', 'country_code',
            'gender', 'is_married', 'company_area_code', 'company_phone_number', 'home_area_code',
            'home_phone_number', 'email', 'line_id', 'job_title', 'highest_education',
            'address', 'emergency_name', 'emergency_relationship',
            'emergency_contact', 'postal_code_id',
        ];
    }

    public static function getPhoneNumberFromRequest($phoneNumber, $countryCode)
    {
        try {
            $phone = (int) $phoneNumber;
            if (str_starts_with($phone, $countryCode)) {
                $phone = substr($phone, strlen($countryCode));
            }
        } catch (\Exception $e) {
            Log::error('Error Phone Number: ' . $e->getMessage());
        }

        return $phone;
    }

    public static function createByLegacyUserProfile(int $userId, $legacyUserProfile): self
    {
        $userProfile = new self();

        foreach ($legacyUserProfile as $key => $value) {
            if (in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }
            $userProfile[$key] = $value;
        }
        $userProfile['user_id'] = $userId;

        return $userProfile;
    }

    public static function getAfterValueIfSpecifyColumnIsReviewing($userProfile, $userId)
    {
        $oldPhoneNumber = $userProfile['country_code'] . $userProfile['phone_number'];
        $newPhoneNumber = UserManager::getAfterValueIfColumnIsReviewing($oldPhoneNumber, $userId, Review::PHONE);

        $userProfile['name'] = UserManager::getAfterValueIfColumnIsReviewing($userProfile['name'], $userId, Review::NAME);
        $userProfile['birthday'] = UserManager::getAfterValueIfColumnIsReviewing($userProfile['birthday'], $userId, Review::BIRTH);
        $userProfile['country_code'] = PhoneFormatUtil::getCountryCode($newPhoneNumber);
        $userProfile['phone_number'] = PhoneFormatUtil::getPhoneNumber($newPhoneNumber);
        $userProfile['identify_id'] = UserManager::getAfterValueIfColumnIsReviewing($userProfile['identify_id'], $userId, Review::IDENTIFY_ID);

        return $userProfile;
    }

    public static function appendCityAndRegionFromPostalCode($userProfile, $postalCodeId)
    {
        $postalCode = PostalCode::query()
            ->select(['id', 'city', 'region'])
            ->where('id', $postalCodeId)
            ->first();

        $userProfile['city'] = $postalCode['city'] ?? null;
        $userProfile['region'] = $postalCode['region'] ?? null;
    }
}
