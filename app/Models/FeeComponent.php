<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class FeeComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'service_id', 'name', 'base_amount', 'is_variable', 'currency', 'is_active', 'account_id'
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

    public function service()
    {
        return $this->belongsTo(\App\Models\Service::class);
    }

    public function account() {
        return $this->belongsTo(Account::class);
    }

    public function transactionDetails()
    {
        return $this->hasMany(\App\Models\TransactionDetail::class, 'fee_component_id');
    }

}
