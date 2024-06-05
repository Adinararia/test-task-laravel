<?php

namespace App\Models;

use App\Enums\YesNoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Token extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'was_used',
    ];

    protected $casts = [
        'was_used' => YesNoEnum::class,
    ];
}
