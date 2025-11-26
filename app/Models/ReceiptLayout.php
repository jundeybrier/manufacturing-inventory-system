<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ReceiptLayout extends Model
{
    protected $fillable = [
        'user_id',
        'receipt_type',
        'office_name',
        'date',
        'payor_info',
        'particulars',
        'total',
        'amount_words',
        'cashier_name',
        'page_width',
        'page_height',
    ];

    protected $casts = [
        'office_name' => 'array',
        'date' => 'array',
        'payor_info' => 'array',
        'particulars' => 'array',
        'total' => 'array',
        'amount_words' => 'array',
        'cashier_name' => 'array',
    ];
}
