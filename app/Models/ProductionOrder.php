<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'planned_output_qty',
        'actual_output_qty',
        'production_date',
        'status',
    ];

    protected $casts = [
        'production_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function materials()
    {
        return $this->hasMany(ProductionOrderMaterial::class);
    }

    public function stageMovements()
    {
        return $this->hasMany(StageMovement::class);
    }
}
