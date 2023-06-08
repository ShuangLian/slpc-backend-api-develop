<?php

namespace App\Managers;

use App\Models\Intercession;
use Carbon\Carbon;

class IntercessionManager
{
    public static function isPersonalType($cardType): bool
    {
        return in_array($cardType, [Intercession::GENERAL_CARD, Intercession::EMERGENCY_CARD, Intercession::PRIVATE_CARD]);
    }

    public static function isMinistryType($cardType): bool
    {
        return $cardType == Intercession::MINISTRY_CARD;
    }

    public static function isThankfulType($cardType): bool
    {
        return $cardType == Intercession::THANKFUL_CARD;
    }

    /**
     * @throws \Exception
     */
    public static function newCardId(?string $latestCardId, string $cardType, ?string $year = null): string
    {
        $prefix = match ($cardType) {
            Intercession::GENERAL_CARD => '代',
            Intercession::EMERGENCY_CARD => '急',
            Intercession::PRIVATE_CARD => '私',
            Intercession::MINISTRY_CARD => '事',
            Intercession::THANKFUL_CARD => '謝',
            default => throw new \Exception('When new card id get unknown card type: ' . $cardType),
        };

        if (empty($year)) {
            $year = Carbon::now()->format('y');
        }

        // $latestCardId format -> 代22020
        if (!empty($latestCardId) && $prefix != mb_substr($latestCardId, 0, 1)) {
            throw new \Exception('When new card id get error latest card id: ' . $latestCardId);
        }

        // 編號跨年
        if (self::isCardIdShouldReset($latestCardId, $year)) {
            $latestCardId = null;
        }

        if (empty($latestCardId)) {
            $newSerialNumber = '001';
        } else {
            $newSerialNumber = (int) mb_substr($latestCardId, 3) + 1;
            $newSerialNumber = str_pad($newSerialNumber, 3, '0', STR_PAD_LEFT);
        }

        return $prefix . $year . $newSerialNumber;
    }

    public static function isCardIdShouldReset($latestCardId, $year): bool
    {
        return $year != mb_substr($latestCardId, 1, 2);
    }
}
