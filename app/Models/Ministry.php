<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ministry extends Model
{
    use HasFactory;

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->select(['id', 'name', 'parent_id']);
    }
}
