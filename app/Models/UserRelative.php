<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRelative extends Model
{
    use HasFactory, SoftDeletes;

    const RELATIVE_COUPLE = 'couple';
    const RELATIVE_FATHER = 'father';
    const RELATIVE_MOTHER = 'mother';
    const RELATIVE_CHILD = 'child';

    protected $casts = ['is_alive' => 'boolean', 'is_christened' => 'boolean'];

    public static function getRelativeArray(): array
    {
        return [self::RELATIVE_FATHER, self::RELATIVE_MOTHER, self::RELATIVE_CHILD, self::RELATIVE_COUPLE];
    }

    public static function getRelativeColumns(): array
    {
        return [
            'relationship', 'name', 'is_alive', 'is_christened', 'christened_church',
        ];
    }

    public static function mapOrCreateUserRelative(?int $legacyUserId, int $newUserId)
    {
        $legacyUserRelatives = UserRelative::query()
            ->where('user_id', $legacyUserId)
            ->get();

        foreach ($legacyUserRelatives as $legacyUserRelative) {
            $userRelative = new self();
            $userRelative['user_id'] = $newUserId;
            $userRelative['relationship'] = $legacyUserRelative['relationship'];
            $userRelative['name'] = $legacyUserRelative['name'];
            $userRelative['is_alive'] = $legacyUserRelative['is_alive'];
            $userRelative['is_christened'] = $legacyUserRelative['is_christened'];
            $userRelative['christened_church'] = $legacyUserRelative['christened_church'];
            $userRelative->save();
        }
    }
}
