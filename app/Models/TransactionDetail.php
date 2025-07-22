<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class TransactionDetail extends Model
{
    protected $table = 'transaction_details';

    public $timestamps = false; // use datetime_created manually

    protected $fillable = [
        't_id', 'service_id', 'user_id','date', 'datetime_created', 'created_by',
        'price', 'total', 'quantity', 'conversion_rate','name'
    ];

}
