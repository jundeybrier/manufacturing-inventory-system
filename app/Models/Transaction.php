<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class Transaction extends Model
{
    protected $fillable = [
        'uuid',
        'date',
        'reference',
        'or_number',
        'firstname',
        'lastname',
        'middlename',
        'rep_name',
        'total_amount',
        'remarks',
        'counter',
        'user_id',
        'office_id',
        'session_id',
        'datetime_created',
        'datetime_validated',
        'validated_by',
        'created_by',
        'is_voided',
        'void_reason',
        'voided_by',
        'voided_at',
        'synced_at',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }

    public function getComputedTotalAttribute(): float
    {
        return $this->details->reduce(function ($carry, $detail) {
            $lineTotal = $detail->quantity * $detail->amount;

            if ($detail->currency !== 'PHP' && $detail->exchange_rate > 0) {
                $lineTotal *= $detail->exchange_rate;
            }

            return $carry + $lineTotal;
        }, 0);
    }

    public function getFullnameAttribute(){
        if($this->middlename){
            return strtoupper($this->lastname.', '.$this->firstname.' '.$this->middlename[0]);
        }
        return strtoupper($this->lastname.', '.$this->firstname);
    }

    public function getTotalAccount(string $accountName): float
    {
        $total = 0;

        foreach ($this->details as $detail) {
            if (!$detail->feeComponent) {
                continue;
            }
            $component = $detail->feeComponent;
            if ($component->account->name === $accountName) {
                if($detail->exchange_rate > 0){
                    $share = $detail->exchange_rate * $detail->amount * $detail->quantity;
                }else{
                    $share = $component->base_amount * $detail->quantity;
                }
                $total += $share;
            }
        }

        return $total;
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }


}
