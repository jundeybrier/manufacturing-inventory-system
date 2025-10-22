<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    const TYPES = [
        'passport' => 'Passport',
        'authentication' => 'Authentication',
        'notarials' => 'Notarials',
        'others' => 'Others',
    ];

    protected $fillable = ['uuid','type', 'name', 'description', 'is_active'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function feeComponents()
    {
        return $this->hasMany(\App\Models\FeeComponent::class);
    }

    public function productServices()
    {
        return $this->hasMany(ProductService::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_services');
    }
}
