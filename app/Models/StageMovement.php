<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StageMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'from_stage_id',
        'to_stage_id',
        'quantity',
        'movement_date',
        'remarks',
        'production_order_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'movement_date' => 'date',
    ];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function fromStage()
    {
        return $this->belongsTo(Stage::class, 'from_stage_id');
    }

    public function toStage()
    {
        return $this->belongsTo(Stage::class, 'to_stage_id');
    }

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }
}
