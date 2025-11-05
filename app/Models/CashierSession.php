<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class CashierSession extends Model
{
    use HasFactory;

    protected $table = 'cashier_sessions';

    protected $fillable = [
        'user_id',
        'opened_at',
        'closed_at',
        'remarks',
        'uuid',
        'cloud_id',
        'synced_at',
        'office_id',
    ];

    protected $dates = [
        'opened_at',
        'closed_at',
        'synced_at',
        'created_at',
        'updated_at',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // If you have CashCollection/SessionDenomination, add:
    public function denominations()
    {
        return $this->hasMany(\App\Models\SessionDenomination::class, 'session_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'session_id');
    }

    public function getTotalCollectionsAttribute()
    {
        return Transaction::whereDate('created_at', \Carbon\Carbon::parse($this->opened_at)->toDateString())
            ->where('user_id', $this->user_id)
            ->with('details')
            ->whereNull('voided_at')
            ->get()
            ->sum(fn ($transaction) => $transaction->computed_total);
    }

    public function getTransactionCountAttribute()
    {
        return Transaction::whereDate('created_at', \Carbon\Carbon::parse($this->opened_at)->toDateString())
            ->where('user_id', $this->user_id)
            ->whereNull('voided_at')
            ->count();
    }

    public function getVoidedCountAttribute()
    {
        return Transaction::whereDate('created_at', \Carbon\Carbon::parse($this->opened_at)->toDateString())
            ->where('user_id', $this->user_id)
            ->whereNotNull('voided_at')
            ->count();
    }
}
