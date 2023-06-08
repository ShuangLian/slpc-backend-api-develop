<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountTitle extends Model
{
    use HasFactory;

    const MONTHLY_SERIAL_NUMBER = '4103-02';

    /**
     * @throws \Exception
     */
    public static function getIdFromSerialNumber($serialNumber): int
    {
        $accountTitle = self::query()
            ->where('account_title_serial_number', $serialNumber)
            ->first();

        if ($accountTitle == null) {
            throw new \Exception('會計科目不符合: ' . $serialNumber);
        }

        return $accountTitle['id'];
    }
}
