<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'sequence',
    ];

    public function stageInventories()
    {
        return $this->hasMany(StageInventory::class);
    }

    public function movementsFrom()
    {
        return $this->hasMany(StageMovement::class, 'from_stage_id');
    }

    public function movementsTo()
    {
        return $this->hasMany(StageMovement::class, 'to_stage_id');
    }

    public function category()
    {
        return $this->belongsTo(StageCategory::class, 'category_id');
    }
}
