<?php

namespace App\Utils;

class PhoneFormatUtil
{
    public static function getCountryCodes(): array
    {
        return [
            '86',
            '852',
            '886',
            '853',
            '65',
            '81',
            '66',
            '60',
            '63',
            '61',
            '44',
            '1',
        ];
    }

    public static function getCountryCode($phone): string
    {
        foreach (self::getCountryCodes() as $countryCode) {
            if (str_starts_with($phone, $countryCode)) {
                return $countryCode;
            }
        }

        return '';
    }

    public static function getPhoneNumber($phone): string
    {
        $phoneNumber = (int) $phone;
        foreach (self::getCountryCodes() as $countryCode) {
            if (str_starts_with($phone, $countryCode)) {
                $phoneNumber = substr($phone, strlen($countryCode));
            }
        }

        return $phoneNumber;
    }
}
