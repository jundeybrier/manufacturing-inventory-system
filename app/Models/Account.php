<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Account extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'name', 'code', 'description', 'is_active'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function feeComponents() {
        return $this->hasMany(FeeComponent::class);
    }
}
