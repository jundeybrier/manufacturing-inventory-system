<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Transaction extends Model
{
    protected $table = 'transactions';

    public $timestamps = false; // use datetime_created manually

    protected $fillable = [
        'token', 'date', 'reference_number', 'or_number',
        'lastname', 'firstname', 'middlename', 'rep_name',
        'amount_paid', 'remarks', 'counter',
        'user_id', 'datetime_created', 'created_by',
        'datetime_voided', 'voided_by',
        'datetime_validated', 'validated_by',
    ];

    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 't_id');
    }

    public function getFullnameAttribute(): string
    {
        return trim("{$this->firstname} {$this->middlename} {$this->lastname}");
    }
}
