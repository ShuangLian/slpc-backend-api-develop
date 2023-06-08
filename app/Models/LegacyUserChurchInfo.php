<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegacyUserChurchInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'legacy_user_church_info';

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
}
