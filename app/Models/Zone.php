<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    const CHURCH_TYPE_SHUANG_LIEN = 'shuang-lien';
    const CHURCH_TYPE_HSIN_CHUANG = 'hsin-chuang';
    const CHURCH_TYPE_SAN_CHIH = 'san-chih';
    const CHURCH_TYPE_COMMON = 'common';

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->select(['id', 'name', 'parent_id']);
    }

    public function parent()
    {
        return $this->hasOne(self::class, 'id', 'parent_id');
    }
}
