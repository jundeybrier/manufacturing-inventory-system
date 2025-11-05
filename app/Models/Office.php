<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Office extends Model
{
    use HasFactory;

    protected $fillable = ['id','uuid', 'name', 'location'];

    protected static function boot()
    {
        parent::boot();

        // Automatically generate UUID
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // Relationships (if needed later, e.g., users)
    public function users()
    {
        return $this->hasMany(User::class);
    }

}
