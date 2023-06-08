<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dedication extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_MONTHLY = 'monthly';
    const TYPE_OTHERS = 'others';

    const METHOD_IMPORT = 'import';

    protected $hidden = ['created_by_user_id', 'method', 'file_name'];

    public function accountTitle()
    {
        return $this->belongsTo(AccountTitle::class);
    }

    public function getAmountAttribute($amount)
    {
        return number_format($amount);
    }
}
