<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Product extends Model
{
    protected $table = 'products';

    public $timestamps = false; // use datetime_created manually

    public function services()
    {
        return $this->belongsToMany(
            \App\Models\Service::class,
            'products_services',
            'product_id',
            'service_id'
        );
    }
    public function getPriceAttribute()
    {
        return $this->services->sum('amount');
    }

}
