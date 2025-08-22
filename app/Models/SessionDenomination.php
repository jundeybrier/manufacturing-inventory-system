<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class SessionDenomination extends Model
{
    use HasFactory;

    protected $table = 'session_denominations';

    // Automatically casts columns to their native types
    protected $casts = [
        'quantity' => 'integer',
        'total' => 'decimal:2',
        'synced_at' => 'datetime',
    ];

    // Fillable properties (add/remove as needed)
    protected $fillable = [
        'uuid',
        'session_id',
        'denomination',
        'quantity',
        'total',
        'synced_at',
    ];

    // Use uuid as the route key (optional, for route model binding)
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    // Relationship: belongsTo CashierSession
    public function session()
    {
        return $this->belongsTo(CashierSession::class);
    }

    // Auto-generate UUID on create
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
