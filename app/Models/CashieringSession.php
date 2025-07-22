<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashieringSession extends Model
{
    protected $fillable = [
        'user_id',
        'opened_at',
        'closed_at',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
