<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionAllocation extends Model
{
    protected $fillable = [
        'transaction_detail_id',
        'account_id',
        'amount',
    ];

    public function detail(): BelongsTo
    {
        return $this->belongsTo(TransactionDetail::class, 'transaction_detail_id');
    }
}
