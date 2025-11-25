<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Deposit extends Model
{
    protected $fillable = [
        'uuid',
        'date',
        'fund_source_id',
        'amount',
        'reference_number',
        'user_id',
        'office_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($deposit) {
            if (!$deposit->uuid) {
                $deposit->uuid = (string) Str::uuid();
            }
        });
    }

    public function fundSource()
    {
        return $this->belongsTo(Account::class, 'fund_source_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
