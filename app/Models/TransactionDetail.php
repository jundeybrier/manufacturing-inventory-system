<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class TransactionDetail extends Model
{
    protected $fillable = [
        'transaction_id', 'name', 'service_id', 'fee_component_id',
        'account_id', 'quantity', 'amount', 'total', 'currency', 'conversion_rate',
        'date', 'datetime_created', 'created_by', 'user_id','exchange_rate',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function allocations()
    {
        return $this->hasMany(TransactionAllocation::class, 'transaction_detail_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function feeComponent() : BelongsTo
    {
        return $this->belongsTo(\App\Models\FeeComponent::class, 'fee_component_id');
    }

    public function getTotalAccount(string $accountName): float
    {
        $total = 0;

        $component = $this->feeComponent;
        if ($component->account->name === $accountName) {
            $share = $component->base_amount * $this->quantity;
            $total += $share;
        }

        return $total;
    }

}
