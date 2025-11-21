<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = ['user_id', 'settings'];

    protected $casts = [
        'settings' => 'array',
    ];
}
